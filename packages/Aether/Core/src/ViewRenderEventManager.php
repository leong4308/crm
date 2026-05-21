<?php

namespace Aether\Core;

use Illuminate\Support\Facades\Event;

class ViewRenderEventManager
{
    /**
     * Contiene todas las plantillas
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Parámetros pasados ​​con evento.
     *
     * @var array
     */
    protected $params;

    /**
     * Evento de incendios para plantilla de renderizado
     *
     * @param  string  $eventName
     * @param  array|null  $params
     * @return string
     */
    public function handleRenderEvent($eventName, $params = null)
    {
        $this->params = $params ?? [];

        Event::dispatch($eventName, $this);

        return $this->templates;
    }

    /**
     *  obtener parámetros
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     *  obtener parámetro
     *
     * @return mixed
     */
    public function getParam($name)
    {
        return optional($this->params)[$name];
    }

    /**
     * Agregar plantillas para renderizar
     *
     * @param  string  $template
     * @return void
     */
    public function addTemplate($template)
    {
        array_push($this->templates, $template);
    }

    /**
     * Plantillas de renderizado
     *
     * @return string
     */
    public function render()
    {
        $string = '';

        foreach ($this->templates as $template) {
            if (view()->exists($template)) {
                $string .= view($template, $this->params)->render();
            } elseif (is_string($template)) {
                $string .= $template;
            }
        }

        return $string;
    }
}
