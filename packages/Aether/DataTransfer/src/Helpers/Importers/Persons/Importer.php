<?php

namespace Aether\DataTransfer\Helpers\Importers\Persons;

use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Contact\Repositories\PersonRepository;
use Aether\DataTransfer\Contracts\ImportBatch as ImportBatchContract;
use Aether\DataTransfer\Helpers\Import;
use Aether\DataTransfer\Helpers\Importers\AbstractImporter;
use Aether\DataTransfer\Repositories\ImportBatchRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class Importer extends AbstractImporter
{
    /**
     * Código de error para correo electrónico no existente.
     */
    const ERROR_EMAIL_NOT_FOUND_FOR_DELETE = 'email_not_found_to_delete';

    /**
     * Código de error para correo electrónico duplicado.
     */
    const ERROR_DUPLICATE_EMAIL = 'duplicated_email';

    /**
     * Código de error para teléfono duplicado.
     */
    const ERROR_DUPLICATE_PHONE = 'duplicated_phone';

    /**
     * Columnas de entidad permanente.
     */
    protected array $validColumnNames = [
        'contact_numbers',
        'emails',
        'job_title',
        'name',
        'organization_id',
        'user_id',
    ];

    /**
     * Plantillas de mensajes de error.
     */
    protected array $messages = [
        self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE => 'data_transfer::app.importers.persons.validation.errors.email-not-found',
        self::ERROR_DUPLICATE_EMAIL => 'data_transfer::app.importers.persons.validation.errors.duplicate-email',
        self::ERROR_DUPLICATE_PHONE => 'data_transfer::app.importers.persons.validation.errors.duplicate-phone',
    ];

    /**
     * Columnas de entidad permanente.
     *
     * @var string[]
     */
    protected $permanentAttributes = ['emails'];

    /**
     * Columna de entidad permanente.
     */
    protected string $masterAttributeCode = 'unique_id';

    /**
     * Almacenamiento de correos electrónicos.
     */
    protected array $emails = [];

    /**
     * Almacenamiento de teléfonos.
     */
    protected array $phones = [];

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(
        protected ImportBatchRepository $importBatchRepository,
        protected PersonRepository $personRepository,
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected Storage $personStorage,
    ) {
        parent::__construct(
            $importBatchRepository,
            $attributeRepository,
            $attributeValueRepository,
        );
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
     * Validar datos.
     */
    public function validateData(): void
    {
        $this->personStorage->init();

        parent::validateData();
    }

    /**
     * Valida la fila.
     */
    public function validateRow(array $rowData, int $rowNumber): bool
    {
        $rowData = $this->parsedRowData($rowData);

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
            foreach ($rowData['emails'] as $email) {
                if (! $this->isEmailExist($email['value'])) {
                    $this->skipRow($rowNumber, self::ERROR_EMAIL_NOT_FOUND_FOR_DELETE, 'email');

                    return false;
                }

                return true;
            }
        }

        /**
         * Validar datos de fila.
         */
        $validator = Validator::make($rowData, [
            ...$this->getValidationRules('persons', $rowData),
            'organization_id' => 'required|exists:organizations,id',
            'user_id' => 'required|exists:users,id',
            'contact_numbers' => 'required|array',
            'contact_numbers.*.value' => 'required|numeric',
            'contact_numbers.*.label' => 'required|in:home,work',
            'emails' => 'required|array',
            'emails.*.value' => 'required|email',
            'emails.*.label' => 'required|in:home,work',
        ]);

        if ($validator->fails()) {
            $failedAttributes = $validator->failed();

            foreach ($validator->errors()->getMessages() as $attributeCode => $message) {
                $errorCode = array_key_first($failedAttributes[$attributeCode] ?? []);

                $this->skipRow($rowNumber, $errorCode, $attributeCode, current($message));
            }
        }

        /**
         * Compruebe si el correo electrónico es único.
         */
        if (! empty($emails = $rowData['emails'])) {
            foreach ($emails as $email) {
                if (! in_array($email['value'], $this->emails)) {
                    $this->emails[] = $email['value'];
                } else {
                    $message = sprintf(
                        trans($this->messages[self::ERROR_DUPLICATE_EMAIL]),
                        $email['value']
                    );

                    $this->skipRow($rowNumber, self::ERROR_DUPLICATE_EMAIL, 'email', $message);
                }
            }
        }

        /**
         * Compruebe si los teléfonos son únicos.
         */
        if (! empty($rowData['contact_numbers'])) {
            foreach ($rowData['contact_numbers'] as $phone) {
                if (! in_array($phone['value'], $this->phones)) {
                    if (! empty($phone['value'])) {
                        $this->phones[] = $phone['value'];
                    }
                } else {
                    $message = sprintf(
                        trans($this->messages[self::ERROR_DUPLICATE_PHONE]),
                        $phone['value']
                    );

                    $this->skipRow($rowNumber, self::ERROR_DUPLICATE_PHONE, 'phone', $message);
                }
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
            $this->deletePersons($batch);
        } else {
            $this->savePersonData($batch);
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
     * Eliminar personas del lote actual.
     */
    protected function deletePersons(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de personas con correos electrónicos por lotes.
         */
        $emails = collect(Arr::pluck($batch->data, 'emails'))
            ->map(function ($emails) {
                $emails = json_decode($emails, true);

                foreach ($emails as $email) {
                    return $email['value'];
                }
            });

        $this->personStorage->load($emails->toArray());

        $idsToDelete = [];

        foreach ($batch->data as $rowData) {
            $rowData = $this->parsedRowData($rowData);

            foreach ($rowData['emails'] as $email) {
                if (! $this->isEmailExist($email['value'])) {
                    continue;
                }

                $idsToDelete[] = $this->personStorage->get($email['value']);
            }
        }

        $idsToDelete = array_unique($idsToDelete);

        $this->deletedItemsCount = count($idsToDelete);

        $this->personRepository->deleteWhere([['id', 'IN', $idsToDelete]]);

        return true;
    }

    /**
     * Guardar persona del lote actual.
     */
    protected function savePersonData(ImportBatchContract $batch): bool
    {
        /**
         * Cargue el almacenamiento de personas con correo electrónico por lotes.
         */
        $emails = collect(Arr::pluck($batch->data, 'emails'))
            ->map(function ($emails) {
                $emails = json_decode($emails, true);

                foreach ($emails as $email) {
                    return $email['value'];
                }
            });

        $this->personStorage->load($emails->toArray());

        $persons = [];

        $attributeValues = [];

        /**
         * Preparar personas para la importación.
         */
        foreach ($batch->data as $rowData) {
            $this->preparePersons($rowData, $persons);

            $this->prepareAttributeValues($rowData, $attributeValues);
        }

        $this->savePersons($persons);

        $this->saveAttributeValues($attributeValues);

        return true;
    }

    /**
     * Prepare personas del lote actual.
     */
    public function preparePersons(array $rowData, array &$persons): void
    {
        $emails = $this->prepareEmail($rowData['emails']);

        foreach ($emails as $email) {
            $contactNumber = json_decode($rowData['contact_numbers'], true);

            $rowData['unique_id'] = "{$rowData['user_id']}|{$rowData['organization_id']}|{$email}|{$contactNumber[0]['value']}";

            if ($this->isEmailExist($email)) {
                $persons['update'][$email] = $rowData;
            } else {
                $persons['insert'][$email] = [
                    ...$rowData,
                    'created_at' => $rowData['created_at'] ?? now(),
                    'updated_at' => $rowData['updated_at'] ?? now(),
                ];
            }
        }
    }

    /**
     * Guarde personas del lote actual.
     */
    public function savePersons(array $persons): void
    {
        if (! empty($persons['update'])) {
            $this->updatedItemsCount += count($persons['update']);

            $this->personRepository->upsert(
                $persons['update'],
                $this->masterAttributeCode,
            );
        }

        if (! empty($persons['insert'])) {
            $this->createdItemsCount += count($persons['insert']);

            $this->personRepository->insert($persons['insert']);

            /**
             * Actualizar el almacenamiento de sku con productos recién creados
             */
            $emails = array_keys($persons['insert']);

            $newPersons = $this->personRepository->where(function ($query) use ($emails) {
                foreach ($emails as $email) {
                    $query->orWhereJsonContains('emails', [['value' => $email]]);
                }
            })->get();

            foreach ($newPersons as $person) {
                $this->personStorage->set($person->emails[0]['value'], $person->id);
            }
        }
    }

    /**
     * Guarde los valores de los atributos de la persona.
     */
    public function saveAttributeValues(array $attributeValues): void
    {
        $personAttributeValues = [];

        foreach ($attributeValues as $email => $attributeValue) {
            foreach ($attributeValue as $attribute) {
                $attribute['entity_id'] = (int) $this->personStorage->get($email);

                $attribute['unique_id'] = implode('|', array_filter([
                    $attribute['entity_id'],
                    $attribute['attribute_id'],
                ]));

                $attribute['entity_type'] = 'persons';

                $personAttributeValues[$attribute['unique_id']] = $attribute;
            }
        }

        $this->attributeValueRepository->upsert($personAttributeValues, 'unique_id');
    }

    /**
     * Compruebe si existe el correo electrónico.
     */
    public function isEmailExist(string $email): bool
    {
        return $this->personStorage->has($email);
    }

    /**
     * Prepare valores de atributos para la persona.
     */
    public function prepareAttributeValues(array $rowData, array &$attributeValues): void
    {
        foreach ($rowData as $code => $value) {
            if (is_null($value)) {
                continue;
            }

            $where = ['code' => $code];

            if ($code === 'name') {
                $where['entity_type'] = 'persons';
            }

            $attribute = $this->attributeRepository->findOneWhere($where);

            if (! $attribute) {
                continue;
            }

            $typeFields = $this->personRepository->getModel()::$attributeTypeFields;

            $attributeTypeValues = array_fill_keys(array_values($typeFields), null);

            $emails = $this->prepareEmail($rowData['emails']);

            foreach ($emails as $email) {
                $attributeValues[$email][] = array_merge($attributeTypeValues, [
                    'attribute_id' => $attribute->id,
                    $typeFields[$attribute->type] => $value,
                ]);
            }
        }
    }

    /**
     * Obtenga correo electrónico y teléfono analizados.
     */
    private function parsedRowData(array $rowData): array
    {
        $rowData['emails'] = json_decode($rowData['emails'], true);

        $rowData['contact_numbers'] = json_decode($rowData['contact_numbers'], true);

        return $rowData;
    }

    /**
     * Prepare el correo electrónico a partir de datos de fila.
     */
    private function prepareEmail(array|string $emails): Collection
    {
        static $cache = [];

        return collect($emails)
            ->map(function ($emailString) use (&$cache) {
                if (isset($cache[$emailString])) {
                    return $cache[$emailString];
                }

                $decoded = json_decode($emailString, true);

                $emailValue = is_array($decoded)
                    && isset($decoded[0]['value'])
                    ? $decoded[0]['value']
                    : null;

                return $cache[$emailString] = $emailValue;
            });
    }
}
