<?php

namespace Aether\DataTransfer\Helpers\Importers\Persons;

use Aether\Contact\Repositories\PersonRepository;

class Storage
{
    /**
     * Los artículos contienen correo electrónico como clave e información del producto como valor.
     */
    protected array $items = [];

    /**
     * Columnas que se seleccionarán de la base de datos.
     */
    protected array $selectColumns = [
        'id',
        'emails',
    ];

    /**
     * Cree una nueva instancia de ayuda.
     *
     * @return void
     */
    public function __construct(protected PersonRepository $personRepository) {}

    /**
     * Inicializar el almacenamiento.
     */
    public function init(): void
    {
        $this->items = [];

        $this->load();
    }

    /**
     * Cargue los correos electrónicos.
     */
    public function load(array $emails = []): void
    {
        if (empty($emails)) {
            $persons = $this->personRepository->all($this->selectColumns);
        } else {
            $persons = $this->personRepository->scopeQuery(function ($query) use ($emails) {
                return $query->where(function ($subQuery) use ($emails) {
                    foreach ($emails as $email) {
                        $subQuery->orWhereJsonContains('emails', ['value' => $email]);
                    }
                });
            })->all($this->selectColumns);
        }

        $persons->each(function ($person) {
            collect($person->emails)
                ->each(fn ($email) => $this->set($email['value'], $person->id));
        });
    }

    /**
     * Obtener información por correo electrónico.
     */
    public function set(string $email, int $id): self
    {
        $this->items[$email] = $id;

        return $this;
    }

    /**
     * Compruebe si existe el correo electrónico.
     */
    public function has(string $email): bool
    {
        return isset($this->items[$email]);
    }

    /**
     * Obtener información por correo electrónico.
     */
    public function get(string $email): ?int
    {
        if (! $this->has($email)) {
            return null;
        }

        return $this->items[$email];
    }

    /**
     * El almacenamiento está vacío.
     */
    public function isEmpty(): int
    {
        return empty($this->items);
    }
}
