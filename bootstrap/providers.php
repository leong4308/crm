<?php

use App\Providers\AppServiceProvider;
use Barryvdh\DomPDF\ServiceProvider;
use Konekt\Concord\ConcordServiceProvider;
use Prettus\Repository\Providers\RepositoryServiceProvider;
use Aether\Activity\Providers\ActivityServiceProvider;
use Aether\Admin\Providers\AdminServiceProvider;
use Aether\Attribute\Providers\AttributeServiceProvider;
use Aether\Automation\Providers\WorkflowServiceProvider;
use Aether\Contact\Providers\ContactServiceProvider;
use Aether\Core\Providers\CoreServiceProvider;
use Aether\DataGrid\Providers\DataGridServiceProvider;
use Aether\DataTransfer\Providers\DataTransferServiceProvider;
use Aether\Email\Providers\EmailServiceProvider;
use Aether\EmailTemplate\Providers\EmailTemplateServiceProvider;
use Aether\Installer\Providers\InstallerServiceProvider;
use Aether\Lead\Providers\LeadServiceProvider;
use Aether\Marketing\Providers\MarketingServiceProvider;
use Aether\Product\Providers\ProductServiceProvider;
use Aether\Quote\Providers\QuoteServiceProvider;
use Aether\Tag\Providers\TagServiceProvider;
use Aether\User\Providers\UserServiceProvider;
use Aether\Warehouse\Providers\WarehouseServiceProvider;
use Aether\WebForm\Providers\WebFormServiceProvider;

return [
    /*
     * Package Service Providers...
     */
    ServiceProvider::class,
    ConcordServiceProvider::class,
    RepositoryServiceProvider::class,

    /*
     * Application Service Providers...
     */
    AppServiceProvider::class,

    /*
     * Aether Service Providers...
     */
    ActivityServiceProvider::class,
    AdminServiceProvider::class,
    AttributeServiceProvider::class,
    WorkflowServiceProvider::class,
    ContactServiceProvider::class,
    CoreServiceProvider::class,
    DataGridServiceProvider::class,
    DataTransferServiceProvider::class,
    EmailTemplateServiceProvider::class,
    EmailServiceProvider::class,
    MarketingServiceProvider::class,
    InstallerServiceProvider::class,
    LeadServiceProvider::class,
    ProductServiceProvider::class,
    QuoteServiceProvider::class,
    TagServiceProvider::class,
    UserServiceProvider::class,
    WarehouseServiceProvider::class,
    WebFormServiceProvider::class,
];
