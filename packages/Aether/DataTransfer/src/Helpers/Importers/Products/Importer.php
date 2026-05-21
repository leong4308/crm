<?php

namespace Aether\DataTransfer\Helpers\Importers\Products;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Aether\Attribute\Repositories\AttributeOptionRepository;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Aether\DataTransfer\Helpers\Import;
use Aether\DataTransfer\Helpers\Importers\AbstractImporter;
use Aether\DataTransfer\Repositories\ImportBatchRepository;
use Aether\Product\Repositories\ProductInventoryRepository;
use Aether\Product\Repositories\ProductRepository;

class Importer extends AbstractImporter
{
    /**
     * Código de error para SKU no existente.
     */
    const ERROR_SKU_NOT_FOUND_FOR_DELETE = 'sku_not_found_to_delete';

    /**
     * Plantillas de mensajes de error.
     */
    protected array $messages = [
        self::ERROR_SKU_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.products.validation.errors.sku-not-found',
    ];

    /**
     * Columnas de entidad permanente.
     */
    protected array $permanentAttributes = ['sku'];

    /**
     * Columna de entidad permanente.
     */
    protected string $masterAttributeCode = 'sku';

    /**
     * Atributos almacenados en caché.
     */
    protected mixed $attributes = [];

    /**
     * Columnas csv válidas.
     */
    protected array $validColumnNames = [
        'sku',
        'name',
        'description',
        'quantity',
        'price',
    ];

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeOptionRepository $attributeOptionRepository,
        protected ProductRepository $productRepository,
        protected ProductInventoryRepository $productInventoryRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected SKUStorage $skuStorage
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository
        );

        $this->initAttributes();
    }

    /**
     * Cargue todos los atributos y familias para usarlos más tarde.
     */
    protected function initAttributes(): void
    {
        $this->attributes = $this->attributeRepository->all();

        foreach ($this->attributes as $attribute) {
            $this->validColumnNames[] = $attribute->code;
        }
    }

    /**
     * Inicialice las plantillas de error del producto.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Guardar lotes validados.
     */
    protected function saveValidatedBatches(): self
    {
        $source = $this->getSource();

        $source->rewind();

        $this->skuStorage->init();

        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $source->next();

                continue;
            }

            $this->validateRow($rowData, $source->getCurrentRowNumber());

            $source->next();
        }

        parent::saveValidatedBatches();

        return $this;
    }

    /**
     * Valida la fila.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        /**
         * Si la fila ya está validada, no es necesario realizar más validaciones.
         */
        if (isset($this->validatedRows[$rowNumber])) {
            return ! $this->errorHelper->isRowInvalid($rowNumber);
        }

        $this->validatedRows[$rowNumber] = true;

        /**
         * Si se elimina la acción de importación, no es necesario realizar más validación.
         */
        if ($this->import->action == Import::ACTION_DELETE) {
            if (! $this->isSKUExist($rowData['sku'])) {
                $this->skipRow($rowNumber, self::ERROR_SKU_NOT_FOUND_FOR_DELETE, 'sku');

                return false;
            }

            return true;
        }

        /**
         * Validar atributos del producto
         */
        $validator = Validator::make($rowData, $this->getValidationRules('products', $rowData));

        if ($validator->fails()) {
            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $failedAttributes = $validator->failed();

                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Inicie el proceso de importación.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deleteProducts($batch);
        } else {
            $this->saveProductsData($batch);
        }

        /**
         * Actualizar el resumen del lote de importación.
         */
        $batch = $this->importBatchRepository->update([
            'state' => Import::STATE_PROCESSED,

            'summary' => [
                'created' => $this->getCreatedItemsCount(),
                'updated' => $this->getUpdatedItemsCount(),
                'deleted' => $this->getDeletedItemsCount(),
            ],
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.import.after', $batch);

        return true;
    }

    /**
     * Eliminar productos del lote actual.
     */
    protected function deleteProducts(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de SKU con SKU por lotes.
         */
        $this->skuStorage->load(Arr::pluck($batch->data, 'sku'));

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! $this->isSKUExist($rowData['sku'])) {
                continue;
            }

            $product = $this->skuStorage->get($rowData['sku']);

            $idsToDelete[] = $product['id'];
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->productRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Guarde productos del lote actual.
     */
    protected function saveProductsData(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de SKU con SKU por lotes.
         */
        $this->skuStorage->load(Arr::pluck($batch->data, 'sku'));

        $products = [];

        /**
         * Preparar productos para importación.
         */
        foreach ($batch->data as $rowData) {
            $this->prepareProducts($rowData, $products);
        }

        $this->saveProducts($products);

        return true;
    }

    /**
     * Prepare productos del lote actual.
     */
    public function prepareProducts(array $rowData, array &$products): void
    {
        if ($this->isSKUExist($rowData['sku'])) {
            $products['update'][$rowData['sku']] = $rowData;
        } else {
            $products['insert'][$rowData['sku']] = [
                ...$rowData,
                'created_at' => $rowData['created_at'] ?? now(),
                'updated_at' => $rowData['updated_at'] ?? now(),
            ];
        }
    }

    /**
     * Guarde productos del lote actual.
     */
    public function saveProducts(array $products): void
    {
        if (! empty($products['update'])) {
            $this->updatedItemsCount += count($products['update']);

            $this->productRepository->upsert(
                $products['update'],
                $this->masterAttributeCode
            );
        }

        if (! empty($products['insert'])) {
            $this->createdItemsCount += count($products['insert']);

            $this->productRepository->insert($products['insert']);
        }
    }

    /**
     * Guarde canales del lote actual.
     */
    public function saveChannels(array $channels): void
    {
        $productChannels = [];

        foreach ($channels as $sku => $channelIds) {
            $product = $this->skuStorage->get($sku);

            foreach (array_unique($channelIds) as $channelId) {
                $productChannels[] = [
                    'product_id' => $product['id'],
                    'channel_id' => $channelId,
                ];
            }
        }

        DB::table('product_channels')->upsert(
            $productChannels,
            [
                'product_id',
                'channel_id',
            ],
        );
    }

    /**
     * Guardar enlaces.
     */
    public function loadUnloadedSKUs(array $skus): void
    {
        $notLoadedSkus = [];

        foreach ($skus as $sku) {
            if ($this->skuStorage->has($sku)) {
                continue;
            }

            $notLoadedSkus[] = $sku;
        }

        /**
         * Cargue los SKU no cargados en el almacenamiento de SKU.
         */
        if (! empty($notLoadedSkus)) {
            $this->skuStorage->load($notLoadedSkus);
        }
    }

    /**
     * Compruebe si existe SKU.
     */
    public function isSKUExist(string $sku): bool
    {
        return $this->skuStorage->has($sku);
    }

    /**
     * Prepare los datos de la fila para guardarlos en la base de datos.
     */
    protected function prepareRowForDb(array $rowData): array
    {
        return parent::prepareRowForDb($rowData);
    }
}
