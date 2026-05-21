<?php

namespace Aether\DataTransfer\Helpers\Importers\Leads;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Contracts\Validations\Decimal;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Aether\DataTransfer\Helpers\Import;
use Aether\DataTransfer\Helpers\Importers\AbstractImporter;
use Aether\DataTransfer\Repositories\ImportBatchRepository;
use Aether\Lead\Repositories\LeadRepository;
use Aether\Lead\Repositories\ProductRepository as LeadProductRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class Importer extends AbstractImporter
{
    /**
     * Código de error para identificación no existente.
     */
    const ERROR_ID_NOT_FOUND_FOR_DELETE = 'id_not_found_to_delete';

    /**
     * Columnas de entidad permanente.
     */
    protected array $validColumnNames = [
        'id',
        'title',
        'description',
        'lead_value',
        'status',
        'lost_reason',
        'closed_at',
        'user_id',
        'person_id',
        'lead_source_id',
        'lead_type_id',
        'lead_pipeline_id',
        'lead_pipeline_stage_id',
        'expected_close_date',
        'product',
    ];

    /**
     * Plantillas de mensajes de error.
     */
    protected array $messages = [
        self::ERROR_ID_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.leads.validation.errors.id-not-found',
    ];

    /**
     * Columnas de entidad permanente.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['title'];

    /**
     * Columna de entidad permanente.
     */
    protected string $masterAttributeCode = 'id';

    /**
     * ¿Se requiere vinculación?
     */
    protected bool $linkingRequired = true;

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected LeadRepository $leadRepository,
        protected LeadProductRepository $leadProductRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $leadsStorage,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
    }

    /**
     * Inicialice plantillas de errores de clientes potenciales.
     */
    protected function initErrorMessages(): void
    {
        foreach ($this->messages as $errorCode => $message) {
            $this->errorHelper->addErrorMessage($errorCode, trans($message));
        }

        parent::initErrorMessages();
    }

    /**
     * Validar datos.
     */
    public function validateData(): void
    {
        $this->leadsStorage->init();

        parent::validateData();
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
            if (! $this->isTitleExist($rowData['title'])) {
                $this->skipRow($rowNumber, self::ERROR_ID_NOT_FOUND_FOR_DELETE, 'id');

                return false;
            }

            return true;
        }

        if (! empty($rowData['product'])) {
            $product = $this->parseProducts($rowData['product']);

            $validator = Validator::make($product, [
                'id' => 'required|exists:products,id',
                'price' => 'required',
                'quantity' => 'required',
            ]);

            if ($validator->fails()) {
                $failedAttributes = $validator->failed();

                foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                    $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                    $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
                }
            }
        }

        /**
         * Validar atributos de leads.
         */
        $validator = Validator::make($rowData, [
            ...$this->getValidationRules('leads|persons', $rowData),
            'id' => 'numeric',
            'status' => 'sometimes|required|in:0,1',
            'user_id' => 'required|exists:users,id',
            'person_id' => 'required|exists:persons,id',
            'lead_source_id' => 'required|exists:lead_sources,id',
            'lead_type_id' => 'required|exists:lead_types,id',
            'lead_pipeline_id' => 'required|exists:lead_pipelines,id',
            'lead_pipeline_stage_id' => 'required|exists:lead_pipeline_stages,id',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        return ! $this->errorHelper->isRowInvalid($rowNumber);
    }

    /**
     * Prepare datos de fila para el producto principal.
     */
    protected function parseProducts(?string $products): array
    {
        $productData = [];

        $productArray = explode(',', $products);

        foreach ($productArray as $product) {
            if (empty($product)) {
                continue;
            }

            [$key, $value] = explode('=', $product);

            $productData[$key] = $value;
        }

        if (
            isset($productData['price'])
            && isset($productData['quantity'])
        ) {
            $productData['amount'] = $productData['price'] * $productData['quantity'];
        }

        return $productData;
    }

    /**
     * Obtenga reglas de validación.
     */
    public function getValidationRules(string $entityTypes, array $rowData): array
    {
        $rules = [];

        foreach (explode('|', $entityTypes) as $entityType) {
            $attributes = $this->attributeRepository->scopeQuery(fn ($query) => $query->whereIn('code', array_keys($rowData))->where('entity_type', $entityType))->get();

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
                    ], function ($field, $value, $fail) use ($attribute) {
                        if (! $this->attributeValueRepository->isValueUnique(
                            null,
                            $attribute->entity_type,
                            $attribute,
                            request($field)
                        )
                        ) {
                            $fail(trans('data_transfer::app.validation.errors.already-exists', ['attribute' => $attribute->name]));
                        }
                    });
                }

                $rules = [
                    ...$rules,
                    ...$validations,
                ];
            }
        }

        return $rules;
    }

    /**
     * Inicie el proceso de importación.
     */
    public function importBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.import.before', $batch);

        if ($batch->import->action == Import::ACTION_DELETE) {
            $this->deleteLeads($batch);
        } else {
            $this->saveLeads($batch);
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
     * Iniciar el proceso de vinculación de productos
     */
    public function linkBatch(ImportBatchContract $batch): bool
    {
        Event::dispatch('data_transfer.imports.batch.linking.before', $batch);

        /**
         * Cargue el almacenamiento de clientes potenciales con identificadores de lote.
         */
        $this->leadsStorage->load(Arr::pluck($batch->data, 'title'));

        $products = [];

        foreach ($batch->data as $rowData) {
            /**
             * Preparar productos.
             */
            $this->prepareProducts($rowData, $products);
        }

        $this->saveProducts($products);

        /**
         * Actualizar el resumen del lote de importación
         */
        $this->importBatchRepository->update([
            'state' => Import::STATE_LINKED,
        ], $batch->id);

        Event::dispatch('data_transfer.imports.batch.linking.after', $batch);

        return true;
    }

    /**
     * Preparar productos.
     */
    public function prepareProducts($rowData, &$product): void
    {
        if (! empty($rowData['product'])) {
            $product[$rowData['title']] = $this->parseProducts($rowData['product']);
        }
    }

    /**
     * Guardar productos.
     */
    public function saveProducts(array $products): void
    {
        $leadProducts = [];

        foreach ($products as $title => $product) {
            $lead = $this->leadsStorage->get($title);

            $leadProducts['insert'][] = [
                'lead_id' => $lead['id'],
                'product_id' => $product['id'],
                'price' => $product['price'],
                'quantity' => $product['quantity'],
                'amount' => $product['amount'],
            ];
        }

        foreach ($leadProducts['insert'] as $key => $leadProduct) {
            $this->leadProductRepository->deleteWhere([
                'lead_id' => $leadProduct['lead_id'],
                'product_id' => $leadProduct['product_id'],
            ]);
        }

        $this->leadProductRepository->upsert($leadProducts['insert'], ['lead_id', 'product_id']);
    }

    /**
     * Eliminar clientes potenciales del lote actual.
     */
    protected function deleteLeads(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de clientes potenciales con identificadores de lote.
         */
        $this->leadsStorage->load(Arr::pluck($batch->data, 'title'));

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            if (! $this->isTitleExist($rowData['title'])) {
                continue;
            }

            $idsToDelete[] = $this->leadsStorage->get($rowData['title']);
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->leadRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Guarde clientes potenciales del lote actual.
     */
    protected function saveLeads(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de clientes potenciales con un título único por lotes.
         */
        $this->leadsStorage->load(Arr::pluck($batch->data, 'title'));

        $leads = [];

        /**
         * Preparar leads para importar.
         */
        foreach ($batch->data as $rowData) {
            if (isset($rowData['id'])) {
                $leads['update'][$rowData['id']] = Arr::except($rowData, ['product']);
            } else {
                $leads['insert'][$rowData['title']] = [
                    ...Arr::except($rowData, ['id', 'product']),
                    'created_at' => $rowData['created_at'] ?? now(),
                    'updated_at' => $rowData['updated_at'] ?? now(),
                ];
            }
        }

        if (! empty($leads['update'])) {
            $this->updatedItemsCount += count($leads['update']);

            $this->leadRepository->upsert(
                $leads['update'],
                $this->masterAttributeCode
            );
        }

        if (! empty($leads['insert'])) {
            $this->createdItemsCount += count($leads['insert']);

            $this->leadRepository->insert($leads['insert']);

            /**
             * Actualizar el almacenamiento de sku con productos recién creados
             */
            $newLeads = $this->leadRepository->findWhereIn(
                'title',
                array_keys($leads['insert']),
                [
                    'id',
                    'title',
                ]
            );

            foreach ($newLeads as $lead) {
                $this->leadsStorage->set($lead->title, [
                    'id' => $lead->id,
                    'title' => $lead->title,
                ]);
            }
        }

        return true;
    }

    /**
     * Compruebe si el título existe.
     */
    public function isTitleExist(string $title): bool
    {
        return $this->leadsStorage->has($title);
    }

    /**
     * Prepare los datos de la fila para guardarlos en la base de datos.
     */
    protected function prepareRowForDb(array $rowData): array
    {
        return parent::prepareRowForDb($rowData);
    }
}
