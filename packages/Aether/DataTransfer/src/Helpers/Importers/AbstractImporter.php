<?php

namespace Aether\DataTransfer\Helpers\Importers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Contracts\Validations\Decimal;
use Aether\DataTransfer\Contracts\Import as ImportContract;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Aether\DataTransfer\Helpers\Error;
use Aether\DataTransfer\Helpers\Import;
use Aether\DataTransfer\Helpers\Source;
use Aether\DataTransfer\Jobs\Import\Completed as CompletedJob;
use Aether\DataTransfer\Jobs\Import\ImportBatch as ImportBatchJob;
use Aether\DataTransfer\Jobs\Import\IndexBatch as IndexBatchJob;
use Aether\DataTransfer\Jobs\Import\Indexing as IndexingJob;
use Aether\DataTransfer\Jobs\Import\LinkBatch as LinkBatchJob;
use Aether\DataTransfer\Jobs\Import\Linking as LinkingJob;
use Aether\DataTransfer\Repositories\ImportBatchRepository;

abstract class AbstractImporter
{
    /**
     * Código de error para excepción del sistema.
     */
    public const ERROR_CODE_SYSTEM_EXCEPTION = 'system_exception';

    /**
     * Código de error para la columna no encontrada.
     */
    public const ERROR_CODE_COLUMN_NOT_FOUND = 'column_not_found';

    /**
     * Código de error para encabezado de columna vacío.
     */
    public const ERROR_CODE_COLUMN_EMPTY_HEADER = 'column_empty_header';

    /**
     * Código de error para el nombre de la columna no válido.
     */
    public const ERROR_CODE_COLUMN_NAME_INVALID = 'column_name_invalid';

    /**
     * Código de error para atributo no válido.
     */
    public const ERROR_CODE_INVALID_ATTRIBUTE = 'invalid_attribute_name';

    /**
     * Código de error por cotizaciones incorrectas.
     */
    public const ERROR_CODE_WRONG_QUOTES = 'wrong_quotes';

    /**
     * Código de error para número de columnas incorrecto.
     */
    public const ERROR_CODE_COLUMNS_NUMBER = 'wrong_columns_number';

    /**
     * Plantillas de mensajes de error.
     */
    protected array $errorMessages = [
        self::ERROR_CODE_SYSTEM_EXCEPTION => 'data_transfer::app.validation.errors.system',
        self::ERROR_CODE_COLUMN_NOT_FOUND => 'data_transfer::app.validation.errors.column-not-found',
        self::ERROR_CODE_COLUMN_EMPTY_HEADER => 'data_transfer::app.validation.errors.column-empty-headers',
        self::ERROR_CODE_COLUMN_NAME_INVALID => 'data_transfer::app.validation.errors.column-name-invalid',
        self::ERROR_CODE_INVALID_ATTRIBUTE => 'data_transfer::app.validation.errors.invalid-attribute',
        self::ERROR_CODE_WRONG_QUOTES => 'data_transfer::app.validation.errors.wrong-quotes',
        self::ERROR_CODE_COLUMNS_NUMBER => 'data_transfer::app.validation.errors.column-numbers',
    ];

    public const BATCH_SIZE = 100;

    /**
     * ¿Se requiere vinculación?
     */
    protected bool $linkingRequired = false;

    /**
     * ¿Se requiere indexación?
     */
    protected bool $indexingRequired = false;

    /**
     * Instancia de ayuda de error.
     *
     * @var Error
     */
    protected $errorHelper;

    /**
     * Importar instancia.
     */
    protected ImportContract $import;

    /**
     * Instancia fuente.
     *
     * @var Source
     */
    protected $source;

    /**
     * Nombres de columnas válidos.
     */
    protected array $validColumnNames = [];

    /**
     * Matriz de números de filas validadas como claves y valores booleanos VERDADERO.
     */
    protected array $validatedRows = [];

    /**
     * Número de filas procesadas por validación.
     */
    protected int $processedRowsCount = 0;

    /**
     * Número de elementos creados.
     */
    protected int $createdItemsCount = 0;

    /**
     * Número de elementos actualizados.
     */
    protected int $updatedItemsCount = 0;

