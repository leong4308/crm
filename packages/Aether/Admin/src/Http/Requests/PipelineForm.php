<?php

namespace Aether\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Factory as ValidationFactory;

class PipelineForm extends FormRequest
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(ValidationFactory $validationFactory)
    {
        $this->validatorExtensions($validationFactory);
    }

    /**
     * Determine si el usuario está autorizado a realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Obtenga las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        if (request('id')) {
            return [
                'name' => 'required|unique:lead_pipelines,name,'.request('id'),
                'stages.*.name' => 'unique_key',
                'stages.*.code' => 'unique_key',
            ];
        }

        return [
            'name' => 'required|unique:lead_pipelines,name',
            'rotten_days' => 'required',
            'stages.*.name' => 'unique_key',
            'stages.*.code' => 'unique_key',
        ];
    }

    /**
     * Obtenga los mensajes de error para las reglas de validación definidas.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'stages.*.name.unique_key' => trans('admin::app.settings.pipelines.duplicate-name'),
        ];
    }

    /**
     * Coloque todas sus extensiones de validador aquí.
     *
     * @return void
     */
    public function validatorExtensions(ValidationFactory $validationFactory)
    {
        $validationFactory->extend(
            'unique_key',
            function ($attribute, $value, $parameters) {
                $key = last(explode('.', $attribute));

                $stages = collect(request()->get('stages'))->filter(function ($stage) use ($key, $value) {
                    return $stage[$key] === $value;
                });

                if ($stages->count() > 1) {
                    return false;
                }

                return true;
            }
        );
    }
}
