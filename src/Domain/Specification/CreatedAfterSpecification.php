<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

final class CreatedAfterSpecification implements SpecificationInterface
{
    public function __construct(
        private \DateTimeImmutable $date,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return $document->getCreatedAt() >= $this->date;
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitCreatedAfter($this);
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
