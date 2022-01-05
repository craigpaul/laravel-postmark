<?php

namespace CraigPaul\Mail\Tests\Factories;

use Faker\Factory;

class EmailFactory
{
    public function __construct(
        protected string $body,
        protected string $from,
        protected string $messageId,
        protected string $subject,
        protected string $to,
    ) {
    }

    public static function create(): self
    {
        $faker = Factory::create();

        return new self(
            $faker->sentences(asText: true),
            $faker->email(),
            $faker->uuid(),
            $faker->words(asText: true),
            $faker->email(),
        );
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTo(): string
    {
        return $this->to;
    }
}
