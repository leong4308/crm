<?php

namespace Aether\Core\SystemConfig;

use Illuminate\Support\Str;

class ItemField
{
    /**
     * Mapeos de validación de Laravel a Vee.
     *
     * @var array
     */
    protected $veeValidateMappings = [
        'min' => 'min_value',
    ];

    /**
     * Cree una nueva instancia de ItemField.
     */
    public function __construct(
        public string $item_key,
        public string $name,
        public string $title,
        public ?string $info,
        public string $type,
        public ?string $path,
        public ?string $validation,
        public ?string $depends,
        public ?string $default,
        public ?bool $channel_based,
        public ?bool $locale_based,
        public array|string $options,
        public bool $is_visible = true,
        public bool $tinymce = false,
    ) {
        $this->options = $this->getOptions();
    }

    /**
     * Obtener el nombre del elemento de configuración.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Obtenga información del elemento de configuración.
     */
    public function getInfo(): ?string
    {
        return $this->info ?? '';
    }

    /**
     * Obtener el título del elemento de configuración.
     */
    public function getTitle(): ?string
    {
        return $this->title ?? '';
    }

    /**
     * Determine si el campo debe usar TinyMCE.
     */
    public function getTinymce(): bool
    {
        return $this->tinymce;
    }

    /**
     * Obtener el tipo de elemento de configuración.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Obtener la ruta del elemento de configuración.
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Obtenga la clave del elemento de configuración.
     */
    public function getItemKey(): string
    {
        return $this->item_key;
    }

    /**
     * Obtenga la validación del elemento de configuración.
     */
    public function getValidations(): ?string
    {
        if (empty($this->validation)) {
            return '';
        }

        foreach ($this->veeValidateMappings as $laravelRule => $veeValidateRule) {
            $this->validation = str_replace($laravelRule, $veeValidateRule, $this->validation);
        }

        return $this->validation;
    }

    /**
     * Obtener depende del elemento de configuración.
     */
    public function getDepends(): ?string
    {
        return $this->depends;
    }

    /**
     * Obtenga el valor predeterminado del elemento de configuración.
     */
    public function getDefault(): ?string
    {
        return $this->default;
    }

    /**
     * Obtener canal basado en el elemento de configuración.
     */
    public function getChannelBased(): ?bool
    {
        return $this->channel_based;
    }

    /**
     * Obtenga la configuración regional basada en el elemento de configuración.
     */
    public function getLocaleBased(): ?bool
    {
        return $this->locale_based;
    }

    /**
     * Obtenga el campo de nombre para formularios en la página de configuración.
     */
    public function getNameKey(): string
    {
        return $this->item_key.'.'.$this->name;
    }

    /**
     * Compruebe si el campo es obligatorio.
     */
    public function isRequired(): string
    {
        return Str::contains($this->getValidations(), 'required') ? 'required' : '';
    }

    /**
     * Obtener opciones del elemento de configuración.
     */
    public function getOptions(): array
    {
        if (is_array($this->options)) {
            return collect($this->options)->map(fn ($option) => [
                'title' => trans($option['title']),
                'value' => $option['value'],
            ])->toArray();
        }

        return collect($this->getFieldOptions($this->options))->map(fn ($option) => [
            'title' => trans($option['title']),
            'value' => $option['value'],
        ])->toArray();
    }

    /**
     * Convierta el campo en una matriz.
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'title' => $this->getTitle(),
            'info' => $this->getInfo(),
            'type' => $this->getType(),
            'path' => $this->getPath(),
            'depends' => $this->getDepends(),
            'validation' => $this->getValidations(),
            'default' => $this->getDefault(),
            'channel_based' => $this->getChannelBased(),
            'locale_based' => $this->getLocaleBased(),
            'options' => $this->getOptions(),
            'item_key' => $this->getItemKey(),
            'tinymce' => $this->getTinymce(),
        ];
    }

    /**
     * Obtenga el campo de nombre para formularios en la página de configuración.
     *
     * @param  string  $key
     * @return string
     */
    public function getNameField($key = null)
    {
        if (! $key) {
            $key = $this->item_key.'.'.$this->name;
        }

        $nameField = '';

        foreach (explode('.', $key) as $key => $field) {
            $nameField .= $key === 0 ? $field : '['.$field.']';
        }

        return $nameField;
    }

    /**
     * Depende del nombre del campo.
     */
    public function getDependFieldName(): string
    {
        if (empty($depends = $this->getDepends())) {
            return '';
        }

        $dependNameKey = $this->getItemKey().'.'.collect(explode(':', $depends))->first();

        return $this->getNameField($dependNameKey);
    }

    /**
     * Devuelve las opciones seleccionadas para el campo.
     */
    protected function getFieldOptions(string $options): array
    {
        [$class, $method] = Str::parseCallback($options);

        return app($class)->$method();
    }
}
