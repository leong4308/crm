<?php

namespace Aether\DataGrid\Enums;

enum FilterTypeEnum: string
{
    /**
     * Desplegable.
     */
    case DROPDOWN = 'dropdown';

    /**
     * Rango de fechas.
     */
    case DATE_RANGE = 'date_range';

    /**
     * Rango de fecha y hora.
     */
    case DATETIME_RANGE = 'datetime_range';
}
