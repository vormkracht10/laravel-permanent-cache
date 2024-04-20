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

    /** {@inheritdoc} */
    public function resolveView()
    {
        $this->parameters = collect((new \ReflectionClass(static::class))
            ->getProperties(\ReflectionProperty::IS_PUBLIC))
            ->filter(fn (\ReflectionProperty $p) => $p->class === static::class)
            ->mapWithKeys(fn (\ReflectionProperty $p) => [$p->name => $p->getValue($this)])
            ->toArray();

        if ($this->isUpdating()) {
            $value = $this->get($this->parameters);

            return is_null($value) ? parent::resolveView() : new HtmlString($value);
        }

        if (null !== $cache = $this->get($this->parameters, update: true)) {
            return new HtmlString((string) $cache);
        }

        return parent::resolveView();
    }
}
