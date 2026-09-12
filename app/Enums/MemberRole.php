<?php

namespace App\Enums;

enum MemberRole: string
{
    case Admin = 'admin';
    case Events = 'events';
    case Web = 'web';
    case Finance = 'finance';
    case Marketing = 'marketing';
}
