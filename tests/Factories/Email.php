<?php

namespace CraigPaul\Mail\Tests\Factories;

use Faker\Factory;

class Email
{
    public function __construct(
        protected string $from,
        protected string $htmlBody,
        protected string $messageId,
        protected string $subject,
        protected string $textBody,
        protected string $to,
    ) {
    }

    public static function createBasic(): self
    {
        $faker = Factory::create();

        return new self(
            from: $faker->email(),
            htmlBody: $faker->randomHtml(),
            messageId: $faker->uuid(),
            subject: $faker->words(asText: true),
            textBody: $faker->sentences(asText: true),
            to: $faker->email(),
        );
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTextBody(): string
    {
        return $this->textBody;
    }

    public function getTo(): string
    {
        return $this->to;
    }
}
