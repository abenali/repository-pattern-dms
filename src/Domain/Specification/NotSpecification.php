<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

final class NotSpecification implements SpecificationInterface
{
    public function __construct(
        private SpecificationInterface $specification,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return !$this->specification->isSatisfiedBy($document);
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitNot($this);
    }

    public function getSpecification(): SpecificationInterface
    {
        return $this->specification;
    }
}
