<?php

namespace Aether\Admin\Http\Controllers;

use Illuminate\Support\Facades\Crypt;

class DataGridController extends Controller
{
    /**
     * Buscar.
     */
    public function lookUp()
    {
        /**
         * Validación de parámetros.
         */
        $params = $this->validate(request(), [
            'datagrid_id' => ['required'],
            'column' => ['required'],
            'search' => ['required', 'min:2'],
        ]);

        /**
         * Preparando la instancia del datagrid y solo las columnas.
         */
        $datagrid = app(Crypt::decryptString($params['datagrid_id']));
        $datagrid->prepareColumns();

        /**
         * Encontrar la primera columna de la colección.
         */
        $column = collect($datagrid->getColumns())->map(fn ($column) => $column->toArray())->where('index', $params['column'])->firstOrFail();

        /**
         * Obtención según las opciones de columna.
         */
        return app($column['filterable_options']['repository'])
            ->select([$column['filterable_options']['column']['label'].' as label', $column['filterable_options']['column']['value'].' as value'])
            ->where($column['filterable_options']['column']['label'], 'LIKE', '%'.$params['search'].'%')
            ->get();
    }
}
