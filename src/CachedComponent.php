<?php

namespace Vormkracht10\PermanentCache;

use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

abstract class CachedComponent extends Component implements Scheduled
{
    use Cached;

    public function resolveView()
    {
        if(null !== $cache = $this->get()) {
            return new HtmlString($cache);
        }

        return parent::resolveView();
    }
}
