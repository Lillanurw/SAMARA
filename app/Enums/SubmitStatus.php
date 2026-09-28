<?php

namespace App\Enums;

enum SubmitStatus: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case CORRECTED = 'CORRECTED';
}
