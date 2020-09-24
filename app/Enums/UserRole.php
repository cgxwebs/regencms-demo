<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const Superuser = 'superuser';
    const Editor = 'editor';
    const Contributor = 'contributor';
    const Readonly = 'readonly';
    const Disabled = 'disabled';
}
