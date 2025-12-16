<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Specification;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\Specification\CreatedAfterSpecification;
use App\Domain\Specification\CreatedBeforeSpecification;
use App\Domain\ValueObject\DocumentStatus;
use PHPUnit\Framework\TestCase;

final class CreatedBeforeSpecificationTest extends TestCase
{
    public function testIsSatisfiedByDocumentCreatedBeforeDate(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'Recent Doc',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: [],
            createdAt: new \DateTimeImmutable('2024-11-01')
        );

        $spec = new CreatedBeforeSpecification(new \DateTimeImmutable('2024-12-01'));

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }

    public function testIsNotSatisfiedByDocumentCreatedAfterDate(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $document = new Document(
            title: 'Old Doc',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: [],
            createdAt: new \DateTimeImmutable('2024-11-01')
        );

        $spec = new CreatedBeforeSpecification(new \DateTimeImmutable('2024-10-01'));

        // Act & Assert
        $this->assertFalse($spec->isSatisfiedBy($document));
    }

    public function testIsSatisfiedByDocumentCreatedOnExactDate(): void
    {
        // Arrange
        $user = new User('Alice', 'alice@example.com');
        $date = new \DateTimeImmutable('2024-11-01 10:00:00');

        $document = new Document(
            title: 'Exact Date Doc',
            author: $user,
            status: DocumentStatus::DRAFT,
            fileType: 'pdf',
            fileSize: 1024,
            tags: [],
            createdAt: $date
        );

        $spec = new CreatedBeforeSpecification($date);

        // Act & Assert
        $this->assertTrue($spec->isSatisfiedBy($document));
    }
}
