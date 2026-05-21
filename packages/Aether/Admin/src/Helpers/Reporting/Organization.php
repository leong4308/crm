<?php

namespace Aether\Admin\Helpers\Reporting;

use Aether\Contact\Repositories\OrganizationRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Organization extends AbstractReporting
{
    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct(protected OrganizationRepository $organizationRepository)
    {
        parent::__construct();
    }

    /**
     * Recupera organizaciones totales y su progreso.
     */
    public function getTotalOrganizationsProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalOrganizations($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalOrganizations($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el total de organizaciones por fecha.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getTotalOrganizations($startDate, $endDate): int
    {
        return $this->organizationRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Obtiene los mejores clientes por ingresos.
     *
     * @param  int  $limit
     */
    public function getTopOrganizationsByRevenue($limit = null): Collection
    {
        $tablePrefix = DB::getTablePrefix();

        $items = $this->organizationRepository
            ->resetModel()
            ->leftJoin('persons', 'organizations.id', '=', 'persons.organization_id')
            ->leftJoin('leads', 'persons.id', '=', 'leads.person_id')
            ->select('organizations.id', 'organizations.name')
            ->addSelect(DB::raw('SUM('.$tablePrefix.'leads.lead_value) as revenue'))
            ->whereBetween('leads.closed_at', [$this->startDate, $this->endDate])
            ->having(DB::raw('SUM('.$tablePrefix.'leads.lead_value)'), '>', 0)
            ->groupBy('organizations.id', 'organizations.name')
            ->orderBy('revenue', 'DESC')
            ->limit($limit)
            ->get();

        $items = $items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'revenue' => $item->revenue,
                'formatted_revenue' => core()->formatBasePrice($item->revenue),
            ];
        });

        return $items;
    }
}
