<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;

final class AuthorSpecification implements SpecificationInterface
{
    public function __construct(
        private User $author,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return $document->getAuthor()->getId() === $this->author->getId();
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitAuthor($this);
    }

    public function getAuthor(): User
    {
        return $this->author;
    }
}
