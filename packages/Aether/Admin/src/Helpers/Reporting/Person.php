<?php

namespace Aether\Admin\Helpers\Reporting;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Aether\Contact\Repositories\PersonRepository;

class Person extends AbstractReporting
{
    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository)
    {
        parent::__construct();
    }

    /**
     * Recupera el total de personas y su progreso.
     */
    public function getTotalPersonsProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalPersons($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalPersons($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el total de personas por fecha.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getTotalPersons($startDate, $endDate): int
    {
        return $this->personRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Obtiene los mejores clientes por ingresos.
     *
     * @param  int  $limit
     */
    public function getTopCustomersByRevenue($limit = null): Collection
    {
        $tablePrefix = DB::getTablePrefix();

        $items = $this->personRepository
            ->resetModel()
            ->leftJoin('leads', 'persons.id', '=', 'leads.person_id')
            ->select('persons.id', 'persons.name', 'persons.emails', 'persons.contact_numbers')
            ->addSelect(DB::raw('SUM('.$tablePrefix.'leads.lead_value) as revenue'))
            ->whereBetween('leads.closed_at', [$this->startDate, $this->endDate])
            ->having(DB::raw('SUM('.$tablePrefix.'leads.lead_value)'), '>', 0)
            ->groupBy('persons.id', 'persons.name', DB::raw('persons.emails::text'), DB::raw('persons.contact_numbers::text'))
            ->orderBy('revenue', 'DESC')
            ->limit($limit)
            ->get();

        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'emails' => $item->emails,
                'contact_numbers' => $item->contact_numbers,
                'revenue' => $item->revenue,
                'formatted_revenue' => core()->formatBasePrice($item->revenue),
            ];
        });

        return $items;
    }
}
