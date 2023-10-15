<?php

namespace Vormkracht10\PermanentCache\Enums;

enum Status: string
{
    case UPDATED = 'updated';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
    case CRASHED = 'crashed';
}
