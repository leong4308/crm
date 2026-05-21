<?php

namespace Aether\Admin\Listeners;

use Aether\Activity\Contracts\Activity as ActivityContract;
use Aether\Contact\Repositories\PersonRepository;
use Aether\Lead\Repositories\LeadRepository;
use Aether\Product\Repositories\ProductRepository;
use Aether\Warehouse\Repositories\WarehouseRepository;

class Activity
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected LeadRepository $leadRepository,
        protected PersonRepository $personRepository,
        protected ProductRepository $productRepository,
        protected WarehouseRepository $warehouseRepository
    ) {}

    /**
     * Vincular la actividad al cliente potencial o a la persona.
     */
    public function afterUpdateOrCreate(ActivityContract $activity): void
    {
        if (request()->input('lead_id')) {
            $lead = $this->leadRepository->find(request()->input('lead_id'));

            if (! $lead->activities->contains($activity->id)) {
                $lead->activities()->attach($activity->id);
            }
        } elseif (request()->input('person_id')) {
            $person = $this->personRepository->find(request()->input('person_id'));

            if (! $person->activities->contains($activity->id)) {
                $person->activities()->attach($activity->id);
            }
        } elseif (request()->input('warehouse_id')) {
            $warehouse = $this->warehouseRepository->find(request()->input('warehouse_id'));

            if (! $warehouse->activities->contains($activity->id)) {
                $warehouse->activities()->attach($activity->id);
            }
        } elseif (request()->input('product_id')) {
            $product = $this->productRepository->find(request()->input('product_id'));

            if (! $product->activities->contains($activity->id)) {
                $product->activities()->attach($activity->id);
            }
        }
    }
}
