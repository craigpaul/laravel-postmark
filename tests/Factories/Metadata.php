<?php

namespace CraigPaul\Mail\Tests\Factories;

use Faker\Factory;

class Metadata
{
    public function __construct(
        protected string $key,
        protected string $value,
    ) {
    }

    public static function create(): self
    {
        $faker = Factory::create();

        return new self($faker->word(), $faker->word());
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
