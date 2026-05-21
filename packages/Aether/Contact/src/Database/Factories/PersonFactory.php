<?php

namespace Aether\Contact\Database\Factories;

use Aether\Contact\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente de la fábrica.
     *
     * @var string
     */
    protected $model = Person::class;

    /**
     * Defina el estado predeterminado del modelo.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'emails' => [$this->faker->unique()->safeEmail()],
            'contact_numbers' => [$this->faker->randomNumber(9)],
        ];
    }
}
