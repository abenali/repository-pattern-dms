<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

final class OrSpecification implements SpecificationInterface
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
            if ($specification->isSatisfiedBy($document)) {
                return true; // Si au moins UNE est vraie, tout est vraie
            }
        }

        return false; // Sinon toutes sont fausses
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitOr($this);
    }

    public function getSpecifications(): array
    {
        return $this->specifications;
    }
}
