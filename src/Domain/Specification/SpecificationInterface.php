<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

interface SpecificationInterface
{
    /**
     * Check if a document satisfies this specification (in-memory).
     */
    public function isSatisfiedBy(Document $document): bool;

    /**
     * Accept a visitor for translating to specific query language.
     */
    public function accept(SpecificationVisitorInterface $visitor): void;
}
