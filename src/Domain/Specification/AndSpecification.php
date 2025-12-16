<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

final class AndSpecification implements SpecificationInterface
{
    /**
     * @param array<int, SpecificationInterface> $specifications
     */
    public function __construct(
        private array $specifications,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        foreach ($this->specifications as $specification) {
            if (!$specification->isSatisfiedBy($document)) {
                return false; // Si UNE est fausse, tout est faux
            }
        }

        return true; // Toutes sont vraies
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitAnd($this);
    }

    public function getSpecifications(): array
    {
        return $this->specifications;
    }
}
