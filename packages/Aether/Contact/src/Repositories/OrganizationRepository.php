<?php

namespace Aether\Contact\Repositories;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Contact\Contracts\Organization;
use Aether\Core\Eloquent\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;

class OrganizationRepository extends Repository
{
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
        return Organization::class;
    }

    /**
     * Crear.
     *
     * @return Organization
     */
    public function create(array $data)
    {
        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'] ?: null;
        }

        $organization = parent::create($data);

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $organization->id,
        ]));

        return $organization;
    }

    /**
     * Actualizar.
     *
     * @param  int  $id
     * @param  array  $attribute
     * @return Organization
     */
    public function update(array $data, $id, $attributes = [])
    {
        if (isset($data['user_id'])) {
            $data['user_id'] = $data['user_id'] ?: null;
        }

        $organization = parent::update($data, $id);

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
                'entity_id' => $organization->id,
            ]), $attributes);

            return $organization;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $organization->id,
        ]));

        return $organization;
    }

    /**
     * Eliminar organización y sus personas.
     *
     * @param  int  $id
     * @return @void
     */
    public function delete($id)
    {
        $organization = $this->findOrFail($id);

        DB::transaction(function () use ($organization, $id) {
            $this->attributeValueRepository->deleteWhere([
                'entity_id' => $id,
                'entity_type' => 'organizations',
            ]);

            $organization->delete();
        });
    }
}
