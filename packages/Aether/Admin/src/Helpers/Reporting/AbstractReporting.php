<?php

namespace Aether\Admin\Helpers\Reporting;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class AbstractReporting
{
    /**
     * La fecha de inicio de un período determinado.
     */
    protected Carbon $startDate;

    /**
     * La fecha de finalización de un período determinado.
     */
    protected Carbon $endDate;

    /**
     * La fecha de inicio del período anterior.
     */
    protected Carbon $lastStartDate;

    /**
     * La fecha de finalización del período anterior.
     */
    protected Carbon $lastEndDate;

    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setStartDate(request()->date('start'));

        $this->setEndDate(request()->date('end'));
    }

    /**
     * Establezca la fecha de inicio o la fecha predeterminada en hace 30 días si no se proporciona.
     *
     * @param  \Carbon\Carbon|null  $startDate
     * @return void
     */
    public function setStartDate(?Carbon $startDate = null): self
    {
        $this->startDate = $startDate ? $startDate->startOfDay() : now()->subDays(30)->startOfDay();

        $this->setLastStartDate();

        return $this;
    }

    /**
     * Establece la fecha de finalización al final del día de la fecha proporcionada o a la fecha actual.
     * fecha si no se proporciona o si la fecha proporcionada es en el futuro.
     *
     * @param  \Carbon\Carbon|null  $endDate
     * @return void
     */
    public function setEndDate(?Carbon $endDate = null): self
    {
        $this->endDate = ($endDate && $endDate->endOfDay() <= now()) ? $endDate->endOfDay() : now();

        $this->setLastEndDate();

        return $this;
    }

    /**
     * Obtenga la fecha de inicio.
     *
     * @return \Carbon\Carbon
     */
    public function getStartDate(): Carbon
    {
        return $this->startDate;
    }

    /**
     * Obtenga la fecha de finalización.
     *
     * @return \Carbon\Carbon
     */
    public function getEndDate(): Carbon
    {
        return $this->endDate;
    }

    /**
     * Establece la fecha de inicio del último período.
     */
    private function setLastStartDate(): void
    {
        if (! isset($this->startDate)) {
            $this->setStartDate(request()->date('start'));
        }

        if (! isset($this->endDate)) {
            $this->setEndDate(request()->date('end'));
        }

        $this->lastStartDate = $this->startDate->clone()->subDays($this->startDate->diffInDays($this->endDate));
    }

    /**
     * Establece la fecha de finalización del último período.
     */
    private function setLastEndDate(): void
    {
        $this->lastEndDate = $this->startDate->clone();
    }

    /**
     * Obtenga la última fecha de inicio.
     *
     * @return \Carbon\Carbon
     */
    public function getLastStartDate(): Carbon
    {
        return $this->lastStartDate;
    }

    /**
     * Obtenga la última fecha de finalización.
     *
     * @return \Carbon\Carbon
     */
    public function getLastEndDate(): Carbon
    {
        return $this->lastEndDate;
    }

    /**
     * Calcule el cambio porcentual entre los valores anteriores y actuales.
     *
     * @param  float|int  $previous
     * @param  float|int  $current
     */
    public function getPercentageChange($previous, $current): float|int
    {
        if (! $previous) {
            return $current ? 100 : 0;
        }

        return ($current - $previous) / $previous * 100;
    }

    /**
     * Devuelve intervalos de tiempo.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  string  $period
     * @return array
     */
    public function getTimeInterval($startDate, $endDate, $dateColumn, $period)
    {
        if ($period == 'auto') {
            $totalMonths = $startDate->diffInMonths($endDate) + 1;

            /**
             * Si la diferencia entre la fecha de inicio y finalización es superior a 5 meses
             */
            $intervals = $this->getMonthsInterval($startDate, $endDate);

            if (! empty($intervals)) {
                $groupColumn = DB::getDriverName() === 'pgsql' ? "EXTRACT(MONTH FROM $dateColumn)" : "MONTH($dateColumn)";

                return [
                    'group_column' => $groupColumn,
                    'intervals' => $intervals,
                ];
            }

            /**
             * Si la diferencia entre la fecha de inicio y finalización es superior a 6 semanas
             */
            $intervals = $this->getWeeksInterval($startDate, $endDate);

            if (! empty($intervals)) {
                $groupColumn = DB::getDriverName() === 'pgsql' ? "EXTRACT(WEEK FROM $dateColumn)" : "WEEK($dateColumn)";

                return [
                    'group_column' => $groupColumn,
                    'intervals' => $intervals,
                ];
            }

            /**
             * Si la diferencia entre la fecha de inicio y finalización es inferior a 6 semanas
             */
            $groupColumn = DB::getDriverName() === 'pgsql' ? "EXTRACT(DOY FROM $dateColumn)" : "DAYOFYEAR($dateColumn)";

            return [
                'group_column' => $groupColumn,
                'intervals' => $this->getDaysInterval($startDate, $endDate),
            ];
        } else {
            $datePeriod = CarbonPeriod::create($this->startDate, "1 $period", $this->endDate);

            if ($period == 'year') {
                $formatter = '?';
            } elseif ($period == 'month') {
                $formatter = '?-?';
            } else {
                $formatter = '?-?-?';
            }

            if (DB::getDriverName() === 'pgsql') {
                $formatter = Str::replaceArray('?', ['YYYY', 'MM', 'DD'], $formatter);
                $groupColumn = "TO_CHAR($dateColumn, '$formatter')";
            } else {
                $formatter = Str::replaceArray('?', ['%Y', '%m', '%d'], $formatter);
                $groupColumn = "DATE_FORMAT($dateColumn, '$formatter')";
            }

            $intervals = [];

            foreach ($datePeriod as $date) {
                $formattedDate = $date->format(Str::replaceArray('?', ['Y', 'm', 'd'], $formatter));

                $intervals[] = [
                    'filter' => $formattedDate,
                    'start' => $formattedDate,
                ];
            }

            return [
                'group_column' => $groupColumn,
                'intervals' => $intervals,
            ];
        }
    }

    /**
     * Devuelve intervalos de tiempo.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getMonthsInterval($startDate, $endDate)
    {
        $intervals = [];

        $totalMonths = $startDate->diffInMonths($endDate) + 1;

        /**
         * Si la diferencia entre la fecha de inicio y finalización es inferior a 5 meses
         */
        if ($totalMonths <= 5) {
            return $intervals;
        }

        for ($i = 0; $i < $totalMonths; $i++) {
            $intervalStartDate = clone $startDate;

            $intervalStartDate->addMonths($i);

            $start = $intervalStartDate->startOfDay();

            $end = ($totalMonths - 1 == $i)
                ? $endDate
                : $intervalStartDate->addMonth()->subDay()->endOfDay();

            $intervals[] = [
                'filter' => $start->month,
                'start' => $start->format('d M'),
                'end' => $end->format('d M'),
            ];
        }

        return $intervals;
    }

    /**
     * Devuelve intervalos de tiempo.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getWeeksInterval($startDate, $endDate)
    {
        $intervals = [];

        $startWeekDay = Carbon::createFromTimeString(core()->xWeekRange($startDate, 0).' 00:00:01');

        $endWeekDay = Carbon::createFromTimeString(core()->xWeekRange($endDate, 1).' 23:59:59');

        $totalWeeks = $startWeekDay->diffInWeeks($endWeekDay);

        /**
         * Si la diferencia entre la fecha de inicio y finalización es inferior a 6 semanas
         */
        if ($totalWeeks <= 6) {
            return $intervals;
        }

        for ($i = 0; $i < $totalWeeks; $i++) {
            $intervalStartDate = clone $startDate;

            $intervalStartDate->addWeeks($i);

            $start = $i == 0
                ? $startDate
                : Carbon::createFromTimeString(core()->xWeekRange($intervalStartDate, 0).' 00:00:01');

            $end = ($totalWeeks - 1 == $i)
                ? $endDate
                : Carbon::createFromTimeString(core()->xWeekRange($intervalStartDate->subDay(), 1).' 23:59:59');

            $intervals[] = [
                'filter' => $start->week,
                'start' => $start->format('d M'),
                'end' => $end->format('d M'),
            ];
        }

        return $intervals;
    }

    /**
     * Devuelve intervalos de tiempo.
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @return array
     */
    public function getDaysInterval($startDate, $endDate)
    {
        $intervals = [];

        $totalDays = $startDate->diffInDays($endDate) + 1;

        for ($i = 0; $i < $totalDays; $i++) {
            $intervalStartDate = clone $startDate;

            $intervalStartDate->addDays($i);

            $intervals[] = [
                'filter' => $intervalStartDate->dayOfYear,
                'start' => $intervalStartDate->startOfDay()->format('d M'),
                'end' => $intervalStartDate->endOfDay()->format('d M'),
            ];
        }

        return $intervals;
    }
}
