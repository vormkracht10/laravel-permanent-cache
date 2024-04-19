<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

/**
 * @method string render()
 */
abstract class CachedComponent extends Component
{
    use CachesValue;

    protected function registerParameters(array $args): void
    {
        $parameters = (new \ReflectionClass(static::class))->getConstructor()->getParameters();
        $this->parameters = collect($args)->mapWithKeys(fn ($v, $k) => [$parameters[$k]->name => $v])->toArray();
    }

    /** {@inheritdoc} */
    public function resolveView()
    {
        if ($this->isUpdating()) {
            return $this->get($this->parameters) ?? parent::resolveView();
        }

        if (null !== $cache = $this->get($this->parameters, update: true)) {
            return new HtmlString((string) $cache);
        }

        return parent::resolveView();
    }
}
