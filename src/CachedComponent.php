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
        if (null !== $cache = $this->get()) {
            return new HtmlString((string) $cache);
        }

        return parent::resolveView();
    }
}
