<?php

namespace App\Domain\Specification;

use App\Domain\Entity\Document;

class FileTypeSpecification implements SpecificationInterface
{
    /**
     * @param string $fileType
     */
    public function __construct(private string $fileType)
    {
    }

    public function isSatisfiedBy(Document $document): bool
    {
        return $document->getFileType() === $this->fileType;
    }

    public function accept(SpecificationVisitorInterface $visitor): void
    {
        $visitor->visitFileType($this);
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }
}
