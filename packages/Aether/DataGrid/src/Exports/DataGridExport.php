<?php

namespace Aether\DataGrid\Exports;

use Aether\DataGrid\DataGrid;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataGridExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    /**
     * Crea una nueva instancia.
     *
     * @return void
     */
    public function __construct(protected DataGrid $datagrid) {}

    /**
     * Consulta.
     */
    public function query(): mixed
    {
        return $this->datagrid->getQueryBuilder();
    }

    /**
     * Encabezados.
     */
    public function headings(): array
    {
        return collect($this->datagrid->getColumns())
            ->filter(fn ($column) => $column->getExportable())
            ->map(fn ($column) => $column->getLabel())
            ->toArray();
    }

    /**
     * Asigne cada fila para exportar.
     */
    public function map(mixed $record): array
    {
        return collect($this->datagrid->getColumns())
            ->filter(fn ($column) => $column->getExportable())
            ->map(function ($column) use ($record) {
                $index = $column->getIndex();
                $value = $record->{$index};

                if (
                    in_array($index, ['emails', 'contact_numbers'])
                    && is_string($value)
                ) {
                    return $this->extractValuesFromJson($value);
                }

                return $value;
            })
            ->toArray();
    }

    /**
     * Extraiga campos de 'valor' de una cadena JSON.
     */
    protected function extractValuesFromJson(string $json): string
    {
        $items = json_decode($json, true);

        if (
            json_last_error() === JSON_ERROR_NONE
            && is_array($items)
        ) {
            return collect($items)->map(fn ($item) => "{$item['value']} ({$item['label']})")->implode(', ');
        }

        return $json;
    }
}
