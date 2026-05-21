<?php

use Aether\Admin\Providers\ModuleServiceProvider as AdminModuleServiceProvider;
use Aether\Attribute\Providers\ModuleServiceProvider as AttributeModuleServiceProvider;
use Aether\Automation\Providers\ModuleServiceProvider as AutomationModuleServiceProvider;
use Aether\Contact\Providers\ModuleServiceProvider as ContactModuleServiceProvider;
use Aether\Core\Providers\ModuleServiceProvider as CoreModuleServiceProvider;
use Aether\DataGrid\Providers\ModuleServiceProvider as DataGridModuleServiceProvider;
use Aether\DataTransfer\Providers\ModuleServiceProvider as DataTransferModuleServiceProvider;
use Aether\Email\Providers\ModuleServiceProvider as EmailModuleServiceProvider;
use Aether\EmailTemplate\Providers\ModuleServiceProvider as EmailTemplateModuleServiceProvider;
use Aether\Lead\Providers\ModuleServiceProvider as LeadModuleServiceProvider;
use Aether\Product\Providers\ModuleServiceProvider as ProductModuleServiceProvider;
use Aether\Quote\Providers\ModuleServiceProvider as QuoteModuleServiceProvider;
use Aether\Tag\Providers\ModuleServiceProvider as TagModuleServiceProvider;
use Aether\User\Providers\ModuleServiceProvider as UserModuleServiceProvider;
use Aether\Warehouse\Providers\ModuleServiceProvider as WarehouseModuleServiceProvider;
use Aether\WebForm\Providers\ModuleServiceProvider as WebFormModuleServiceProvider;

return [
    'modules' => [
        DataTransferModuleServiceProvider::class,
        AdminModuleServiceProvider::class,
        AttributeModuleServiceProvider::class,
        AutomationModuleServiceProvider::class,
        ContactModuleServiceProvider::class,
        CoreModuleServiceProvider::class,
        DataGridModuleServiceProvider::class,
        EmailTemplateModuleServiceProvider::class,
        EmailModuleServiceProvider::class,
        LeadModuleServiceProvider::class,
        ProductModuleServiceProvider::class,
        QuoteModuleServiceProvider::class,
        TagModuleServiceProvider::class,
        UserModuleServiceProvider::class,
        WarehouseModuleServiceProvider::class,
        WebFormModuleServiceProvider::class,
        DataTransferModuleServiceProvider::class,
    ],

    'register_route_models' => true,
];
