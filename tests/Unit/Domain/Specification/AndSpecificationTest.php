<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\AndSpecification;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\Specification\StatusSpecification;
use App\Domain\Specification\TagsSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class AndSpecificationTest extends TestCase
{
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        $this->alice = new User('Alice', 'alice@example.com', 'alice-id');
        $this->bob = new User('Bob', 'bob@example.com', 'bob-id');
    }

    public function testIsSatisfiedWhenAllSpecificationsAreSatisfied(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->alice,
            status: DocumentStatus::APPROVED,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new AndSpecification([
            new AuthorSpecification($this->alice),
            new StatusSpecification(DocumentStatus::APPROVED),
        ]);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedWhenOneSpecificationFails(): void
    {
        // Arrange
        $document = new Document(
            title: 'Test Doc',
            author: $this->alice,
            status: DocumentStatus::DRAFT, // Different status
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new AndSpecification([
            new AuthorSpecification($this->alice), // ✅ Satisfied
            new StatusSpecification(DocumentStatus::APPROVED), // ❌ Not satisfied
        ]);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedWhenAllSpecificationsFail(): void
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

        $spec = new AndSpecification([
            new AuthorSpecification($this->alice), // ❌
            new StatusSpecification(DocumentStatus::APPROVED), // ❌
        ]);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }

    public function testIsSatisfiedWithThreeSpecifications(): void
    {
        // Arrange
        $document = new Document(
            title: 'Report',
            author: $this->alice,
            status: DocumentStatus::APPROVED,
            fileType: 'pdf',
            fileSize: 1024,
            tags: ['finance']
        );

        $spec = new AndSpecification([
            new AuthorSpecification($this->alice),
            new StatusSpecification(DocumentStatus::APPROVED),
            new TagsSpecification(['finance']),
        ]);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }
}
