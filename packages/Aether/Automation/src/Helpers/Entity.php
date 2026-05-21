<?php

namespace Aether\Automation\Helpers;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\EmailTemplate\Repositories\EmailTemplateRepository;

class Entity
{
    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected EmailTemplateRepository $emailTemplateRepository
    ) {}

    /**
     * Devuelve eventos que coinciden con la entidad.
     *
     * @return array
     */
    public function getEvents()
    {
        $entities = config('workflows.trigger_entities');

        $events = [];

        foreach ($entities as $key => $entity) {
            $object = app($entity['class']);

            $events[$key] = [
                'id' => $key,
                'name' => $entity['name'],
                'events' => $entity['events'],
            ];
        }

        return $events;
    }

    /**
     * Devuelve condiciones que coinciden con la entidad.
     *
     * @return array
     */
    public function getConditions()
    {
        $entities = config('workflows.trigger_entities');

        $conditions = [];

        foreach ($entities as $key => $entity) {
            $object = app($entity['class']);

            $conditions[$key] = $object->getConditions();
        }

        return $conditions;
    }

    /**
     * Devuelve acciones de flujo de trabajo
     *
     * @return array
     */
    public function getActions()
    {
        $entities = config('workflows.trigger_entities');

        $conditions = [];

        foreach ($entities as $key => $entity) {
            $object = app($entity['class']);

            $conditions[$key] = $object->getActions();
        }

        return $conditions;
    }

    /**
     * Devuelve marcadores de posición para plantillas de correo electrónico
     *
     * @return array
     */
    public function getEmailTemplatePlaceholders()
    {
        $entities = config('workflows.trigger_entities');

        $placeholders = [];

        foreach ($entities as $key => $entity) {
            $object = app($entity['class']);

            $placeholders[] = $object->getEmailTemplatePlaceholders($entity);
        }

        return $placeholders;
    }
}
