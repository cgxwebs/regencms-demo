<?php

namespace App\Concerns;

use Webmozart\Assert\Assert as BaseAssert;

/**
 * @inheritDoc Webmozart\Assert\Assert
 */
final class Assert extends BaseAssert
{
    protected static function reportInvalidArgument($message)
    {
        throw new AssertException($message);
    }
}
