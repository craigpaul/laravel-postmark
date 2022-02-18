<?php

namespace CraigPaul\Mail\Tests\Notifications;

use CraigPaul\Mail\TemplatedMailMessage;
use CraigPaul\Mail\Tests\Factories\Template;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TemplatedNotification extends Notification
{
    public function __construct(
        protected Template $template,
        protected string $uses,
    ) {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(): MailMessage
    {
        $message = (new TemplatedMailMessage());

        if ($this->uses === 'alias') {
            $message = $message->alias($this->template->getAlias());
        } else {
            $message = $message->identifier($this->template->getId());
        }

        return $message->include($this->template->getModel());
    }
}
