<?php

namespace CraigPaul\Mail\Tests\Factories;

class Attachment
{
    public static function create(): string
    {
        $image = imagecreatetruecolor(200, 50);
        imagepng($image, '/tmp/test-image.png');
        return '/tmp/test-image.png';
    }
}
