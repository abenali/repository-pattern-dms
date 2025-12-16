<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\TagsSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class TagsSpecificationTest extends TestCase
{
    public function testIsSatisfiedByDocumentWithOneMatchingTag(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'Finance Report',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: ['finance', 'Q4', 'report']
        );

        $spec = new TagsSpecification(['finance']);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsSatisfiedByDocumentWithMultipleMatchingTags(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'Finance Report',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: ['finance', 'Q4', 'report']
        );

        $spec = new TagsSpecification(['finance', 'legal']);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document)); // Has "finance"
    }

    public function testIsNotSatisfiedByDocumentWithoutMatchingTags(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'HR Document',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: ['hr', 'policy']
        );

        $spec = new TagsSpecification(['finance', 'legal']);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedByDocumentWithNoTags(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'Untagged Doc',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: []
        );

        $spec = new TagsSpecification(['finance']);

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }
}
