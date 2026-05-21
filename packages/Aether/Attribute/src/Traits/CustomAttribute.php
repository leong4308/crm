<?php

namespace Aether\Attribute\Traits;

use Aether\Attribute\Models\AttributeValueProxy;
use Aether\Attribute\Repositories\AttributeRepository;
use Illuminate\Database\Eloquent\MassAssignmentException;

trait CustomAttribute
{
    /**
     * @var array
     */
    public static $attributeTypeFields = [
        'text' => 'text_value',
        'textarea' => 'text_value',
        'price' => 'float_value',
        'boolean' => 'boolean_value',
        'select' => 'integer_value',
        'multiselect' => 'text_value',
        'checkbox' => 'text_value',
        'email' => 'json_value',
        'address' => 'json_value',
        'phone' => 'json_value',
        'lookup' => 'integer_value',
        'datetime' => 'datetime_value',
        'date' => 'date_value',
        'file' => 'text_value',
        'image' => 'text_value',
    ];

    /**
     * Obtenga los valores de los atributos que posee la entidad.
     */
    public function attribute_values()
    {
        return $this->morphMany(AttributeValueProxy::modelClass(), 'entity');
    }

    /**
     * Obtenga un atributo del modelo.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! method_exists(static::class, $key) && ! isset($this->attributes[$key])) {
            if (isset($this->id)) {
                $this->attributes[$key] = '';

                $attribute = app(AttributeRepository::class)->getAttributeByCode($key);

                $this->attributes[$key] = $this->getCustomAttributeValue($attribute);

                return $this->getAttributeValue($key);
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        $hiddenAttributes = $this->getHidden();

        if (isset($this->id)) {
            $customAttributes = $this->getCustomAttributes();

            foreach ($customAttributes as $attribute) {
                if (in_array($attribute->code, $hiddenAttributes) && isset($this->attributes[$attribute->code])) {
                    continue;
                }

                $attributes[$attribute->code] = $this->getCustomAttributeValue($attribute);
            }
        }

        return $attributes;
    }

    /**
     * Verifique los atributos familiares cargados.
     *
     * @return object
     */
    public function getCustomAttributes()
    {
        static $attributes;

        if ($attributes) {
            return $attributes;
        }

        return $attributes = app(AttributeRepository::class)->where('entity_type', $this->getTable())->get();
    }

    /**
     * Obtenga un valor de atributo de producto.
     *
     * @return mixed
     */
    public function getCustomAttributeValue($attribute)
    {
        if (! $attribute) {
            return;
        }

        $attributeValue = $this->attribute_values->where('attribute_id', $attribute->id)->first();

        return $attributeValue[self::$attributeTypeFields[$attribute->type]] ?? null;
    }

    /**
     * Crea una nueva instancia del modelo dado.
     *
     * @param  array  $attributes
     * @return Collection
     */
    public function getLookUpAttributes($attributes)
    {
        $attributes = app(AttributeRepository::class)->scopeQuery(function ($query) use ($attributes) {
            return $query->distinct()
                ->where('type', 'lookup')
                ->where('entity_type', request('entity_type'))
                ->whereIn('code', array_keys($attributes, '', false));
        })->get();

        return $attributes;
    }

    /**
     * Crea una nueva instancia del modelo dado.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        // $attributes = $this->getLookUpAttributes($attributes);

        // Juega con datos aquí

        return parent::newInstance($attributes, $exists);
    }

    /**
     * Llene el modelo con una variedad de atributos.
     *
     * @return $this
     *
     * @throws MassAssignmentException
     */
    public function fill(array $attributes)
    {
        // Juega con datos aquí

        return parent::fill($attributes);
    }

    // Eliminar los valores de los atributos del modelo.
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($entity) {
            $entity->attribute_values()->delete();
        });
    }
}
