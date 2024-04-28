<?php

class CachedComponent extends \Vormkracht10\PermanentCache\CachedComponent
{
    protected $store = 'file:unique-cache-key';

    public function render(): string
    {
        sleep(2);

        return <<<'HTML'
            <div>This is a {{ $value ?? 'cached' }} component!</div>
        HTML;
    }
}
