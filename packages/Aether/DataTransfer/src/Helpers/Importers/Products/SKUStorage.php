<?php

namespace Aether\DataTransfer\Helpers\Importers\Products;

use Aether\Product\Repositories\ProductRepository;

class SKUStorage
{
    /**
     * Delimitador de información de SKU.
     */
    private const DELIMITER = '|';

    /**
     * Los artículos contienen SKU como clave e información del producto como valor.
     */
    protected array $items = [];

    /**
     * Columnas que se seleccionarán de la base de datos.
     */
    protected array $selectColumns = [
        'id',
        'sku',
    ];

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Inicializar el almacenamiento.
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Cargue el SKU.
     */
    public function load(array $skus = []): void
    {
        if (empty($skus)) {
            $products = $this->productRepository->all($this->selectColumns);
        } else {
            $products = $this->productRepository->findWhereIn('sku', $skus, $this->selectColumns);
        }

        foreach ($products as $product) {
            $this->set($product->sku, [
                'id' => $product->id,
                'sku' => $product->sku,
            ]);
        }
    }

    /**
     * Obtenga información de SKU.
     */
    public function set(string $sku, array $data): self
    {
        $this->items[$sku] = implode(self::DELIMITER, [
            $data['id'],
            $data['sku'],
        ]);

        return $this;
    }

    /**
     * Compruebe si existe SKU.
     */
    public function has(string $sku): bool
    {
        return isset($this->items[$sku]);
    }

    /**
     * Obtenga información de SKU.
     */
    public function get(string $sku): ?array
    {
        if (! $this->has($sku)) {
            return null;
        }

        $data = explode(self::DELIMITER, $this->items[$sku]);

        return [
            'id' => $data[0],
        ];
    }

    /**
     * El almacenamiento está vacío.
     */
    public function isEmpty(): int
    {
        return empty($this->items);
    }
}
