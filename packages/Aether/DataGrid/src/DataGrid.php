<?php

namespace Aether\DataGrid;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Aether\DataGrid\Enums\ColumnTypeEnum;
use Aether\DataGrid\Exports\DataGridExport;

abstract class DataGrid
{
    /**
     * Columna primaria.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Columna de clasificación predeterminada de la cuadrícula de datos.
     *
     * @var ?string
     */
    protected $sortColumn;

    /**
     * Orden de clasificación predeterminado de la cuadrícula de datos.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Elementos predeterminados por página.
     *
     * @var int
     */
    protected $itemsPerPage = 10;

    /**
     * Opciones por página.
     *
     * @var array
     */
    protected $perPageOptions = [10, 20, 30, 40, 50];

    /**
     * Columnas.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Comportamiento.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * Acción de masas.
     *
     * @var array
     */
    protected $massActions = [];

    /**
     * Instancia del generador de consultas.
     *
     * @var object
     */
    protected $queryBuilder;

    /**
     * Instancia de paginador.
     */
    protected LengthAwarePaginator $paginator;

    /**
     * Exportable.
     */
    protected bool $exportable = false;

    /**
     * Exportar metainformación.
     */
    protected mixed $exportFile = null;

    /**
     * Preparar el generador de consultas.
     */
    abstract public function prepareQueryBuilder();

    /**
     * Preparar columnas.
     */
    abstract public function prepareColumns();

    /**
     * Preparar acciones.
     */
    public function prepareActions() {}

    /**
     * Preparar acciones masivas.
     */
    public function prepareMassActions() {}

    /**
     * Obtener columnas.
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Obtener acciones.
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Consigue acciones masivas.
     */
    public function getMassActions(): array
    {
        return $this->massActions;
    }

    /**
     * Agregar columna.
     */
    public function addColumn(array $column): void
    {
        $this->dispatchEvent('columns.add.before', [$this, $column]);

        $this->columns[] = Column::resolveType($column);

        $this->dispatchEvent('columns.add.after', [$this, $this->columns[count($this->columns) - 1]]);
    }

    /**
     * Añadir acción.
     */
    public function addAction(array $action): void
    {
        $this->dispatchEvent('actions.add.before', [$this, $action]);

        $this->actions[] = new Action(
            index: $action['index'] ?? '',
            icon: $action['icon'] ?? '',
            title: $action['title'],
            method: $action['method'],
            url: $action['url'],
        );

        $this->dispatchEvent('actions.add.after', [$this, $this->actions[count($this->actions) - 1]]);
    }

    /**
     * Añadir acción masiva.
     */
    public function addMassAction(array $massAction): void
    {
        $this->dispatchEvent('mass_actions.add.before', [$this, $massAction]);

        $this->massActions[] = new MassAction(
            icon: $massAction['icon'] ?? '',
            title: $massAction['title'],
            method: $massAction['method'],
            url: $massAction['url'],
            options: $massAction['options'] ?? [],
        );

        $this->dispatchEvent('mass_actions.add.after', [$this, $this->massActions[count($this->massActions) - 1]]);
    }

    /**
     * Establecer generador de consultas.
     *
     * @param  mixed  $queryBuilder
     */
    public function setQueryBuilder($queryBuilder = null): void
    {
        $this->dispatchEvent('query_builder.set.before', [$this, $queryBuilder]);

        $this->queryBuilder = $queryBuilder ?: $this->prepareQueryBuilder();

        $this->dispatchEvent('query_builder.set.after', $this);
    }

    /**
     * Obtenga el generador de consultas.
     */
    public function getQueryBuilder(): mixed
    {
        return $this->queryBuilder;
    }

    /**
     * Mapee su filtro.
     */
    public function addFilter(string $datagridColumn, mixed $queryColumn): void
    {
        $this->dispatchEvent('filters.add.before', [$this, $datagridColumn, $queryColumn]);

        foreach ($this->columns as $column) {
            if ($column->getIndex() === $datagridColumn) {
                $column->setColumnName($queryColumn);

                break;
            }
        }

        $this->dispatchEvent('filters.add.after', [$this, $datagridColumn, $queryColumn]);
    }

    /**
     * Conjunto exportable.
     */
    public function setExportable(bool $exportable): void
    {
        $this->dispatchEvent('exportable.set.before', [$this, $exportable]);

        $this->exportable = $exportable;

        $this->dispatchEvent('exportable.set.after', $this);
    }

