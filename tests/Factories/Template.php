<?php

namespace CraigPaul\Mail\Tests\Factories;

use Faker\Factory;
use Illuminate\Support\Facades\Config;

class Template
{
    public function __construct(
        protected string $alias,
        protected string $fromAddress,
        protected string $fromName,
        protected int $id,
        protected string $messageId,
        protected array $model,
        protected string $toAddress,
    ) {
    }

    public static function create(): self
    {
        $faker = Factory::create();
        $from = Config::get('mail.from');

        return new self(
            alias: $faker->words(asText: true),
            fromAddress: $from['address'],
            fromName: $from['name'],
            id: $faker->numberBetween(),
            messageId: $faker->uuid(),
            model: [$faker->word() => $faker->word()],
            toAddress: $faker->email(),
        );
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getModel(): array
    {
        return $this->model;
    }

    public function getToAddress(): string
    {
        return $this->toAddress;
    }
}
