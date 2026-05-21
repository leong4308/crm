<?php

namespace Aether\Admin\Http\Requests;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Contracts\Validations\Decimal;
use Illuminate\Foundation\Http\FormRequest;

class AttributeForm extends FormRequest
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Cree una nueva instancia de solicitud de formulario.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository
    ) {}

    /**
     * Determine si el producto está autorizado para realizar esta solicitud.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $inputs = $this->all();

        $attributes = $this->attributeRepository->findWhere([
            'type' => 'price',
        ]);

        foreach ($attributes as $attribute) {
            if (isset($inputs[$attribute->code])) {
                if (is_string($inputs[$attribute->code])) {
                    $inputs[$attribute->code] = str_replace(',', '', $inputs[$attribute->code]);
                }
            }
        }

        $numericAttributes = $this->attributeRepository->findWhere([
            'validation' => 'numeric',
        ]);

        foreach ($numericAttributes as $attribute) {
            if (isset($inputs[$attribute->code])) {
                if (is_string($inputs[$attribute->code])) {
                    $inputs[$attribute->code] = str_replace(',', '', $inputs[$attribute->code]);
                }
            }
        }

        if (isset($inputs['items']) && is_array($inputs['items'])) {
            foreach ($inputs['items'] as $key => $item) {
                foreach (['price', 'quantity', 'total', 'discount_amount', 'tax_amount', 'final_total'] as $field) {
                    if (isset($item[$field]) && is_string($item[$field])) {
                        $inputs['items'][$key][$field] = str_replace(',', '', $item[$field]);
                    }
                }
            }
        }

        $this->replace($inputs);
    }

    /**
     * Obtenga las reglas de validación que se aplican a la solicitud.
     *
     * @return array
     */
    public function rules()
    {
        $attributes = $this->attributeRepository->scopeQuery(function ($query) {
            $query = $query->whereIn('code', array_keys(request()->all()))
                ->where('entity_type', request('entity_type'));

            if (request()->has('quick_add')) {
                $query = $query->where('quick_add', 1);
            }

            return $query;
        })->get();

        foreach ($attributes as $attribute) {
            $validations = [];

            if ($attribute->type == 'boolean') {
                continue;
            } elseif ($attribute->type == 'address') {
                if (! $attribute->is_required) {
                    continue;
                }

                $validations = [
                    $attribute->code.'.address' => 'required',
                    $attribute->code.'.country' => 'required',
                    $attribute->code.'.state' => 'required',
                    $attribute->code.'.city' => 'required',
                    $attribute->code.'.postcode' => 'required',
                ];
            } elseif ($attribute->type == 'email') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable', 'email'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } elseif ($attribute->type == 'phone') {
                $validations = [
                    $attribute->code => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.value' => [$attribute->is_required ? 'required' : 'nullable'],
                    $attribute->code.'.*.label' => $attribute->is_required ? 'required' : 'nullable',
                ];
            } else {
                $validations[$attribute->code] = [$attribute->is_required ? 'required' : 'nullable'];

                if ($attribute->type == 'text' && $attribute->validation) {
                    array_push($validations[$attribute->code],
                        $attribute->validation == 'decimal'
                        ? new Decimal
                        : $attribute->validation
                    );
                }

                if ($attribute->type == 'price') {
                    array_push($validations[$attribute->code], new Decimal);
                }

                if ($attribute->type == 'image' && ! request($attribute->code.'.delete')) {
                    array_push($validations[$attribute->code], 'mimes:bmp,jpeg,jpg,png,webp');
                }
            }

            if ($attribute->is_unique) {
                array_push($validations[in_array($attribute->type, ['email', 'phone'])
                    ? $attribute->code.'.*.value'
                    : $attribute->code
                ], function ($field, $value, $fail) use ($attribute) {
                    if (! $this->attributeValueRepository->isValueUnique($this->id, $attribute->entity_type, $attribute, request($field))) {
                        $fail('The value has already been taken.');
                    }
                });
            }

            $this->rules = array_merge($this->rules, $validations);
        }

        return $this->rules;
    }
}
