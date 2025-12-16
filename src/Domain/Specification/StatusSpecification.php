<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\ValueObject\DocumentStatus;

final class StatusSpecification implements SpecificationInterface
{
    public function __construct(
        private DocumentStatus $status,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return $document->getStatus() === $this->status;
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitStatus($this);
    }

    public function getStatus(): DocumentStatus
    {
        return $this->status;
    }
}
