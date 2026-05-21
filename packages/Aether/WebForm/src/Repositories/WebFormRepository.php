<?php

namespace Aether\WebForm\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\WebForm\Contracts\WebForm;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class WebFormRepository extends Repository
{
    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected WebFormAttributeRepository $webFormAttributeRepository,
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
        return WebForm::class;
    }

    /**
     * Crear formulario web.
     *
     * @return WebForm
     */
    public function create(array $data)
    {
        $webForm = $this->model->create(array_merge($data, [
            'form_id' => Str::random(50),
        ]));

        foreach ($data['attributes'] as $attributeData) {
            $this->webFormAttributeRepository->create(array_merge([
                'web_form_id' => $webForm->id,
            ], $attributeData));
        }

        return $webForm;
    }

    /**
     * Actualizar formulario web.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return WebForm
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $webForm = parent::update($data, $id);

        $previousAttributeIds = $webForm->attributes()->pluck('id');

        foreach ($data['attributes'] as $attributeId => $attributeData) {
            if (Str::contains($attributeId, 'attribute_')) {
                $this->webFormAttributeRepository->create(array_merge([
                    'web_form_id' => $webForm->id,
                ], $attributeData));
            } else {
                if (is_numeric($index = $previousAttributeIds->search($attributeId))) {
                    $previousAttributeIds->forget($index);
                }

                $this->webFormAttributeRepository->update($attributeData, $attributeId);
            }
        }

        foreach ($previousAttributeIds as $attributeId) {
            $this->webFormAttributeRepository->delete($attributeId);
        }

        return $webForm;
    }
}
