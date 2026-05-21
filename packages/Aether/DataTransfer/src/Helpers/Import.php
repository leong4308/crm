<?php

namespace Aether\DataTransfer\Helpers;

use Aether\DataTransfer\Contracts\Import as ImportContract;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Aether\DataTransfer\Helpers\Importers\AbstractImporter;
use Aether\DataTransfer\Helpers\Sources\AbstractSource;
use Aether\DataTransfer\Helpers\Sources\CSV as CSVSource;
use Aether\DataTransfer\Helpers\Sources\Excel as ExcelSource;
use Aether\DataTransfer\Repositories\ImportBatchRepository;
use Aether\DataTransfer\Repositories\ImportRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Import
{
    /**
     * Estado de importación para importación pendiente.
     */
    public const STATE_PENDING = 'pending';

    /**
     * Estado de importación para importación validada.
     */
    public const STATE_VALIDATED = 'validated';

    /**
     * Estado de importación para procesar la importación.
     */
    public const STATE_PROCESSING = 'processing';

    /**
     * Estado de importación para importación procesada.
     */
    public const STATE_PROCESSED = 'processed';

    /**
     * Estado de importación para vincular la importación.
     */
    public const STATE_LINKING = 'linking';

    /**
     * Estado de importación para la importación vinculada.
     */
    public const STATE_LINKED = 'linked';

    /**
     * Estado de importación para la importación de indexación.
     */
    public const STATE_INDEXING = 'indexing';

    /**
     * Estado de importación para importación indexada.
     */
    public const STATE_INDEXED = 'indexed';

    /**
     * Estado de importación para la importación completada.
     */
    public const STATE_COMPLETED = 'completed';

    /**
     * Estrategia de validación para omitir el error durante el proceso de importación.
     */
    public const VALIDATION_STRATEGY_SKIP_ERRORS = 'skip-errors';

    /**
     * Estrategia de validación para detener el proceso de importación en caso de error.
     */
    public const VALIDATION_STRATEGY_STOP_ON_ERROR = 'stop-on-errors';

    /**
     * Constante de acción para actualizar/crear el recurso.
     */
    public const ACTION_APPEND = 'append';

    /**
     * Constante de acción para eliminar el recurso.
     */
    public const ACTION_DELETE = 'delete';

    /**
     * Importar instancia.
     */
    protected ImportContract $import;

    /**
     * Instancia de ayuda de error.
     *
     * @var Error
     */
    protected $typeImporter;

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        protected ImportRepository $importRepository,
        protected ImportBatchRepository $importBatchRepository,
        protected Error $errorHelper
    ) {}

    /**
     * Establecer instancia de importación.
     */
    public function setImport(ImportContract $import): self
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Devuelve la instancia de importación.
     */
    public function getImport(): ImportContract
    {
        return $this->import;
    }

    /**
     * Devuelve la instancia del asistente de error.
     *
     * @return Error
     */
    public function getErrorHelper()
    {
        return $this->errorHelper;
    }

    /**
     * Devuelve la instancia del asistente de origen.
     */
    public function getSource(): AbstractSource
    {
        if (Str::contains($this->import->file_path, '.csv')) {
            $source = new CSVSource(
                $this->import->file_path,
                $this->import->field_separator,
            );
        } else {
            $source = new ExcelSource(
                $this->import->file_path,
                $this->import->field_separator,
            );
        }

        return $source;
    }

    /**
     * Valida la importación y devuelve el resultado de la validación.
     */
    public function validate(): bool
    {
        try {
            $source = $this->getSource();

            $typeImporter = $this->getTypeImporter()->setSource($source);

            $typeImporter->validateData();
        } catch (\Exception $e) {
            $this->errorHelper->addError(
                AbstractImporter::ERROR_CODE_SYSTEM_EXCEPTION,
                null,
                null,
                $e->getMessage()
            );
        }

        $import = $this->importRepository->update([
            'state' => self::STATE_VALIDATED,
            'processed_rows_count' => $this->getProcessedRowsCount(),
            'invalid_rows_count' => $this->errorHelper->getInvalidRowsCount(),
            'errors_count' => $this->errorHelper->getErrorsCount(),
            'errors' => $this->getFormattedErrors(),
            'error_file_path' => $this->uploadErrorReport(),
        ], $this->import->id);

        $this->setImport($import);

        return $this->isValid();
    }

    /**
     * Inicia el proceso de importación.
     */
    public function isValid(): bool
    {
        if ($this->isErrorLimitExceeded()) {
            return false;
        }

        if ($this->import->processed_rows_count <= $this->import->invalid_rows_count) {
            return false;
        }

        return true;
    }

    /**
     * Compruebe si se ha excedido el límite de error.
     */
    public function isErrorLimitExceeded(): bool
    {
        if (
            $this->import->validation_strategy == self::VALIDATION_STRATEGY_STOP_ON_ERROR
            && $this->import->errors_count > $this->import->allowed_errors
        ) {
            return true;
        }

        return false;
    }

    /**
     * Inicia el proceso de importación.
     */
    public function start(?ImportBatchContract $importBatch = null): bool
    {
        DB::beginTransaction();

        try {
            $typeImporter = $this->getTypeImporter();

            $typeImporter->importData($importBatch);
        } catch (\Exception $e) {
            /**
             * Transacción de reversión.
             */
            DB::rollBack();

            throw $e;
        } finally {
            /**
             * Confirmar transacción.
             */
            DB::commit();
        }

        return true;
    }

    /**
     * Vincular recursos de importación.
     */
    public function link(ImportBatchContract $importBatch): bool
    {
        DB::beginTransaction();

        try {
            $typeImporter = $this->getTypeImporter();

            $typeImporter->linkData($importBatch);
        } catch (\Exception $e) {
            /**
             * Transacción de reversión.
             */
            DB::rollBack();

            throw $e;
        } finally {
            /**
             * Confirmar transacción.
             */
            DB::commit();
        }

        return true;
    }

    /**
     * Recursos de importación de índice.
     */
    public function index(ImportBatchContract $importBatch): bool
    {
        DB::beginTransaction();

        try {
            $typeImporter = $this->getTypeImporter();

            $typeImporter->indexData($importBatch);
        } catch (\Exception $e) {
            /**
             * Transacción de reversión.
             */
            DB::rollBack();

            throw $e;
        } finally {
            /**
             * Confirmar transacción.
             */
            DB::commit();
        }

        return true;
    }

    /**
     * Inició el proceso de importación.
     */
    public function started(): void
    {
        $import = $this->importRepository->update([
            'state' => self::STATE_PROCESSING,
            'started_at' => now(),
            'summary' => [],
        ], $this->import->id);

        $this->setImport($import);

        Event::dispatch('data_transfer.imports.started', $import);
    }

    /**
     * Inició el proceso de vinculación de importación.
     */
    public function linking(): void
    {
        $import = $this->importRepository->update([
            'state' => self::STATE_LINKING,
        ], $this->import->id);

        $this->setImport($import);

        Event::dispatch('data_transfer.imports.linking', $import);
    }

    /**
     * Inició el proceso de indexación de importaciones.
     */
    public function indexing(): void
    {
        $import = $this->importRepository->update([
            'state' => self::STATE_INDEXING,
        ], $this->import->id);

        $this->setImport($import);

        Event::dispatch('data_transfer.imports.indexing', $import);
    }

    /**
     * Inicie el proceso de importación.
     */
    public function completed(): void
    {
        $summary = $this->importBatchRepository
            ->select(
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."created"\'))) AS created'),
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."updated"\'))) AS updated'),
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."deleted"\'))) AS deleted'),
            )
            ->where('import_id', $this->import->id)
            ->groupBy('import_id')
            ->first()
            ->toArray();

        $import = $this->importRepository->update([
            'state' => self::STATE_COMPLETED,
            'summary' => $summary,
            'completed_at' => now(),
        ], $this->import->id);

        $this->setImport($import);

        Event::dispatch('data_transfer.imports.completed', $import);
    }

    /**
     * Devuelve estadísticas de importación.
     */
    public function stats(string $state): array
    {
        $total = $this->import->batches->count();

        $completed = $this->import->batches->where('state', $state)->count();

        $progress = $total
            ? round($completed / $total * 100)
            : 0;

        $summary = $this->importBatchRepository
            ->select(
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."created"\'))) AS created'),
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."updated"\'))) AS updated'),
                DB::raw('SUM(json_unquote(json_extract(summary, \'$."deleted"\'))) AS deleted'),
            )
            ->where('import_id', $this->import->id)
            ->where('state', $state)
            ->groupBy('import_id')
            ->first()
            ?->toArray();

        return [
            'batches' => [
                'total' => $total,
                'completed' => $completed,
                'remaining' => $total - $completed,
            ],
            'progress' => $progress,
            'summary' => $summary ?? [
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
            ],
        ];
    }

    /**
     * Devuelve todos los errores agrupados por código de error.
     */
    public function getFormattedErrors(): array
    {
        $errors = [];

        foreach ($this->errorHelper->getAllErrorsGroupedByCode() as $groupedErrors) {
            foreach ($groupedErrors as $errorMessage => $rowNumbers) {
                if (! empty($rowNumbers)) {
                    $errors[] = 'Row(s) '.implode(', ', $rowNumbers).': '.$errorMessage;
                } else {
                    $errors[] = $errorMessage;
                }
            }
        }

        return $errors;
    }

    /**
     * Carga el informe de errores y guarda la ruta a la base de datos.
     */
    public function uploadErrorReport(): ?string
    {
        /**
         * Devuelve nulo si no hay errores.
         */
        if (! $this->errorHelper->getErrorsCount()) {
            return null;
        }

        /**
         * Devuelve nulo si no hay filas no válidas.
         */
        if (! $this->errorHelper->getInvalidRowsCount()) {
            return null;
        }

        $errors = $this->errorHelper->getAllErrors();

        $source = $this->getTypeImporter()->getSource();

        $source->rewind();

        $spreadsheet = new Spreadsheet;

        $sheet = $spreadsheet->getActiveSheet();

        /**
         * Agregue encabezados con una columna de error adicional.
         */
        $sheet->fromArray(
            [array_merge($source->getColumnNames(), [
                'error',
            ])],
            null,
            'A1'
        );

        $rowNumber = 2;

        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $source->next();

                continue;
            }

            $rowErrors = $errors[$source->getCurrentRowNumber()] ?? [];

            if (! empty($rowErrors)) {
                $rowErrors = Arr::pluck($rowErrors, 'message');
            }

            $rowData[] = implode('|', $rowErrors);

            $sheet->fromArray([$rowData], null, 'A'.$rowNumber++);

            $source->next();
        }

        $fileType = pathinfo($this->import->file_path, PATHINFO_EXTENSION);

        switch ($fileType) {
            case 'csv':
                $writer = new Csv($spreadsheet);

                $writer->setDelimiter($this->import->field_separator);

                break;

            case 'xls':
                $writer = new Xls($spreadsheet);

            case 'xlsx':
                $writer = new Xlsx($spreadsheet);

                break;

            default:
                throw new \InvalidArgumentException("Unsupported file type: $fileType");
        }

        $errorFilePath = 'imports/'.time().'-error-report.'.$fileType;

        $writer->save(Storage::disk('public')->path($errorFilePath));

        return $errorFilePath;
    }

    /**
     * Valida el archivo fuente y devuelve el resultado de la validación.
     */
    public function getTypeImporter(): AbstractImporter
    {
        if (! $this->typeImporter) {
            $importerConfig = config('importers.'.$this->import->type);

            $this->typeImporter = app()->make($importerConfig['importer'])
                ->setImport($this->import)
                ->setErrorHelper($this->errorHelper);
        }

        return $this->typeImporter;
    }

    /**
     * Devuelve el número de filas marcadas.
     */
    public function getProcessedRowsCount(): int
    {
        return $this->getTypeImporter()->getProcessedRowsCount();
    }

    /**
     * ¿Se requiere un recurso de vinculación para la operación de importación?
     */
    public function isLinkingRequired(): bool
    {
        return $this->getTypeImporter()->isLinkingRequired();
    }

    /**
     * ¿Se requiere un recurso de indexación para la operación de importación?
     */
    public function isIndexingRequired(): bool
    {
        return $this->getTypeImporter()->isIndexingRequired();
    }
}
