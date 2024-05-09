<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

/**
 * @method mixed get(array $parameters = [], bool $update = false)
 */
abstract class CachedComponent extends Component
{
    use CachesValue;

    /** {@inheritdoc} */
    public function resolveView()
    {
        if (
            $this->isUpdating ||
            $this->shouldBeUpdating()
        ) {
            return parent::resolveView();
        }

        $cachedValue = $this->get($this->getParameters()) ?: $this->updateAndGet($this->getParameters());

        return new HtmlString((string) $cachedValue);
    }
}
