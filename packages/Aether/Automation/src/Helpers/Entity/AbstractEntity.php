<?php

namespace Aether\Automation\Helpers\Entity;

use Carbon\Carbon;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Automation\Repositories\WebhookRepository;
use Aether\Automation\Services\WebhookService;

abstract class AbstractEntity
{
    /**
     * Instancia del repositorio de atributos.
     */
    protected AttributeRepository $attributeRepository;

    /**
     * Cree una nueva instancia de repositorio.
     */
    public function __construct(
        protected WebhookService $webhookService,
        protected WebhookRepository $webhookRepository,
    ) {}

    /**
     * Listado de las entidades.
     */
    abstract public function getEntity(mixed $entity);

    /**
     * Devuelve acciones de flujo de trabajo.
     */
    abstract public function getActions();

    /**
     * Ejecutar acciones de flujo de trabajo.
     */
    abstract public function executeActions(mixed $workflow, mixed $entity): void;

    /**
     * Devuelve atributos para las condiciones del flujo de trabajo.
     */
    public function getConditions(): array
    {
        return $this->getAttributes($this->entityType);
    }

    /**
     * Obtener atributos para la entidad.
     */
    public function getAttributes(string $entityType, array $skipAttributes = ['textarea', 'image', 'file', 'address']): array
    {
        $attributes = [];

        foreach ($this->attributeRepository->findByField('entity_type', $entityType) as $attribute) {
            if (in_array($attribute->type, $skipAttributes)) {
                continue;
            }

            if ($attribute->lookup_type) {
                $options = [];
            } else {
                $options = $attribute->options;
            }

            $attributes[] = [
                'id' => $attribute->code,
                'type' => $attribute->type,
                'name' => $attribute->name,
                'lookup_type' => $attribute->lookup_type,
                'options' => $options,
            ];
        }

        return $attributes;
    }

    /**
     * Devuelve marcadores de posición para plantillas de correo electrónico.
     */
    public function getEmailTemplatePlaceholders(array $entity): array
    {
        $menuItems = [];

        foreach ($this->getAttributes($this->entityType) as $attribute) {
            $menuItems[] = [
                'text' => $attribute['name'],
                'value' => '{%'.$this->entityType.'.'.$attribute['id'].'%}',
            ];
        }

        return [
            'text' => $entity['name'],
            'menu' => $menuItems,
        ];
    }

    /**
     * Reemplace los marcadores de posición con valores.
     */
    public function replacePlaceholders(mixed $entity, string $content): string
    {
        foreach ($this->getAttributes($this->entityType, []) as $attribute) {
            $value = '';

            switch ($attribute['type']) {
                case 'price':
                    $value = core()->formatBasePrice($entity->{$attribute['id']});

                    break;

                case 'boolean':
                    $value = $entity->{$attribute['id']} ? trans('admin::app.common.yes') : trans('admin::app.common.no');

                    break;

                case 'select':
                case 'radio':
                case 'lookup':
                    if ($attribute['lookup_type']) {
                        $option = $this->attributeRepository->getLookUpEntity($attribute['lookup_type'], $entity->{$attribute['id']});
                    } else {
                        $option = $attribute['options']->where('id', $entity->{$attribute['id']})->first();
                    }

                    $value = $option ? $option->name : '';

                    break;

                case 'multiselect':
                case 'checkbox':
                    if ($attribute['lookup_type']) {
                        $options = $this->attributeRepository->getLookUpEntity($attribute['lookup_type'], explode(',', $entity->{$attribute['id']}));
                    } else {
                        $options = $attribute['options']->whereIn('id', explode(',', $entity->{$attribute['id']}));
                    }

                    $optionsLabels = [];

                    foreach ($options as $key => $option) {
                        $optionsLabels[] = $option->name;
                    }

                    $value = implode(', ', $optionsLabels);

                    break;

                case 'email':
                case 'phone':
                    if (! is_array($entity->{$attribute['id']})) {
                        break;
                    }

                    $optionsLabels = [];

                    foreach ($entity->{$attribute['id']} as $item) {
                        $optionsLabels[] = $item['value'].' ('.$item['label'].')';
                    }

                    $value = implode(', ', $optionsLabels);

                    break;

                case 'address':
                    if (! $entity->{$attribute['id']} || ! count(array_filter($entity->{$attribute['id']}))) {
                        break;
                    }

                    $value = $entity->{$attribute['id']}['address'].'<br>'
                             .$entity->{$attribute['id']}['postcode'].'  '.$entity->{$attribute['id']}['city'].'<br>'
                             .core()->state_name($entity->{$attribute['id']}['state']).'<br>'
                             .core()->country_name($entity->{$attribute['id']}['country']).'<br>';

                    break;

                case 'date':
                    if ($entity->{$attribute['id']}) {
                        $value = ! is_object($entity->{$attribute['id']})
                            ? Carbon::parse($entity->{$attribute['id']})
                            : $entity->{$attribute['id']}->format('D M d, Y');
                    } else {
                        $value = 'N/A';
                    }

                    break;

                case 'datetime':
                    if ($entity->{$attribute['id']}) {
                        $value = ! is_object($entity->{$attribute['id']})
                            ? Carbon::parse($entity->{$attribute['id']})
                            : $entity->{$attribute['id']}->format('D M d, Y H:i A');
                    } else {
                        $value = 'N/A';
                    }

                    break;

                default:
                    $value = $entity->{$attribute['id']};

                    break;
            }

            $content = strtr($content, [
                '{%'.$this->entityType.'.'.$attribute['id'].'%}' => $value,
                '{% '.$this->entityType.'.'.$attribute['id'].' %}' => $value,
            ]);
        }

        return $content;
    }

    /**
     * Activar webhook.
     *
     * @return void
     */
    public function triggerWebhook(int $webhookId, mixed $entity)
    {
        $webhook = $this->webhookRepository->findOrFail($webhookId);

        $payload = [
            'method' => $webhook->method,
            'query_params' => $this->replacePlaceholders($entity, json_encode($webhook->query_params)),
            'end_point' => $this->replacePlaceholders($entity, $webhook->end_point),
            'payload' => $this->replacePlaceholders($entity, json_encode($webhook->payload)),
            'headers' => $this->replacePlaceholders($entity, json_encode($webhook->headers)),
        ];

        $this->webhookService->triggerWebhook($payload);
    }
}
