<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\AndSpecification;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\Specification\NotSpecification;
use App\Domain\Specification\StatusSpecification;
use App\Domain\Specification\TagsSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class NotSpecificationTest extends TestCase
{
    private User $alice;
    private User $bob;

    protected function setUp(): void
    {
        $this->alice = new User('Alice', 'alice@example.com', 'alice-id');
        $this->bob = new User('Bob', 'bob@example.com', 'bob-id');
    }

    public function testIsNotSatisfiedWhenSpecificationIsTrue(): void
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

        $spec = new NotSpecification(
            new StatusSpecification(DocumentStatus::APPROVED),
        );

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }

    public function testIsSatisfiedWhenSpecificationIsFalse(): void
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

        $spec = new NotSpecification(
            new StatusSpecification(DocumentStatus::APPROVED)
        );

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }
}
