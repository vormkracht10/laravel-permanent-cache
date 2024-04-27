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
            return $this->setMarkers(parent::resolveView());
        }

        if (null !== $cachedValue = $this->get($this->getParameters())) {
            return new HtmlString($this->setMarkers((string) $cachedValue));
        }
    }

    public function getMarker(): string
    {
        [$cacheDriver, $cacheKey] = $this::store($this->getParameters());

        $marker = $cacheDriver.':'.$cacheKey;

        if(config('permanent-cache.components.markers.hash')) {
            $marker = md5($marker);
        }

        return '<!--##########'.$marker.'##########-->';
    }

    public function setMarkers($value): string
    {
        if(! config('permanent-cache.components.markers.enabled')) {
            return $value;
        }

        return $this->getMarker().$value.$this->getMarker();
    }
}