    /**
     * Vuélvete exportable.
     */
    public function getExportable(): bool
    {
        return $this->exportable;
    }

    /**
     * Establecer archivo de exportación.
     *
     * @param  string  $format
     * @return void
     */
    public function setExportFile($format = 'csv')
    {
        $this->dispatchEvent('export_file.set.before', [$this, $format]);

        $this->setExportable(true);

        $this->exportFile = Excel::download(new DataGridExport($this), Str::random(36).'.'.$format);

        $this->dispatchEvent('export_file.set.after', $this);
    }

    /**
     * Descargar archivo de exportación.
     *
     * @return BinaryFileResponse
     */
    public function downloadExportFile()
    {
        return $this->exportFile;
    }

    /**
     * Procese la cuadrícula de datos.
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function process()
    {
        $this->prepare();

        if ($this->getExportable()) {
            return $this->downloadExportFile();
        }

        return response()->json($this->formatData());
    }

    /**
     * A json. El motivo de su desaprobación es que no es una acción que devuelva JSON; en cambio,
     * es un método de proceso que devuelve una descarga y una respuesta JSON.
     *
     * @deprecated
     *
     * @return BinaryFileResponse|JsonResponse
     */
    public function toJson()
    {
        $this->prepare();

        if ($this->getExportable()) {
            return $this->downloadExportFile();
        }

        return response()->json($this->formatData());
    }

    /**
     * Solicitud validada.
     */
    protected function validatedRequest(): array
    {
        request()->validate([
            'filters' => ['sometimes', 'required', 'array'],
            'sort' => ['sometimes', 'required', 'array'],
            'pagination' => ['sometimes', 'required', 'array'],
            'export' => ['sometimes', 'required', 'boolean'],
            'format' => ['sometimes', 'required', 'in:csv,xls,xlsx'],
        ]);

        return request()->only(['filters', 'sort', 'pagination', 'export', 'format']);
    }

