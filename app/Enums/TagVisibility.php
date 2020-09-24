<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class TagVisibility extends Enum
{
    const Visible = 'visible';
    const Unlisted = 'unlisted';
    const Hidden = 'hidden';
}
