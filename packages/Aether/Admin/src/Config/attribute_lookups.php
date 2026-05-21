<?php

return [
    'leads' => [
        'name' => 'Leads',
        'repository' => 'Aether\Lead\Repositories\LeadRepository',
        'label_column' => 'title',
    ],

    'lead_sources' => [
        'name' => 'Lead Sources',
        'repository' => 'Aether\Lead\Repositories\SourceRepository',
    ],

    'lead_types' => [
        'name' => 'Lead Types',
        'repository' => 'Aether\Lead\Repositories\TypeRepository',
    ],

    'lead_pipelines' => [
        'name' => 'Lead Pipelines',
        'repository' => 'Aether\Lead\Repositories\PipelineRepository',
    ],

    'lead_pipeline_stages' => [
        'name' => 'Lead Pipeline Stages',
        'repository' => 'Aether\Lead\Repositories\StageRepository',
    ],

    'users' => [
        'name' => 'Sales Owners',
        'repository' => 'Aether\User\Repositories\UserRepository',
    ],

    'organizations' => [
        'name' => 'Organizations',
        'repository' => 'Aether\Contact\Repositories\OrganizationRepository',
    ],

    'persons' => [
        'name' => 'Persons',
        'repository' => 'Aether\Contact\Repositories\PersonRepository',
        'table' => 'persons',
    ],

    'warehouses' => [
        'name' => 'Warehouses',
        'repository' => 'Aether\Warehouse\Repositories\WarehouseRepository',
    ],

    'locations' => [
        'name' => 'Locations',
        'repository' => 'Aether\Warehouse\Repositories\LocationRepository',
    ],
];
