<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use ReflectionClass;

class PermanentCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $connection;
    public $queue;
    public int $timeout;
    public bool $failOnTimeout;
    public int $tries;
    public int $maxExceptions;

    public function displayName(): string
    {
        return $this->permanentCache->getShortName();
    }

    public function tags(): array
    {
        return [
            'event:'.(new ReflectionClass($this->event))->getShortName()
        ];
    }

    public function middleware(): array
    {
        return method_exists($this->permanentCache, 'middleware') ? call_user_func_array([$this->permanentCache, 'middleware'], []) : [];
    }

    public function __construct(
        public $permanentCache,
        public $event
    )
    {
        if($this->permanentCache->connection) {
            $this->onConnection($this->permanentCache->connection);
        }

        if($this->permanentCache->queue) {
            $this->onQueue($this->permanentCache->queue);
        }

        if($this->permanentCache->timeout) {
            $this->timeout = $this->permanentCache->timeout;
        }

        if($this->permanentCache->tries) {
            $this->tries = $this->permanentCache->tries;
        }

        if($this->permanentCache->failOnTimeout) {
            $this->failOnTimeout = $this->permanentCache->failOnTimeout;
        }

        if($this->permanentCache->maxExceptions) {
            $this->maxExceptions = $this->permanentCache->maxExceptions;
        }
    }

    public function handle()
    {
        $this->permanentCache->handle($this->event);
    }
}
