<?php

namespace Aether\Automation\Helpers\Entity;

use Illuminate\Support\Facades\Mail;
use Aether\Activity\Repositories\ActivityRepository;
use Aether\Admin\Notifications\Common;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Automation\Repositories\WebhookRepository;
use Aether\Automation\Services\WebhookService;
use Aether\Contact\Repositories\PersonRepository;
use Aether\EmailTemplate\Repositories\EmailTemplateRepository;
use Aether\Lead\Contracts\Lead as ContractsLead;
use Aether\Lead\Repositories\LeadRepository;
use Aether\Tag\Repositories\TagRepository;

class Lead extends AbstractEntity
{
    /**
     * Defina el tipo de entidad.
     */
    protected string $entityType = 'leads';

    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected EmailTemplateRepository $emailTemplateRepository,
        protected LeadRepository $leadRepository,
        protected ActivityRepository $activityRepository,
        protected PersonRepository $personRepository,
        protected TagRepository $tagRepository,
        protected WebhookRepository $webhookRepository,
        protected WebhookService $webhookService
    ) {}

    /**
     * Listado de las entidades.
     */
    public function getEntity(mixed $entity)
    {
        if (! $entity instanceof ContractsLead) {
            $entity = $this->leadRepository->find($entity);
        }

        return $entity;
    }

    /**
     * Devuelve atributos.
     */
    public function getAttributes(string $entityType, array $skipAttributes = ['textarea', 'image', 'file', 'address']): array
    {
        return parent::getAttributes($entityType, $skipAttributes);
    }

    /**
     * Devuelve acciones de flujo de trabajo.
     */
    public function getActions(): array
    {
        $emailTemplates = $this->emailTemplateRepository->all(['id', 'name']);

        $webhooksOptions = $this->webhookRepository->all(['id', 'name']);

        return [
            [
                'id' => 'update_lead',
                'name' => trans('admin::app.settings.workflows.helpers.update-lead'),
                'attributes' => $this->getAttributes('leads'),
            ], [
                'id' => 'update_person',
                'name' => trans('admin::app.settings.workflows.helpers.update-person'),
                'attributes' => $this->getAttributes('persons'),
            ], [
                'id' => 'send_email_to_person',
                'name' => trans('admin::app.settings.workflows.helpers.send-email-to-person'),
                'options' => $emailTemplates,
            ], [
                'id' => 'send_email_to_sales_owner',
                'name' => trans('admin::app.settings.workflows.helpers.send-email-to-sales-owner'),
                'options' => $emailTemplates,
            ], [
                'id' => 'add_tag',
                'name' => trans('admin::app.settings.workflows.helpers.add-tag'),
            ], [
                'id' => 'add_note_as_activity',
                'name' => trans('admin::app.settings.workflows.helpers.add-note-as-activity'),
            ], [
                'id' => 'trigger_webhook',
                'name' => trans('admin::app.settings.workflows.helpers.add-webhook'),
                'options' => $webhooksOptions,
            ],
        ];
    }

    /**
     * Ejecutar acciones de flujo de trabajo.
     */
    public function executeActions(mixed $workflow, mixed $lead): void
    {
        foreach ($workflow->actions as $action) {
            switch ($action['id']) {
                case 'update_lead':
                    $this->leadRepository->update(
                        [
                            'entity_type' => 'leads',
                            $action['attribute'] => $action['value'],
                        ],
                        $lead->id,
                        [$action['attribute']]
                    );

                    break;

                case 'update_person':
                    $this->personRepository->update([
                        'entity_type' => 'persons',
                        $action['attribute'] => $action['value'],
                    ], $lead->person_id);

                    break;

                case 'send_email_to_person':
                    $emailTemplate = $this->emailTemplateRepository->find($action['value']);

                    if (! $emailTemplate) {
                        break;
                    }

                    try {
                        Mail::queue(new Common([
                            'to' => data_get($lead->person->emails, '*.value'),
                            'subject' => $this->replacePlaceholders($lead, $emailTemplate->subject),
                            'body' => $this->replacePlaceholders($lead, $emailTemplate->content),
                        ]));
                    } catch (\Exception $e) {
                    }

                    break;

                case 'send_email_to_sales_owner':
                    $emailTemplate = $this->emailTemplateRepository->find($action['value']);

                    if (! $emailTemplate) {
                        break;
                    }

                    try {
                        Mail::queue(new Common([
                            'to' => $lead->user->email,
                            'subject' => $this->replacePlaceholders($lead, $emailTemplate->subject),
                            'body' => $this->replacePlaceholders($lead, $emailTemplate->content),
                        ]));
                    } catch (\Exception $e) {
                    }

                    break;

                case 'add_tag':
                    $colors = [
                        '#337CFF',
                        '#FEBF00',
                        '#E5549F',
                        '#27B6BB',
                        '#FB8A3F',
                        '#43AF52',
                    ];

                    if (! $tag = $this->tagRepository->findOneByField('name', $action['value'])) {
                        $tag = $this->tagRepository->create([
                            'name' => $action['value'],
                            'color' => $colors[rand(0, 5)],
                            'user_id' => auth()->guard('user')->user()->id,
                        ]);
                    }

                    if (! $lead->tags->contains($tag->id)) {
                        $lead->tags()->attach($tag->id);
                    }

                    break;

                case 'add_note_as_activity':
                    $activity = $this->activityRepository->create([
                        'type' => 'note',
                        'comment' => $action['value'],
                        'is_done' => 1,
                        'user_id' => auth()->guard('user')->user()->id,
                    ]);

                    $lead->activities()->attach($activity->id);

                    break;

                case 'trigger_webhook':
                    try {
                        $this->triggerWebhook($action['value'], $lead);
                    } catch (\Exception $e) {
                        report($e);
                    }

                    break;
            }
        }
    }
}
