<?php

namespace Vormkracht10\LaravelOK\Checks\Base;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\ManagesFrequencies;
use Illuminate\Support\Facades\Date;

trait WithPermanentCache
{
    use ManagesFrequencies;

    protected string $expression = '* * * * *';

    protected ?string $name = null;

    protected bool $shouldRun = true;

    public static function config(): static
    {
        $instance = app(static::class);

        $instance->everyMinute();

        return $instance;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name) {
            return $this->name;
        }

        return class_basename(static::class);
    }

    public function shouldRun(): bool
    {
        if (! $this->shouldRun) {
            return false;
        }

        $date = Date::now();

        return (new CronExpression($this->expression))->isDue($date->toDateTimeString());
    }

    public function if(bool $condition)
    {
        $this->shouldRun = $condition;

        return $this;
    }

    public function unless(bool $condition)
    {
        $this->shouldRun = ! $condition;

        return $this;
    }

    abstract public function updateCache();

    public function markAsCrashed()
    {
        // return new Result(Status::CRASHED);
    }
}
