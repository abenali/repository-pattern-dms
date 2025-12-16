<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\StatusSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class StatusSpecificationTest extends TestCase
{
    private DocumentStatus $status;
    private User $john;

    protected function setUp(): void
    {
        $this->status = DocumentStatus::DRAFT;
        $this->john = new User('John', 'john@example.com', 'john-id');
    }

    public function testIsSatisfiedByDocumentWithSameStatus(): void
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

        $spec = new StatusSpecification($this->status);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedByDocumentWithDifferentStatus(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->john,
            status: DocumentStatus::APPROVED,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new StatusSpecification($this->status);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }
}
