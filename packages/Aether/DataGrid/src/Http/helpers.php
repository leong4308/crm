<?php

use Aether\DataGrid\DataGrid;
use Aether\DataGrid\Exceptions\InvalidDataGridException;

if (! function_exists('datagrid')) {
    /**
     * Ayudante de cuadrícula de datos.
     */
    function datagrid(string $datagridClass): DataGrid
    {
        if (! is_subclass_of($datagridClass, DataGrid::class)) {
            throw new InvalidDataGridException("'{$datagridClass}' must extend the '".DataGrid::class."' class.");
        }

        return app($datagridClass);
    }
}
