<?php

namespace CraigPaul\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TemplatedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public string $alias;

    public int $id;

    public array $model;

    public function build(): self
    {
        return $this->view('postmark::template');
    }

    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function identifier(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function include(array $data): self
    {
        $this->model = $data;

        return $this;
    }
}
