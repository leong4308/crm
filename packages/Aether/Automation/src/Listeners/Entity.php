<?php

namespace Aether\Automation\Listeners;

use Aether\Automation\Helpers\Validator;
use Aether\Automation\Repositories\WorkflowRepository;

class Entity
{
    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected WorkflowRepository $workflowRepository,
        protected Validator $validator
    ) {}

    /**
     * @param  string  $eventName
     * @param  mixed  $entity
     * @return void
     */
    public function process($eventName, $entity)
    {
        $workflows = $this->workflowRepository->findByField('event', $eventName);

        foreach ($workflows as $workflow) {
            $workflowEntity = app(config('workflows.trigger_entities.'.$workflow->entity_type.'.class'));

            $entity = $workflowEntity->getEntity($entity);

            if (! $this->validator->validate($workflow, $entity)) {
                continue;
            }

            try {
                $workflowEntity->executeActions($workflow, $entity);
            } catch (\Exception $e) {
                logger()->error($e->getMessage());
            }
        }
    }
}
