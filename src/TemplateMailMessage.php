<?php

namespace Coconuts\Mail;

use Symfony\Component\HttpFoundation\HeaderBag;
use Illuminate\Notifications\Messages\MailMessage;

class TemplateMailMessage extends MailMessage
{
    public $view = 'postmark::postmark';
    public $subject = 'subject';
    public $rawAttachments = [];
    public $priority;
    public $callbacks = [];
    public $viewData = [];

    /** @var int|null */
    public $templateId;

    /** @var string|null */
    public $templateAlias;

    /** @var array */
    public $templateModel;

    /** @var bool */
    public $inlineCss;

    /** @var string */
    public $to;

    /** @var string */
    public $tag;

    /** @var HeaderBag */
    public $headers;

    /** @var bool */
    public $trackOpens;

    /** @var string */
    public $trackLinks;

    /** @var array */
    public $metadata;

    /**
     * @param string|int $id
     * @return self
     */
    public function id($id): self
    {
        $this->templateId = (int) $id;

        return $this;
    }

    public function alias(string $alias): self
    {
        $this->templateAlias = $alias;

        return $this;
    }

    public function model(array $data): self
    {
        $this->templateModel = $data;

        return $this;
    }

    public function data(): array
    {
        return [
            'templateId' => $this->templateId,
            'templateAlias' => $this->templateAlias,
            'templateModel' => $this->templateModel,
            'inlineCss' => $this->inlineCss,
        ];
    }
}