    /**
     * Número de elementos eliminados.
     */
    protected int $deletedItemsCount = 0;

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository
    ) {}

    /**
     * Validar fila de datos.
     */
    abstract public function validateRow(array $rowData, int $rowNumber): bool;

    /**
     * Importar filas de datos.
     */
    abstract public function importBatch(ImportBatchContract $importBatchContract): bool;

    /**
     * Inicialice los mensajes de error del producto.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->errorMessages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }
    }

    /**
     * Importar instancia.
     */
    public function setImport(ImportContract $import): self
    {
        $this->import = $import;

        return $this;
    }

    /**
     * Importar instancia.
     *
     * @param  Source  $errorHelper
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Importar instancia.
     *
     * @param  Error  $errorHelper
     */
    public function setErrorHelper($errorHelper): self
    {
        $this->errorHelper = $errorHelper;

        $this->initErrorMessages();

        return $this;
    }

    /**
     * Importar instancia.
     *
     * @return Source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Recuperar nombres de columnas válidos.
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * Validar datos.
     */
    public function validateData(): void
    {
        Event::dispatch('data_transfer.imports.validate.before', $this->import);

        $errors = [];

        $absentColumns = array_diff($this->permanentAttributes, $columns = $this->getSource()->getColumnNames());

        if (! empty($absentColumns)) {
            $errors[self::ERROR_CODE_COLUMN_NOT_FOUND] = $absentColumns;
        }

        foreach ($columns as $columnNumber => $columnName) {
            if (empty($columnName)) {
                $errors[self::ERROR_CODE_COLUMN_EMPTY_HEADER][] = $columnNumber + 1;
            } elseif (! preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                $errors[self::ERROR_CODE_COLUMN_NAME_INVALID][] = $columnName;
            } elseif (! in_array($columnName, $this->getValidColumnNames())) {
                $errors[self::ERROR_CODE_INVALID_ATTRIBUTE][] = $columnName;
            }
        }

        /**
         * Errores al agregar columnas.
         */
        foreach ($errors as $errorCode => $error) {
            $this->addErrors($errorCode, $error);
        }

        if (! $this->errorHelper->getErrorsCount()) {
            $this->saveValidatedBatches();
        }

        Event::dispatch('data_transfer.imports.validate.after', $this->import);
    }

    /**
     * Guardar lotes validados.
     */
    protected function saveValidatedBatches(): self
    {
        $source = $this->getSource();

        $batchRows = [];

        $source->rewind();

        /**
         * Limpiar lotes guardados anteriores.
         */
        $this->importBatchRepository->deleteWhere([
            'import_id' => $this->import->id,
        ]);

        while (
            $source->valid()
            || count($batchRows)
        ) {
            if (
                count($batchRows) == self::BATCH_SIZE
                || ! $source->valid()
            ) {
                $this->importBatchRepository->create([
                    'import_id' => $this->import->id,
                    'data' => $batchRows,
                ]);

                $batchRows = [];
            }

            if ($source->valid()) {
                $rowData = $source->current();

                if ($this->validateRow($rowData, $source->getCurrentRowNumber())) {
                    $batchRows[] = $this->prepareRowForDb($rowData);
                }

                $this->processedRowsCount++;

                $source->next();
            }
        }

        return $this;
    }

    /**
     * Preparar reglas de validación.
     */
    public function getValidationRules(string $entityType, array $rowData): array
    {
        if (empty($entityType)) {
            return [];
        }

        $rules = [];

        $attributes = $this->attributeRepository->scopeQuery(fn ($query) => $query->whereIn('code', array_keys($rowData))->where('entity_type', $entityType))->get();

        foreach ($attributes as $attribute) {
            $validations = [];

            if ($attribute->type == 'boolean') {
                continue;
            } elseif ($attribute->type == 'address') {
                if (! $attribute->is_required) {
                    continue;
                }

                $validations = [
                    $attribute->code.'.address' => 'required',
                    $attribute->code.'.country' => 'required',
                    $attribute->code.'.state' => 'required',
                    $attribute->code.'.city' => 'required',
                    $attribute->code.'.postcode' => 'required',
                ];
            } elseif ($attribute->type == 'email') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable', 'email'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } elseif ($attribute->type == 'phone') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } else {
                $validations[$attribute->code] = [$attribute->is_required ? 'required' : 'nullable'];

                if ($attribute->type == 'text' && $attribute->validation) {
                    array_push($validations[$attribute->code],
                        $attribute->validation == 'decimal'
                        ? new Decimal
                        : $attribute->validation
                    );
                }

                if ($attribute->type == 'price') {
                    array_push($validations[$attribute->code], new Decimal);
                }
            }

            if ($attribute->is_unique) {
                array_push($validations[in_array($attribute->type, ['email', 'phone'])
                    ? $attribute->code.'.*.value'
                    : $attribute->code
                ], function ($field, $value, $fail) use ($attribute) {
                    if (! $this->attributeValueRepository->isValueUnique(null, $attribute->entity_type, $attribute, $field)) {
                        $fail(trans('data_transfer::app.validation.errors.already-exists', ['attribute' => $attribute->name]));
                    }
                });
            }

            $rules = [
                ...$rules,
                ...$validations,
            ];
        }

        return $rules;
    }

    /**
     * Inicie el proceso de importación.
     */
    public function importData(?ImportBatchContract $importBatch = null): bool
    {
        if ($importBatch) {
            $this->importBatch($importBatch);

            return true;
        }

        $typeBatches = [];

        foreach ($this->import->batches as $batch) {
            $typeBatches['import'][] = new ImportBatchJob($batch);

            if ($this->isLinkingRequired()) {
                $typeBatches['link'][] = new LinkBatchJob($batch);
            }

            if ($this->isIndexingRequired()) {
                $typeBatches['index'][] = new IndexBatchJob($batch);
            }
        }

        $chain[] = Bus::batch($typeBatches['import']);

        if (! empty($typeBatches['link'])) {
            $chain[] = new LinkingJob($this->import);

            $chain[] = Bus::batch($typeBatches['link']);
        }

        if (! empty($typeBatches['index'])) {
            $chain[] = new IndexingJob($this->import);

            $chain[] = Bus::batch($typeBatches['index']);
        }

        $chain[] = new CompletedJob($this->import);

        Bus::chain($chain)->dispatch();

        return true;
    }

    /**
     * Vincular datos de recursos.
     */
    public function linkData(ImportBatchContract $importBatch): bool
    {
        $this->linkBatch($importBatch);

        return true;
    }

    /**
     * Indexar datos de recursos.
     */
    public function indexData(ImportBatchContract $importBatch): bool
    {
        $this->indexBatch($importBatch);

        return true;
    }

    /**
     * Agregue errores al agregador de errores.
     */
    protected function addErrors(string $code, mixed $errors): void
    {
        $this->errorHelper->addError(
            $code,
            null,
            implode('", "', $errors)
        );
    }

    /**
     * Agregue la fila omitida.
     *
     * @param  int|null  $rowNumber
     * @param  string|null  $columnName
     * @param  string|null  $errorMessage
     * @return $this
     */
    protected function skipRow($rowNumber, string $errorCode, $columnName = null, $errorMessage = null): self
    {
        $this->errorHelper->addError(
            $errorCode,
            $rowNumber,
            $columnName,
            $errorMessage
        );

        $this->errorHelper->addRowToSkip($rowNumber);

        return $this;
    }

    /**
     * Prepare los datos de la fila para guardarlos en la base de datos.
     */
    protected function prepareRowForDb(array $rowData): array
    {
        $rowData = array_map(function ($value) {
            return $value === '' ? null : $value;
        }, $rowData);

        return $rowData;
    }

    /**
     * Devuelve el número de filas marcadas.
     */
    public function getProcessedRowsCount(): int
    {
        return $this->processedRowsCount;
    }

    /**
     * Devuelve el número de recuento de elementos creados.
     */
    public function getCreatedItemsCount(): int
    {
        return $this->createdItemsCount;
    }

    /**
     * Devuelve el número de recuento de elementos actualizados.
     */
    public function getUpdatedItemsCount(): int
    {
        return $this->updatedItemsCount;
    }

    /**
     * Devuelve el número de elementos eliminados.
     */
    public function getDeletedItemsCount(): int
    {
        return $this->deletedItemsCount;
    }

    /**
     * ¿Se requiere un recurso de vinculación para la operación de importación?
     */
    public function isLinkingRequired(): bool
    {
        if ($this->import->action == Import::ACTION_DELETE) {
            return false;
        }

        return $this->linkingRequired;
    }

    /**
     * ¿Se requiere un recurso de indexación para la operación de importación?
     */
    public function isIndexingRequired(): bool
    {
        if ($this->import->action == Import::ACTION_DELETE) {
            return false;
        }

        return $this->indexingRequired;
    }
}
