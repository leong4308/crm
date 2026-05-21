<?php

namespace Aether\Admin\Traits;

use Aether\Contact\Repositories\OrganizationRepository;
use Aether\Lead\Repositories\SourceRepository;
use Aether\User\Repositories\UserRepository;
use Aether\Warehouse\Repositories\WarehouseRepository;

/**
 * Lugar único para todas las opciones desplegables. Conjuntos de menú desplegable ordenado
 * métodos de opciones. Úselo según su necesidad.
 */
trait ProvideDropdownOptions
{
    /**
     * Opciones desplegables.
     *
     * @var array
     */
    public $booleanDropdownChoices = [
        'active_inactive',
        'yes_no',
    ];

    /**
     * ¿Existe una opción desplegable booleana?
     *
     * @param  string  $choice
     */
    public function isBooleanDropdownChoiceExists($choice): bool
    {
        return in_array($choice, $this->booleanDropdownChoices);
    }

    /**
     * Obtenga opciones desplegables booleanas.
     *
     * @param  string  $choice
     */
    public function getBooleanDropdownOptions($choice = 'active_inactive'): array
    {
        return $this->isBooleanDropdownChoiceExists($choice) && $choice == 'active_inactive'
            ? $this->getActiveInactiveDropdownOptions()
            : $this->getYesNoDropdownOptions();
    }

    /**
     * Obtenga opciones desplegables activas/inactivas.
     */
    public function getActiveInactiveDropdownOptions(): array
    {
        return [
            [
                'value' => '',
                'label' => trans('admin::app.common.select-options'),
                'disabled' => true,
                'selected' => true,
            ],
            [
                'label' => trans('admin::app.datagrid.active'),
                'value' => 1,
                'disabled' => false,
                'selected' => false,
            ], [
                'label' => trans('admin::app.datagrid.inactive'),
                'value' => 0,
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Obtenga opciones desplegables de sí/no.
     */
    public function getYesNoDropdownOptions(): array
    {
        return [
            [
                'value' => '',
                'label' => trans('admin::app.common.select-options'),
                'disabled' => true,
                'selected' => true,
            ],
            [
                'value' => 0,
                'label' => trans('admin::app.common.no'),
                'disabled' => false,
                'selected' => false,
            ], [
                'value' => 1,
                'label' => trans('admin::app.common.yes'),
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Obtenga opciones desplegables de usuario.
     */
    public function getUserDropdownOptions(): array
    {
        $options = app(UserRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label' => trans('admin::app.common.select-users'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options,
        ];
    }

    /**
     * Obtenga opciones de fuentes de clientes potenciales.
     */
    public function getLeadSourcesOptions(): array
    {
        $options = app(SourceRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label' => trans('admin::app.common.select-users'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options,
        ];
    }

    /**
     * Obtenga opciones desplegables de organización.
     */
    public function getOrganizationDropdownOptions(): array
    {
        $options = app(OrganizationRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label' => trans('admin::app.common.select-organization'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options,
        ];
    }

    /**
     * Obtenga opciones desplegables de roles.
     */
    public function getRoleDropdownOptions(): array
    {
        return [
            [
                'label' => trans('admin::app.settings.roles.all'),
                'value' => 'all',
                'disabled' => false,
                'selected' => false,
            ], [
                'label' => trans('admin::app.settings.roles.custom'),
                'value' => 'custom',
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Obtenga opciones desplegables de tipo de actividad.
     */
    public function getActivityTypeDropdownOptions(): array
    {
        return [
            [
                'label' => trans('admin::app.common.select-type'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ], [
                'label' => trans('admin::app.common.select-call'),
                'value' => 'call',
                'disabled' => false,
                'selected' => false,
            ], [
                'label' => trans('admin::app.common.select-meeting'),
                'value' => 'meeting',
                'disabled' => false,
                'selected' => false,
            ], [
                'label' => trans('admin::app.common.select-lunch'),
                'value' => 'lunch',
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Obtener opciones desplegables de tipo de atributo.
     */
    public function getAttributeTypeDropdownOptions(): array
    {
        return [
            [
                'label' => trans('admin::app.common.select-options'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ],
            [
                'label' => trans('admin::app.common.system_attribute'),
                'value' => '0',
                'disabled' => false,
                'selected' => false,
            ],
            [
                'label' => trans('admin::app.common.custom_attribute'),
                'value' => '1',
                'disabled' => false,
                'selected' => false,
            ],
        ];
    }

    /**
     * Obtenga opciones desplegables de organización.
     */
    public function getWarehouseDropdownOptions(): array
    {
        $options = app(WarehouseRepository::class)
            ->get(['id as value', 'name as label'])
            ->map(function ($item, $key) {
                $item->disabled = false;

                $item->selected = false;

                return $item;
            })
            ->toArray();

        return [
            [
                'label' => trans('admin::app.common.select-warehouse'),
                'value' => '',
                'disabled' => true,
                'selected' => true,
            ],
            ...$options,
        ];
    }
}
