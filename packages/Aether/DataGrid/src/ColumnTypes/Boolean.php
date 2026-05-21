<?php

namespace Aether\DataGrid\ColumnTypes;

use Aether\DataGrid\Column;
use Aether\DataGrid\Enums\FilterTypeEnum;
use Aether\DataGrid\Exceptions\InvalidColumnException;

class Boolean extends Column
{
    /**
     * Establecer tipo filtrable.
     */
    public function setFilterableType(?string $filterableType): void
    {
        if (
            $filterableType
            && ($filterableType !== FilterTypeEnum::DROPDOWN->value)
        ) {
            throw new InvalidColumnException('Boolean filters will only work with `dropdown` type. Either remove the `filterable_type` or set it to `dropdown`.');
        }

        if (! $filterableType) {
            $filterableType = FilterTypeEnum::DROPDOWN->value;
        }

        parent::setFilterableType($filterableType);
    }

    /**
     * Establecer opciones filtrables.
     */
    public function setFilterableOptions(mixed $filterableOptions): void
    {
        if (empty($filterableOptions)) {
            $filterableOptions = [
                [
                    'label' => trans('admin::app.components.datagrid.filters.boolean-options.true'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.components.datagrid.filters.boolean-options.false'),
                    'value' => 0,
                ],
            ];
        }

        parent::setFilterableOptions($filterableOptions);
    }

    /**
     * Filtro de proceso.
     */
    public function processFilter($queryBuilder, $requestedValues): mixed
    {
        return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedValues) {
            if (is_string($requestedValues)) {
                $scopeQueryBuilder->orWhere($this->columnName, $requestedValues);

                return;
            }

            foreach ($requestedValues as $value) {
                $scopeQueryBuilder->orWhere($this->columnName, $value);
            }
        });
    }
}
