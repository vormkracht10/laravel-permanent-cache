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
    public function resolveView(): \Illuminate\Contracts\View\View|HtmlString|\Illuminate\Contracts\Support\Htmlable|\Closure|string
    {
        $this->parameters = collect((new \ReflectionClass(static::class))
            ->getProperties(\ReflectionProperty::IS_PUBLIC))
            ->filter(fn (\ReflectionProperty $p) => $p->class === static::class)
            ->mapWithKeys(fn (\ReflectionProperty $p) => [$p->name => $p->getValue($this)])
            ->toArray();

        if(
            $this->isUpdating ||
            $this->shouldBeUpdating()
        ) {
            return parent::resolveView();
        }

        if (null !== $cachedValue = $this->get($this->parameters)) {
            return new HtmlString((string) $cachedValue);
        }
    }
}
