<?php

namespace Aether\Admin\Http\Requests;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Contracts\Validations\Decimal;
use Illuminate\Foundation\Http\FormRequest;

class LeadForm extends FormRequest
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

            if (isset($inputs['person'][$attribute->code])) {
                if (is_string($inputs['person'][$attribute->code])) {
                    $inputs['person'][$attribute->code] = str_replace(',', '', $inputs['person'][$attribute->code]);
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

            if (isset($inputs['person'][$attribute->code])) {
                if (is_string($inputs['person'][$attribute->code])) {
                    $inputs['person'][$attribute->code] = str_replace(',', '', $inputs['person'][$attribute->code]);
                }
            }
        }

        if (isset($inputs['products']) && is_array($inputs['products'])) {
            foreach ($inputs['products'] as $key => $product) {
                foreach (['price', 'quantity'] as $field) {
                    if (isset($product[$field]) && is_string($product[$field])) {
                        $inputs['products'][$key][$field] = str_replace(',', '', $product[$field]);
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
        foreach (['leads', 'persons'] as $key => $entityType) {
            $attributes = $this->attributeRepository->scopeQuery(function ($query) use ($entityType) {
                $attributeCodes = $entityType == 'persons'
                    ? array_keys(request('person') ?? [])
                    : array_keys(request()->all());

                $query = $query->whereIn('code', $attributeCodes)
                    ->where('entity_type', $entityType);

                if (request()->has('quick_add')) {
                    $query = $query->where('quick_add', 1);
                }

                return $query;
            })->get();

            foreach ($attributes as $attribute) {
                if ($entityType == 'persons') {
                    $attribute->code = 'person.'.$attribute->code;
                }

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
                }

                if ($attribute->is_unique) {
                    array_push($validations[in_array($attribute->type, ['email', 'phone'])
                        ? $attribute->code.'.*.value'
                        : $attribute->code
                    ], function ($field, $value, $fail) use ($attribute, $entityType) {
                        if (! $this->attributeValueRepository->isValueUnique(
                            $entityType == 'persons' ? request('person.id') : $this->id,
                            $attribute->entity_type,
                            $attribute,
                            request($field)
                        )
                        ) {
                            $fail('The value has already been taken.');
                        }
                    });
                }

                $this->rules = array_merge($this->rules, $validations);
            }
        }

        return [
            ...$this->rules,
            'products' => 'array',
            'products.*.product_id' => 'sometimes|required|exists:products,id',
            'products.*.name' => 'required_with:products.*.product_id',
            'products.*.price' => 'required_with:products.*.product_id',
            'products.*.quantity' => 'required_with:products.*.product_id',
        ];
    }

    /**
     * Obtenga los mensajes de validación que se aplican a la solicitud.
     */
    public function messages(): array
    {
        return [
            'products.*.product_id.exists' => trans('admin::app.leads.selected-product-not-exist'),
            'products.*.name.required_with' => trans('admin::app.leads.product-name-required'),
            'products.*.price.required_with' => trans('admin::app.leads.product-price-required'),
            'products.*.quantity.required_with' => trans('admin::app.leads.product-quantity-required'),
        ];
    }
}
