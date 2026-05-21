<?php

namespace Aether\Quote\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Str;
use Aether\Attribute\Repositories\AttributeRepository;
use Aether\Attribute\Repositories\AttributeValueRepository;
use Aether\Core\Eloquent\Repository;
use Aether\Lead\Repositories\ProductRepository;
use Aether\Quote\Contracts\Quote;

class QuoteRepository extends Repository
{
    /**
     * Campos buscables.
     */
    protected $fieldSearchable = [
        'subject',
        'description',
        'person_id',
        'person.name',
        'user_id',
        'user.name',
    ];

    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeValueRepository $attributeValueRepository,
        protected QuoteItemRepository $quoteItemRepository,
        protected ProductRepository $productRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Especifique el nombre de la clase de modelo.
     *
     * @return mixed
     */
    public function model()
    {
        return Quote::class;
    }

    /**
     * Crear.
     *
     * @return Quote
     */
    public function create(array $data)
    {
        $quote = parent::create($data);

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $quote->id,
        ]));

        foreach ($data['items'] as $itemData) {
            $this->quoteItemRepository->create(array_merge($itemData, [
                'quote_id' => $quote->id,
            ]));
        }

        return $quote;
    }

    /**
     * Actualizar.
     *
     * @param  int  $id
     * @param  array  $attribute
     * @return Quote
     */
    public function update(array $data, $id, $attributes = [])
    {
        $quote = $this->find($id);

        $originalLeadId = $quote->lead_id;

        parent::update($data, $id);

        /**
         * Si se proporcionan atributos, solo guarde los atributos proporcionados y regrese.
         */
        if (! empty($attributes)) {
            $conditions = ['entity_type' => $data['entity_type']];

            if (isset($data['quick_add'])) {
                $conditions['quick_add'] = 1;
            }

            $attributes = $this->attributeRepository->where($conditions)
                ->whereIn('code', $attributes)
                ->get();

            $this->attributeValueRepository->save(array_merge($data, [
                'entity_id' => $quote->id,
            ]), $attributes);

            return $quote;
        }

        $this->attributeValueRepository->save(array_merge($data, [
            'entity_id' => $quote->id,
        ]));

        $previousItemIds = $quote->items->pluck('id');

        if (isset($data['items'])) {
            foreach ($data['items'] as $itemId => $itemData) {
                if (Str::contains($itemId, 'item_')) {
                    $this->quoteItemRepository->create(array_merge($itemData, [
                        'quote_id' => $id,
                    ]));

                    if (! empty($data['lead_id'])) {
                        $this->productRepository->updateOrCreate(
                            [
                                'lead_id' => $data['lead_id'],
                                'product_id' => $itemData['product_id'],
                            ],
                            [
                                'lead_id' => $data['lead_id'],
                                'amount' => $itemData['price'] * $itemData['quantity'],
                                'quantity' => $itemData['quantity'],
                                'price' => $itemData['price'],
                            ]
                        );
                    }
                } else {
                    if (is_numeric($index = $previousItemIds->search($itemId))) {
                        $previousItemIds->forget($index);
                    }

                    $this->quoteItemRepository->update($itemData, $itemId);
                }
            }
        }

        foreach ($previousItemIds as $itemId) {
            if (! empty($originalLeadId)) {
                $deletedItem = $quote->items->firstWhere('id', $itemId);

                if ($deletedItem?->product_id) {
                    $this->productRepository->deleteWhere([
                        'lead_id' => $originalLeadId,
                        'product_id' => $deletedItem->product_id,
                    ]);
                }
            }

            $this->quoteItemRepository->delete($itemId);
        }

        return $quote;
    }

    /**
     * Recupera el recuento de clientes según la fecha.
     *
     * @return number
     */
    public function getQuotesCount($startDate, $endDate)
    {
        return $this
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->count();
    }
}
