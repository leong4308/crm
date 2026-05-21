<?php

namespace Aether\Admin\DataGrids\Settings;

use Aether\DataGrid\DataGrid;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GroupDataGrid extends DataGrid
{
    /**
     * Preparar el generador de consultas.
     */
    public function prepareQueryBuilder(): Builder
    {
        $queryBuilder = DB::table('groups')
            ->addSelect(
                'groups.id',
                'groups.name',
                'groups.description'
            );

        $this->addFilter('id', 'groups.id');

        return $queryBuilder;
    }

    /**
     * Preparar columnas.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('admin::app.settings.groups.index.datagrid.id'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'type' => 'string',
            'label' => trans('admin::app.settings.groups.index.datagrid.name'),
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'description',
            'label' => trans('admin::app.settings.groups.index.datagrid.description'),
            'type' => 'string',
            'sortable' => false,
        ]);
    }

    /**
     * Preparar acciones.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('settings.user.groups.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('admin::app.settings.groups.index.datagrid.edit'),
                'method' => 'GET',
                'url' => fn ($row) => route('admin.settings.groups.edit', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('settings.user.groups.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('admin::app.settings.groups.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => fn ($row) => route('admin.settings.groups.delete', $row->id),
            ]);
        }
    }
}
