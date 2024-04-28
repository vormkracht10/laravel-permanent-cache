<?php

use Vormkracht10\PermanentCache\Scheduled;

class CachedComponent extends \Vormkracht10\PermanentCache\CachedComponent
{
    protected $store = 'file:unique-cache-key';

    public function render(): string
    {
        sleep(3);

        return <<<HTML
            <div class="alert alert-danger">
                This is a cached component!
            </div>
        HTML;
    }
}
