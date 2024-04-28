<?php

class CachedComponent extends \Vormkracht10\PermanentCache\CachedComponent
{
    protected $store = 'file:unique-cache-key';

    public function render(): string
    {
        return <<<'HTML'
            <div>This is a cached component: {{ str_random() }}</div>
        HTML;
    }
}
