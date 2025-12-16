<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

enum DocumentStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case ARCHIVED = 'archived';
}
