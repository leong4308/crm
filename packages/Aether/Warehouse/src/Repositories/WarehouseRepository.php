<?php

namespace Aether\Warehouse\Repositories;

use Illuminate\Container\Container;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Eloquent\Repository;
use Aether\Warehouse\Contracts\Warehouse;

class WarehouseRepository extends Repository
{
    /**
     * Campos buscables.
     */
    protected $fieldSearchable = [
        'name',
        'contact_name',
        'contact_emails',
        'contact_numbers',
        'contact_address',
    ];

    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Especifique el nombre de la clase de modelo.
     *
     * @return mixed
     */
    public function model()
    {
        return Warehouse::class;
    }

    /**
     * Crear.
     *
     * @return Warehouse
     */
    public function create(array $data)
    {
        $warehouse = parent::create($data);

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $warehouse->id,
        ]));

        return $warehouse;
    }

    /**
     * Actualizar.
     *
     * @param  int  $id
     * @param  array  $attribute
     * @return Warehouse
     */
    public function update(array $data, $id, $attributes = [])
    {
        $warehouse = parent::update($data, $id);

        /**
         * Si se proporcionan atributos, solo guarde los atributos proporcionados y regrese.
         */
        if (! empty($attributes)) {
            $conditions = ['entity_type' => $data['entity_type']];

            if (isset($data['quick_add'])) {
                $conditions['quick_add'] = 1;
            }

            $attributes = $this->attributeRepository->where($conditions)
                ->whereIn('code', $attributes)
                ->get();

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $warehouse->id,
            ]), $attributes);

            return $warehouse;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $warehouse->id,
        ]));

        return $warehouse;
    }
}
