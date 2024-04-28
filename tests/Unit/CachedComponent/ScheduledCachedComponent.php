<?php

use Vormkracht10\PermanentCache\Scheduled;

class ScheduledCachedComponent extends \Vormkracht10\PermanentCache\CachedComponent implements Scheduled
{
    protected $store = 'file:unique-cache-key';

    public function render(): string
    {
        sleep(10);

        return <<<'HTML'
            <div class="alert alert-danger">
                This is a cached component!
            </div>
        HTML;
    }

    public static function schedule($callback)
    {
        return $callback->everyMinute();
    }
}
