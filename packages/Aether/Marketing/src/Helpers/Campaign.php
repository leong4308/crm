<?php

namespace Aether\Marketing\Helpers;

use Aether\Contact\Repositories\PersonRepository;
use Aether\Marketing\Mail\CampaignMail;
use Aether\Marketing\Repositories\CampaignRepository;
use Aether\Marketing\Repositories\EventRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class Campaign
{
    /**
     * Cree una nueva instancia de ayuda.
     *
     *
     * @return void
     */
    public function __construct(
        protected EventRepository $eventRepository,
        protected CampaignRepository $campaignRepository,
        protected PersonRepository $personRepository,
    ) {}

    /**
     * Procesa el correo electrónico.
     */
    public function process(): void
    {
        $campaigns = $this->campaignRepository->getModel()
            ->leftJoin('marketing_events', 'marketing_campaigns.marketing_event_id', 'marketing_events.id')
            ->leftJoin('email_templates', 'marketing_campaigns.marketing_template_id', 'email_templates.id')
            ->select('marketing_campaigns.*')
            ->where('marketing_campaigns.status', 1)
            ->where(function ($query) {
                $query->where('marketing_events.date', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull('marketing_events.date');
            })
            ->get();

        collect($campaigns)->each(function ($campaign) {
            collect($this->getPersonsEmails())->each(fn ($email) => Mail::queue(new CampaignMail($email, $campaign)));
        });
    }

    /**
     * Obtenga la dirección de correo electrónico.
     */
    private function getPersonsEmails(): array
    {
        return $this->personRepository->pluck('emails')
            ->flatMap(fn ($emails) => collect($emails)->pluck('value'))
            ->all();
    }
}
