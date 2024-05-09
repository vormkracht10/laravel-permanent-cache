<?php

use Illuminate\Contracts\Queue\ShouldQueue;
use Vormkracht10\PermanentCache\CachedComponent;
use Vormkracht10\PermanentCache\Scheduled;

class ScheduledAndQueuedCachedComponent extends CachedComponent implements Scheduled, ShouldQueue
{
    protected $store = 'file:unique-cache-key';

    public function __construct(public string $value = '')
    {
    }

    public function render(): string
    {
        $value = $this->value ?? str_random();

        return <<<'HTML'
            <div>This is a {{ $value ?? 'cached' }} component!</div>
        HTML;
    }

    public static function schedule($callback)
    {
        return $callback->everyMinute();
    }
}
