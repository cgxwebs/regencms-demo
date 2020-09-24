<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class StoryStatus extends Enum
{
    const Draft = 'draft';
    const Published = 'published';
    const Unlisted = 'unlisted';

    public static function getDescription($value): string
    {
        // Demo
        $desc = [
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Unlisted => 'Unlisted'
        ];
        return isset($desc[$value]) ? $desc[$value] : parent::getDescription($value);
    }
}
