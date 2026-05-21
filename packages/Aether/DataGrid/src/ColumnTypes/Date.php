<?php

namespace Aether\DataGrid\ColumnTypes;

use Aether\DataGrid\Column;
use Aether\DataGrid\Enums\DateRangeOptionEnum;
use Aether\DataGrid\Enums\FilterTypeEnum;
use Aether\DataGrid\Exceptions\InvalidColumnException;

class Date extends Column
{
    /**
     * Establecer tipo filtrable.
     */
    public function setFilterableType(?string $filterableType): void
    {
        if (
            $filterableType
            && ($filterableType !== FilterTypeEnum::DATE_RANGE->value)
        ) {
            throw new InvalidColumnException('Date filters will only work with `date_range` type. Either remove the `filterable_type` or set it to `date_range`.');
        }

        parent::setFilterableType($filterableType);
    }

    /**
     * Establecer opciones filtrables.
     */
    public function setFilterableOptions(mixed $filterableOptions): void
    {
        if (empty($filterableOptions)) {
            $filterableOptions = DateRangeOptionEnum::options();
        }

        parent::setFilterableOptions($filterableOptions);
    }

    /**
     * Filtro de proceso.
     */
    public function processFilter($queryBuilder, $requestedDates)
    {
        return $queryBuilder->where(function ($scopeQueryBuilder) use ($requestedDates) {
            if (is_string($requestedDates)) {
                $rangeOption = collect($this->filterableOptions)->firstWhere('name', $requestedDates);

                $requestedDates = ! $rangeOption
                    ? [[$requestedDates, $requestedDates]]
                    : [[$rangeOption['from'], $rangeOption['to']]];
            }

            foreach ($requestedDates as $value) {
                $scopeQueryBuilder->whereBetween($this->columnName, [
                    ($value[0] ?? '').' 00:00:01',
                    ($value[1] ?? '').' 23:59:59',
                ]);
            }
        });
    }
}
