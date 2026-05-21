<?php

namespace Aether\DataTransfer\Helpers\Importers\Leads;

use Aether\Lead\Repositories\LeadRepository;

class Storage
{
    /**
     * Los artículos contienen un identificador como clave e información del producto como valor.
     */
    protected array $items = [];

    /**
     * Columnas que se seleccionarán de la base de datos.
     */
    protected array $selectColumns = ['id', 'title'];

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(protected LeadRepository $leadRepository) {}

    /**
     * Inicializar el almacenamiento.
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Cargue los cables.
     */
    public function load(array $titles = []): void
    {
        if (empty($titles)) {
            $leads = $this->leadRepository->all($this->selectColumns);
        } else {
            $leads = $this->leadRepository->findWhereIn('title', $titles, $this->selectColumns);
        }

        foreach ($leads as $lead) {
            $this->set($lead->title, [
                'id' => $lead->id,
                'title' => $lead->title,
            ]);
        }
    }

    /**
     * Obtenga identificaciones e identificación única.
     */
    public function set(string $title, array $data): self
    {
        $this->items[$title] = $data;

        return $this;
    }

    /**
     * Compruebe si existe una identificación única.
     */
    public function has(string $title): bool
    {
        return isset($this->items[$title]);
    }

    /**
     * Obtenga información de identificación única.
     */
    public function get(string $title): ?array
    {
        if (! $this->has($title)) {
            return null;
        }

        return $this->items[$title];
    }

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * El almacenamiento está vacío.
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}
