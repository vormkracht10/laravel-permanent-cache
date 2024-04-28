<?php

class CachedComponent extends \Vormkracht10\PermanentCache\CachedComponent
{
    protected $store = 'file:unique-cache-key';

    public function __construct(public string $value = '')
    {
    }

    public function render(): string
    {
        $value = $this->value ?? str_random();

        return <<<'HTML'
            <div>This is a cached component: {{ $value }}</div>
        HTML;
    }
}
