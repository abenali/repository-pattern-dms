<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\Specification\FileTypeSpecification;
use App\Domain\Specification\StatusSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class FileTypeSpecificationTest extends TestCase
{
    private string $fileType = 'pdf';
    private User $john;

    protected function setUp(): void
    {
        $this->john = new User('John', 'john@example.com', 'john-id');
    }

    public function testIsSatisfiedByDocumentWithSameFileType(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->john,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new FileTypeSpecification($this->fileType);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedByDocumentWithDifferentFileType(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->john,
            status: DocumentStatus::APPROVED,
            fileType: 'jpg',
            fileSize: 1024,
            tags: []
        );

        $spec = new FileTypeSpecification($this->fileType);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }
}
