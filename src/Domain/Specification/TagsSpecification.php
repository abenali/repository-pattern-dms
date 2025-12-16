<?php

declare(strict_types=1);

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

final class TagsSpecification implements SpecificationInterface
{
    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        private array $tags,
    ) {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return $document->hasAnyTag($this->tags);
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitTags($this);
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
