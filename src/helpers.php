<?php

if (! function_exists('readable_size')) {
    function readable_size($bytes, $decimals = 2, $decimal_separator = ',', $thousands_separator = '.')
    {
        $base = log($bytes) / log(1024);
        $unit = ['bytes', 'kb', 'mb', 'gb', 'tb', 'zb', 'pb'];
        $unit = $unit[floor($base)];
        $size = pow(1024, $base - floor($base));

        if ($unit == 'bytes') {
            $decimals = 0;
        }

        return number_format($size, $decimals, $decimal_separator, $thousands_separator).' '.$unit;
    }
}
