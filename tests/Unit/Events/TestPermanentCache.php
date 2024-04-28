<?php

use Vormkracht10\PermanentCache\Cached;

class TestPermanentCache extends Cached
{
    protected $store = 'file:test';

    public function run(TestEvent $_): mixed
    {
        return 'It works!';
    }
}
