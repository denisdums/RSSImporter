<?php

namespace RssImporter\Core;

use RssImporter\Core\Interfaces\HookableInterface;

abstract class Hookable implements HookableInterface
{
    public function __construct()
    {
        $this->register();
    }

    public static function add(): Hookable
    {
        return new static();
    }
}

