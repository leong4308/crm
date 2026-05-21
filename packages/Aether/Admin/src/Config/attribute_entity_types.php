<?php

return [
    'leads' => [
        'name' => 'admin::app.leads.index.title',
        'repository' => 'Aether\Lead\Repositories\LeadRepository',
    ],

    'persons' => [
        'name' => 'admin::app.contacts.persons.index.title',
        'repository' => 'Aether\Contact\Repositories\PersonRepository',
    ],

    'organizations' => [
        'name' => 'admin::app.contacts.organizations.index.title',
        'repository' => 'Aether\Contact\Repositories\OrganizationRepository',
    ],

    'products' => [
        'name' => 'admin::app.products.index.title',
        'repository' => 'Aether\Product\Repositories\ProductRepository',
    ],

    'quotes' => [
        'name' => 'admin::app.quotes.index.title',
        'repository' => 'Aether\Quote\Repositories\QuoteRepository',
    ],

    'warehouses' => [
        'name' => 'admin::app.settings.warehouses.index.title',
        'repository' => 'Aether\Warehouse\Repositories\WarehouseRepository',
    ],
];
