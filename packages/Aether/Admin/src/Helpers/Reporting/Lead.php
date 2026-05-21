<?php

namespace Aether\Admin\Helpers\Reporting;

use Aether\Lead\Repositories\LeadRepository;
use Aether\Lead\Repositories\StageRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Lead extends AbstractReporting
{
    /**
     * Los identificadores del canal.
     */
    protected array $stageIds;

    /**
     * Los identificadores de todas las etapas.
     */
    protected array $allStageIds;

    /**
     * Los identificadores de etapa ganados.
     */
    protected array $wonStageIds;

    /**
     * Las identificaciones del escenario perdido.
     */
    protected array $lostStageIds;

    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected StageRepository $stageRepository
    ) {
        $this->allStageIds = $this->stageRepository->pluck('id')->toArray();

        $this->wonStageIds = $this->stageRepository->where('code', 'won')->pluck('id')->toArray();

        $this->lostStageIds = $this->stageRepository->where('code', 'lost')->pluck('id')->toArray();

        parent::__construct();
    }

    /**
     * Devuelve a los clientes actuales a lo largo del tiempo
     *
     * @param  string  $period
     */
    public function getTotalLeadsOverTime($period = 'auto'): array
    {
        $this->stageIds = $this->allStageIds;

        $period = $this->determinePeriod($period);

        return $this->getOverTimeStats($this->startDate, $this->endDate, 'leads.id', 'created_at', $period);
    }

    /**
     * Devuelve a los clientes actuales a lo largo del tiempo
     *
     * @param  string  $period
     */
    public function getTotalWonLeadsOverTime($period = 'auto'): array
    {
        $this->stageIds = $this->wonStageIds;

        $period = $this->determinePeriod($period);

        return $this->getOverTimeStats($this->startDate, $this->endDate, 'leads.id', 'closed_at', $period);
    }

    /**
     * Devuelve a los clientes actuales a lo largo del tiempo
     *
     * @param  string  $period
     */
    public function getTotalLostLeadsOverTime($period = 'auto'): array
    {
        $this->stageIds = $this->lostStageIds;

        $period = $this->determinePeriod($period);

        return $this->getOverTimeStats($this->startDate, $this->endDate, 'leads.id', 'closed_at', $period);
    }

    /**
     * Determine el período apropiado según el rango de fechas
     *
     * @param  string  $period
     */
    protected function determinePeriod($period = 'auto'): string
    {
        if ($period !== 'auto') {
            return $period;
        }

        $diffInDays = $this->startDate->diffInDays($this->endDate);
        $diffInMonths = $this->startDate->diffInMonths($this->endDate);
        $diffInYears = $this->startDate->diffInYears($this->endDate);

        if ($diffInYears > 3) {
            return 'year';
        } elseif ($diffInMonths > 6) {
            return 'month';
        } elseif ($diffInDays > 60) {
            return 'week';
        } else {
            return 'day';
        }
    }

    /**
     * Recupera el total de clientes potenciales y su progreso.
     */
    public function getTotalLeadsProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalLeads($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalLeads($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el total de clientes potenciales por fecha
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getTotalLeads($startDate, $endDate): int
    {
        return $this->leadRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Recupera clientes potenciales promedio por día y su progreso.
     */
    public function getAverageLeadsPerDayProgress(): array
    {
        return [
            'previous' => $previous = $this->getAverageLeadsPerDay($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getAverageLeadsPerDay($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera clientes potenciales promedio por día
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getAverageLeadsPerDay($startDate, $endDate): float
    {
        $days = $startDate->diffInDays($endDate);

        if ($days == 0) {
            return 0;
        }

        return $this->getTotalLeads($startDate, $endDate) / $days;
    }

    /**
     * Recupera el valor total del cliente potencial y su progreso.
     */
    public function getTotalLeadValueProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalLeadValue($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalLeadValue($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el valor total del cliente potencial
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getTotalLeadValue($startDate, $endDate): float
    {
        return $this->leadRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('lead_value');
    }

    /**
     * Recupera el valor promedio de los clientes potenciales y su progreso.
     */
    public function getAverageLeadValueProgress(): array
    {
        return [
            'previous' => $previous = $this->getAverageLeadValue($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getAverageLeadValue($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el valor promedio del cliente potencial
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getAverageLeadValue($startDate, $endDate): float
    {
        return $this->leadRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('lead_value') ?? 0;
    }

    /**
     * Recupera el valor total de los leads ganados y su progreso.
     */
    public function getTotalWonLeadValueProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalWonLeadValue($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalWonLeadValue($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el valor promedio de clientes potenciales ganados
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array
     */
    public function getTotalWonLeadValue($startDate, $endDate): ?float
    {
        return $this->leadRepository
            ->resetModel()
            ->whereIn('lead_pipeline_stage_id', $this->wonStageIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('lead_value');
    }

    /**
     * Recupera el valor promedio de los clientes potenciales perdidos y su progreso.
     */
    public function getTotalLostLeadValueProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalLostLeadValue($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalLostLeadValue($this->startDate, $this->endDate),
            'formatted_total' => core()->formatBasePrice($current),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera el valor promedio de clientes potenciales perdidos
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return array
     */
    public function getTotalLostLeadValue($startDate, $endDate): ?float
    {
        return $this->leadRepository
            ->resetModel()
            ->whereIn('lead_pipeline_stage_id', $this->lostStageIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('lead_value');
    }

    /**
     * Recupera el valor total de clientes potenciales por fuentes.
     */
    public function getTotalWonLeadValueBySources()
    {
        return $this->leadRepository
            ->resetModel()
            ->select(
                'lead_sources.name',
                DB::raw('SUM(lead_value) as total')
            )
            ->leftJoin('lead_sources', 'leads.lead_source_id', '=', 'lead_sources.id')
            ->whereIn('lead_pipeline_stage_id', $this->wonStageIds)
            ->whereBetween('leads.created_at', [$this->startDate, $this->endDate])
            ->groupBy('lead_source_id', 'lead_sources.name')
            ->get();
    }

    /**
     * Recupera el valor total de clientes potenciales por tipos.
     */
    public function getTotalWonLeadValueByTypes()
    {
        return $this->leadRepository
            ->resetModel()
            ->select(
                'lead_types.name',
                DB::raw('SUM(lead_value) as total')
            )
            ->leftJoin('lead_types', 'leads.lead_type_id', '=', 'lead_types.id')
            ->whereIn('lead_pipeline_stage_id', $this->wonStageIds)
            ->whereBetween('leads.created_at', [$this->startDate, $this->endDate])
            ->groupBy('lead_type_id', 'lead_types.name')
            ->get();
    }

    /**
     * Recupera clientes potenciales abiertos por estados.
     */
    public function getOpenLeadsByStates()
    {
        return $this->leadRepository
            ->resetModel()
            ->select(
                'lead_pipeline_stages.name',
                DB::raw('COUNT(lead_value) as total')
            )
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->whereNotIn('lead_pipeline_stage_id', $this->wonStageIds)
            ->whereNotIn('lead_pipeline_stage_id', $this->lostStageIds)
            ->whereBetween('leads.created_at', [$this->startDate, $this->endDate])
            ->groupBy('lead_pipeline_stage_id', 'lead_pipeline_stages.name')
            ->orderByDesc('total')
            ->get();
    }

    /**
     * Estadísticas de retornos a lo largo del tiempo.
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @param  string  $valueColumn
     * @param  string  $dateColumn
     * @param  string  $period
     */
    public function getOverTimeStats($startDate, $endDate, $valueColumn, $dateColumn = 'created_at', $period = 'auto'): array
    {
        $period = $this->determinePeriod($period);

        $intervals = $this->generateTimeIntervals($startDate, $endDate, $period);

        $groupColumn = $this->getGroupColumn($dateColumn, $period);

        $query = $this->leadRepository
            ->resetModel()
            ->select(
                DB::raw("$groupColumn AS date"),
                DB::raw('COUNT(DISTINCT id) AS count'),
                DB::raw('SUM('.\DB::getTablePrefix()."$valueColumn) AS total")
            )
            ->whereIn('lead_pipeline_stage_id', $this->stageIds)
            ->whereBetween($dateColumn, [$startDate, $endDate])
            ->groupBy(DB::raw($groupColumn))
            ->orderBy(DB::raw($groupColumn));

        $results = $query->get();
        $resultLookup = $results->keyBy('date');

        $stats = [];

        foreach ($intervals as $interval) {
            $result = $resultLookup->get($interval['key']);

            $stats[] = [
                'label' => $interval['label'],
                'count' => $result ? (int) $result->count : 0,
                'total' => $result ? (float) $result->total : 0,
            ];
        }

        return $stats;
    }

    /**
     * Generar intervalos de tiempo basados ​​en el período.
     */
    protected function generateTimeIntervals(Carbon $startDate, Carbon $endDate, string $period): array
    {
        $intervals = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $interval = [
                'key' => $this->formatDateForGrouping($current, $period),
                'label' => $this->formatDateForLabel($current, $period),
            ];

            $intervals[] = $interval;

            switch ($period) {
                case 'day':
                    $current->addDay();

                    break;
                case 'week':
                    $current->addWeek();

                    break;
                case 'month':
                    $current->addMonth();

                    break;
                case 'year':
                    $current->addYear();

                    break;
            }
        }

        return $intervals;
    }

    /**
     * Obtenga la columna del grupo SQL según el período
     */
    protected function getGroupColumn(string $dateColumn, string $period): string
    {
        $driverName = DB::getDriverName();

        switch ($period) {
            case 'day':
                if ($driverName === 'pgsql') {
                    return "CAST($dateColumn AS DATE)";
                }

                return "DATE($dateColumn)";

            case 'week':
                if ($driverName === 'pgsql') {
                    return "TO_CHAR($dateColumn, 'IYYY-IW')";
                }

                return "DATE_FORMAT($dateColumn, '%Y-%u')";

            case 'month':
                if ($driverName === 'pgsql') {
                    return "TO_CHAR($dateColumn, 'YYYY-MM')";
                }

                return "DATE_FORMAT($dateColumn, '%Y-%m')";

            case 'year':
                if ($driverName === 'pgsql') {
                    return "TO_CHAR($dateColumn, 'YYYY')";
                }

                return "YEAR($dateColumn)";

            default:
                if ($driverName === 'pgsql') {
                    return "CAST($dateColumn AS DATE)";
                }

                return "DATE($dateColumn)";
        }
    }

    /**
     * Fecha de formato para clave de agrupación
     */
    protected function formatDateForGrouping(Carbon $date, string $period): string
    {
        switch ($period) {
            case 'day':
                return $date->format('Y-m-d');
            case 'week':
                return $date->format('Y-W');
            case 'month':
                return $date->format('Y-m');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('Y-m-d');
        }
    }

    /**
     * Dar formato a la fecha para mostrar la etiqueta
     */
    protected function formatDateForLabel(Carbon $date, string $period): string
    {
        switch ($period) {
            case 'day':
                return $date->format('M d');
            case 'week':
                return 'Week '.$date->format('W, Y');
            case 'month':
                return $date->format('M Y');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('M d');
        }
    }
}
