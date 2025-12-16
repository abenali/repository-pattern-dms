<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class AuthorSpecificationTest extends TestCase
{
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        $this->alice = new User('Alice', 'alice@example.com', 'alice-id');
        $this->bob = new User('Bob', 'bob@example.com', 'bob-id');
    }

    public function testIsSatisfiedByDocumentWithSameAuthor(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->alice,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new AuthorSpecification($this->alice);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedByDocumentWithDifferentAuthor(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->bob,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new AuthorSpecification($this->alice);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }
}
