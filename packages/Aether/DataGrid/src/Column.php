<?php

namespace Aether\DataGrid;

use Aether\DataGrid\Enums\ColumnTypeEnum;
use Aether\DataGrid\Exceptions\InvalidColumnException;

class Column
{
    /**
     * Índice de la columna.
     */
    protected string $index;

    /**
     * Etiqueta de la columna.
     */
    protected string $label;

    /**
     * Tipo de columna.
     */
    protected string $type;

    /**
     * Capacidad de búsqueda de la columna.
     */
    protected bool $searchable = false;

    /**
     * Filtrabilidad de la columna.
     */
    protected bool $filterable = false;

    /**
     * Tipo filtrable de columna.
     */
    protected ?string $filterableType = null;

    /**
     * Opciones filtrables de la columna.
     */
    protected array $filterableOptions = [];

    /**
     * Las columnas permiten múltiples valores.
     */
    protected bool $allowMultipleValues = true;

    /**
     * Ordenabilidad de la columna.
     */
    protected bool $sortable = false;

    /**
     * Exportabilidad de la columna.
     */
    protected bool $exportable = true;

    /**
     * Visibilidad de la columna.
     */
    protected bool $visibility = true;

    /**
     * Cierre de columna.
     */
    protected mixed $closure = null;

    /**
     * Nombre de columna de la tabla completamente calificado.
     */
    protected $columnName;

    /**
     * Cree una instancia de columna.
     */
    public function __construct(array $column)
    {
        $this->init($column);
    }

    /**
     * Inicialice todas las configuraciones necesarias para las columnas.
     */
    public function init(array $column): void
    {
        $this->setIndex($column['index']);

        $this->setLabel($column['label']);

        $this->setType($column['type']);

        $this->setSearchable($column['searchable'] ?? $this->searchable);

        $this->setFilterable($column['filterable'] ?? $this->filterable);

        $this->setFilterableType($column['filterable_type'] ?? $this->filterableType);

        $this->setFilterableOptions($column['filterable_options'] ?? $this->filterableOptions);

        $this->setAllowMultipleValues($column['allow_multiple_values'] ?? $this->allowMultipleValues);

        $this->setSortable($column['sortable'] ?? $this->sortable);

        $this->setVisibility($column['visibility'] ?? $this->visibility);

        $this->setClosure($column['closure'] ?? $this->closure);

        $this->setColumnName($this->index);
    }

    /**
     * Establecer índice.
     */
    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    /**
     * Obtener índice.
     */
    public function getIndex(): string
    {
        return $this->index;
    }

    /**
     * Establecer etiqueta.
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Obtener etiqueta.
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Tipo de conjunto.
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Obtener tipo.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Establecer búsqueda.
     */
    public function setSearchable(bool $searchable): void
    {
        $this->searchable = $searchable;
    }

    /**
     * Sea buscable.
     */
    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Establecer filtrable.
     */
    public function setFilterable(bool $filterable): void
    {
        $this->filterable = $filterable;
    }

    /**
     * Sea filtrable.
     */
    public function getFilterable(): bool
    {
        return $this->filterable;
    }

    /**
     * Establecer tipo filtrable.
     */
    public function setFilterableType(?string $filterableType): void
    {
        $this->filterableType = $filterableType;
    }

    /**
     * Obtener tipo filtrable.
     */
    public function getFilterableType(): ?string
    {
        return $this->filterableType;
    }

    /**
     * Establecer opciones filtrables.
     */
    public function setFilterableOptions(mixed $filterableOptions): void
    {
        if ($filterableOptions instanceof \Closure) {
            $filterableOptions = $filterableOptions();
        }

        $this->filterableOptions = $filterableOptions;
    }

    /**
     * Obtenga opciones filtrables.
     */
    public function getFilterableOptions(): array
    {
        return $this->filterableOptions;
    }

    /**
     * Establecer permitir múltiples valores.
     */
    public function setAllowMultipleValues(bool $allowMultipleValues): void
    {
        $this->allowMultipleValues = $allowMultipleValues;
    }

    /**
     * Obtenga permitir múltiples valores.
     */
    public function getAllowMultipleValues(): bool
    {
        return $this->allowMultipleValues;
    }

    /**
     * Establecer ordenable.
     */
    public function setSortable(?bool $sortable = null): void
    {
        $this->sortable = $sortable;
    }

    /**
     * Vuélvete ordenable.
     */
    public function getSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Conjunto exportable.
     */
    public function setExportable(bool $exportable): void
    {
        $this->exportable = $exportable;
    }

    /**
     * Vuélvete exportable.
     */
    public function getExportable(): bool
    {
        return $this->exportable;
    }

    /**
     * Establecer visibilidad.
     */
    public function setVisibility(bool $visibility): void
    {
        $this->visibility = $visibility;
    }

    /**
     * Consigue visibilidad.
     */
    public function getVisibility(): bool
    {
        return $this->visibility;
    }

    /**
     * Cierre del conjunto.
     */
    public function setClosure(mixed $closure): void
    {
        $this->closure = $closure;
    }

    /**
     * Consiga un cierre.
     */
    public function getClosure(): mixed
    {
        return $this->closure;
    }

    /**
     * Defina el nombre de la columna de la tabla. Inicialmente, coincidirá con el índice. Sin embargo, después de agregar un alias,
     * el nombre de la columna puede cambiar.
     */
    public function setColumnName(mixed $columnName): void
    {
        $this->columnName = $columnName;
    }

    /**
     * Obtenga el nombre de la columna de la tabla.
     */
    public function getColumnName(): mixed
    {
        return $this->columnName;
    }

    /**
     * Para formar.
     */
    public function toArray(): array
    {
        return [
            'index' => $this->index,
            'label' => $this->label,
            'type' => $this->type,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'filterable_type' => $this->filterableType,
            'filterable_options' => $this->filterableOptions,
            'allow_multiple_values' => $this->allowMultipleValues,
            'sortable' => $this->sortable,
            'visibility' => $this->visibility,
        ];
    }

    /**
     * Validar la columna.
     */
    public static function validate(array $column): void
    {
        if (empty($column['index'])) {
            throw new InvalidColumnException('The `index` key is required. Ensure that the `index` key is present in all calls to the `addColumn` method.');
        }

        if (empty($column['label'])) {
            throw new InvalidColumnException('The `label` key is required. Ensure that the `label` key is present in all calls to the `addColumn` method.');
        }

        if (empty($column['type'])) {
            throw new InvalidColumnException('The `type` key is required. Ensure that the `type` key is present in all calls to the `addColumn` method.');
        }
    }

    /**
     * Resuelva la clase de tipo de columna.
     */
    public static function resolveType(array $column): self
    {
        self::validate($column);

        $columnTypeClass = ColumnTypeEnum::getClassName($column['type']);

        return new $columnTypeClass($column);
    }
}
