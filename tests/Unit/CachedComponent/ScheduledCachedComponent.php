<?php

use Vormkracht10\PermanentCache\Scheduled;

class ScheduledCachedComponent extends \Vormkracht10\PermanentCache\CachedComponent implements Scheduled
{
    protected $store = 'file:unique-cache-key';

    public function __construct(public string $parameter = '')
    {
    }

    public function render(): string
    {
        $value = $this->parameter;

        return <<<'HTML'
            <div>This is a {{ $value ?? 'cached' }} component!</div>
        HTML;
    }

    public static function schedule($callback)
    {
        return $callback->everyMinute();
    }
}
