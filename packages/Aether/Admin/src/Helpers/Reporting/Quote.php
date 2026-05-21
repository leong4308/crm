<?php

namespace Aether\Admin\Helpers\Reporting;

use Carbon\Carbon;
use Aether\Quote\Repositories\QuoteRepository;

class Quote extends AbstractReporting
{
    /**
     * Crea una instancia auxiliar.
     *
     * @return void
     */
    public function __construct(protected QuoteRepository $quoteRepository)
    {
        parent::__construct();
    }

    /**
     * Recupera cotizaciones totales y su progreso.
     */
    public function getTotalQuotesProgress(): array
    {
        return [
            'previous' => $previous = $this->getTotalQuotes($this->lastStartDate, $this->lastEndDate),
            'current' => $current = $this->getTotalQuotes($this->startDate, $this->endDate),
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Recupera cotizaciones totales por fecha
     *
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     */
    public function getTotalQuotes($startDate, $endDate): int
    {
        return $this->quoteRepository
            ->resetModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }
}