    /**
     * Procesar todos los filtros solicitados.
     *
     * @return Builder
     */
    protected function processRequestedFilters(array $requestedFilters)
    {
        foreach ($requestedFilters as $requestedColumn => $requestedValues) {
            if ($requestedColumn === 'all') {
                $this->queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
                    foreach ($requestedValues as $value) {
                        collect($this->columns)
                            ->filter(fn ($column) => $column->getSearchable() && ! in_array($column->getType(), [
                                ColumnTypeEnum::BOOLEAN->value,
                                ColumnTypeEnum::AGGREGATE->value,
                            ]))
                            ->each(fn ($column) => $scopeQueryBuilder->orWhere($column->getColumnName(), 'LIKE', '%'.$value.'%'));
                    }
                });
            } else {
                collect($this->columns)
                    ->first(fn ($column) => $column->getIndex() === $requestedColumn)
                    ->processFilter($this->queryBuilder, $requestedValues);
            }
        }

        return $this->queryBuilder;
    }

    /**
     * Proceso de clasificación solicitada.
     *
     * @return Builder
     */
    protected function processRequestedSorting($requestedSort)
    {
        if (! $this->sortColumn) {
            $this->sortColumn = $this->primaryColumn;
        }

        return $this->queryBuilder->orderBy($requestedSort['column'] ?? $this->sortColumn, $requestedSort['order'] ?? $this->sortOrder);
    }

    /**
     * Proceso de paginación solicitada.
     */
    protected function processRequestedPagination($requestedPagination): LengthAwarePaginator
    {
        return $this->queryBuilder->paginate(
            $requestedPagination['per_page'] ?? $this->itemsPerPage,
            ['*'],
            'page',
            $requestedPagination['page'] ?? 1
        );
    }

    /**
     * Procesar solicitud paginada.
     */
    protected function processPaginatedRequest(array $requestedParams): void
    {
        $this->dispatchEvent('process_request.paginated.before', $this);

        $this->paginator = $this->processRequestedPagination($requestedParams['pagination'] ?? []);

        $this->dispatchEvent('process_request.paginated.after', $this);
    }

    /**
     * Procesar solicitud de exportación.
     */
    protected function processExportRequest(array $requestedParams): void
    {
        $this->dispatchEvent('process_request.export.before', $this);

        $this->setExportFile($requestedParams['format']);

        $this->dispatchEvent('process_request.export.after', $this);
    }

    /**
     * Solicitud de proceso.
     */
    protected function processRequest(): void
    {
        $this->dispatchEvent('process_request.before', $this);

        /**
         * Almacene todos los parámetros de solicitud en esta variable; Evite el uso de ayudantes de solicitud directa después.
         */
        $requestedParams = $this->validatedRequest();

        $this->queryBuilder = $this->processRequestedFilters($requestedParams['filters'] ?? []);

        $this->queryBuilder = $this->processRequestedSorting($requestedParams['sort'] ?? []);

        /**
         * El parámetro `export` se valida como booleano en `validatedRequest`. Una función "vacía" no funcionará,
         * ya que siempre se considerará verdadero debido a "0" y "1".
         */
        isset($requestedParams['export']) && (bool) $requestedParams['export']
            ? $this->processExportRequest($requestedParams)
            : $this->processPaginatedRequest($requestedParams);

        $this->dispatchEvent('process_request.after', $this);
    }

    /**
     * Prepare toda la configuración para datagrid.
     */
    protected function sanitizeRow($row): \stdClass
    {
        /**
         * Convierta stdClass en matriz.
         */
        $tempRow = json_decode(json_encode($row), true);

        foreach ($tempRow as $column => $value) {
            if (! is_string($tempRow[$column])) {
                continue;
            }

            if (is_array($value)) {
                return $this->sanitizeRow($tempRow[$column]);
            } else {
                $row->{$column} = strip_tags($value);
            }
        }

        return $row;
    }

    /**
     * Dar formato a las columnas.
     */
    protected function formatColumns(): array
    {
        return collect($this->columns)
            ->map(fn ($column) => $column->toArray())
            ->toArray();
    }

    /**
     * Acciones de formato.
     */
    protected function formatActions(): array
    {
        return collect($this->actions)
            ->map(fn ($action) => $action->toArray())
            ->toArray();
    }

    /**
     * Formatear acciones masivas.
     */
    protected function formatMassActions(): array
    {
        return collect($this->massActions)
            ->map(fn ($massAction) => $massAction->toArray())
            ->toArray();
    }

    /**
     * Dar formato a los registros.
     */
    protected function formatRecords($records): mixed
    {
        foreach ($records as $record) {
            $record = $this->sanitizeRow($record);

            foreach ($this->columns as $column) {
                if ($closure = $column->getClosure()) {
                    $record->{$column->getIndex()} = $closure($record);
                }
            }

            $record->actions = [];

            foreach ($this->actions as $index => $action) {
                $getUrl = $action->url;

                $record->actions[] = [
                    'index' => ! empty($action->index) ? $action->index : 'action_'.$index + 1,
                    'icon' => $action->icon,
                    'title' => $action->title,
                    'method' => $action->method,
                    'url' => $getUrl($record),
                ];
            }
        }

        return $records;
    }

    /**
     * Dar formato a los datos.
     */
    protected function formatData(): array
    {
        $paginator = $this->paginator->toArray();

        return [
            'id' => Crypt::encryptString(get_called_class()),
            'columns' => $this->formatColumns(),
            'actions' => $this->formatActions(),
            'mass_actions' => $this->formatMassActions(),
            'records' => $this->formatRecords($paginator['data']),
            'meta' => [
                'primary_column' => $this->primaryColumn,
                'from' => $paginator['from'],
                'to' => $paginator['to'],
                'total' => $paginator['total'],
                'per_page_options' => $this->perPageOptions,
                'per_page' => $paginator['per_page'],
                'current_page' => $paginator['current_page'],
                'last_page' => $paginator['last_page'],
            ],
        ];
    }

    /**
     * Evento de despacho.
     */
    protected function dispatchEvent(string $eventName, mixed $payload): void
    {
        $reflection = new \ReflectionClass($this);

        $datagridName = Str::snake($reflection->getShortName());

        Event::dispatch("datagrid.{$datagridName}.{$eventName}", $payload);
    }

    /**
     * Prepare toda la configuración para datagrid.
     */
    protected function prepare(): void
    {
        $this->dispatchEvent('prepare.before', $this);

        $this->prepareColumns();

        $this->dispatchEvent('columns.prepare.after', $this);

        $this->prepareActions();

        $this->dispatchEvent('actions.prepare.after', $this);

        $this->prepareMassActions();

        $this->dispatchEvent('mass_actions.prepare.after', $this);

        $this->setQueryBuilder();

        $this->dispatchEvent('query_builder.prepare.after', $this);

        $this->processRequest();

        $this->dispatchEvent('prepare.after', $this);
    }
}
