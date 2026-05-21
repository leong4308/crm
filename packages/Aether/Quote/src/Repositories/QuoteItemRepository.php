<?php

namespace Aether\Quote\Repositories;

use Aether\Core\Eloquent\Repository;
use Aether\Product\Repositories\ProductRepository;
use Aether\Quote\Contracts\QuoteItem;
use Illuminate\Container\Container;

class QuoteItemRepository extends Repository
{
    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Quote\Contracts\QuoteItem';
    }

    /**
     * @return mixed
     */
    public function create(array $data)
    {
        if (empty($data['product_id'])) {
            return null;
        }

        $product = $this->productRepository->findOrFail($data['product_id']);

        $quoteItem = parent::create(array_merge($data, [
            'sku' => $product->sku,
            'name' => $product->name,
        ]));

        return $quoteItem;
    }

    /**
     * @param  int  $id
     * @param  string  $attribute
     * @return QuoteItem
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $product = $this->productRepository->findOrFail($data['product_id']);

        $quoteItem = parent::update(array_merge($data, [
            'sku' => $product->sku,
            'name' => $product->name,
        ]), $id);

        return $quoteItem;
    }
}
