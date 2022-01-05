<?php

namespace CraigPaul\Mail\Tests\Factories;

use Faker\Factory;

class Email
{
    public function __construct(
        protected string $attachment,
        protected string $bcc,
        protected string $cc,
        protected string $from,
        protected Metadata $header,
        protected string $htmlBody,
        protected string $messageId,
        protected Metadata $metadata,
        protected string $replyTo,
        protected string $subject,
        protected string $tag,
        protected string $textBody,
        protected string $toAddress,
        protected string $toName,
    ) {
    }

    public static function createAttachment(): self
    {
        $faker = Factory::create();

        return new self(
            attachment: $faker->image(),
            bcc: '',
            cc: '',
            from: $faker->email(),
            header: Metadata::create(),
            htmlBody: $faker->randomHtml(),
            messageId: $faker->uuid(),
            metadata: Metadata::create(),
            replyTo: $faker->email(),
            subject: $faker->words(asText: true),
            tag: '',
            textBody: $faker->sentences(asText: true),
            toAddress: $faker->email(),
            toName: $faker->name(),
        );
    }

    public static function createBasic(): self
    {
        $faker = Factory::create();

        return new self(
            attachment: '',
            bcc: '',
            cc: '',
            from: $faker->email(),
            header: Metadata::create(),
            htmlBody: $faker->randomHtml(),
            messageId: $faker->uuid(),
            metadata: Metadata::create(),
            replyTo: $faker->email(),
            subject: $faker->words(asText: true),
            tag: '',
            textBody: $faker->sentences(asText: true),
            toAddress: $faker->email(),
            toName: $faker->name(),
        );
    }

    public static function createCopies(): self
    {
        $faker = Factory::create();

        return new self(
            attachment: '',
            bcc: $faker->email(),
            cc: $faker->email(),
            from: $faker->email(),
            header: Metadata::create(),
            htmlBody: $faker->randomHtml(),
            messageId: $faker->uuid(),
            metadata: Metadata::create(),
            replyTo: $faker->email(),
            subject: $faker->words(asText: true),
            tag: '',
            textBody: $faker->sentences(asText: true),
            toAddress: $faker->email(),
            toName: $faker->name(),
        );
    }

    public static function createCustomizations(): self
    {
        $faker = Factory::create();

        return new self(
            attachment: '',
            bcc: '',
            cc: '',
            from: $faker->email(),
            header: Metadata::create(),
            htmlBody: $faker->randomHtml(),
            messageId: $faker->uuid(),
            metadata: Metadata::create(),
            replyTo: $faker->email(),
            subject: $faker->words(asText: true),
            tag: $faker->words(asText: true),
            textBody: $faker->sentences(asText: true),
            toAddress: $faker->email(),
            toName: $faker->name(),
        );
    }

    public function getAttachment(): string
    {
        return $this->attachment;
    }

    public function getBcc(): string
    {
        return $this->bcc;
    }

    public function getCc(): string
    {
        return $this->cc;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getHeader(): Metadata
    {
        return $this->header;
    }

    public function getHtmlBody(): string
    {
        return $this->htmlBody;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getTextBody(): string
    {
        return $this->textBody;
    }

    public function getToAddress(): string
    {
        return $this->toAddress;
    }

    public function getToName(): string
    {
        return $this->toName;
    }
}
