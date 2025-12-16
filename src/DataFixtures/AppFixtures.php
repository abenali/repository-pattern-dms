<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Domain\Entity\Document;
use App\Domain\Entity\User;
use App\Domain\ValueObject\DocumentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const array FILE_TYPES = ['pdf', 'docx', 'xlsx', 'pptx', 'txt'];
    private const array TAGS = ['finance', 'legal', 'hr', 'marketing', 'sales', 'tech', 'urgent', 'confidential', 'archived'];
    private const array STATUSES = [
        DocumentStatus::DRAFT,
        DocumentStatus::PENDING,
        DocumentStatus::APPROVED,
        DocumentStatus::ARCHIVED,
    ];

    public function load(ObjectManager $manager): void
    {
        echo "\n=== CREATING USERS ===\n";

        // Create 5 users
        $users = [
            new User('Alice Johnson', 'alice@example.com'),
            new User('Bob Smith', 'bob@example.com'),
            new User('Charlie Brown', 'charlie@example.com'),
            new User('Diana Prince', 'diana@example.com'),
            new User('Eve Martinez', 'eve@example.com'),
        ];

        foreach ($users as $user) {
            $manager->persist($user);
            echo "Created user: {$user->getName()} ({$user->getId()})\n";
        }

        echo "\n=== CREATING 50 DOCUMENTS ===\n";

        // Create 50 documents with variety
        for ($i = 1; $i <= 50; ++$i) {
            $author = $users[array_rand($users)];
            $status = self::STATUSES[array_rand(self::STATUSES)];
            $fileType = self::FILE_TYPES[array_rand(self::FILE_TYPES)];

            // Random tags (1 to 3 tags per document)
            $numTags = random_int(1, 3);
            $documentTags = [];
            $availableTags = self::TAGS;
            shuffle($availableTags);
            for ($t = 0; $t < $numTags; ++$t) {
                $documentTags[] = $availableTags[$t];
            }

            // Random file size (100 KB to 10 MB)
            $fileSize = rand(100000, 10000000);

            // Random creation date (last 6 months)
            $daysAgo = rand(1, 180);
            $createdAt = new \DateTimeImmutable("-{$daysAgo} days");

            // Updated date (between created and now)
            $updatedDaysAgo = rand(0, $daysAgo);
            $updatedAt = new \DateTimeImmutable("-{$updatedDaysAgo} days");

            $document = new Document(
                title: $this->generateTitle($i, $status, $documentTags),
                author: $author,
                status: $status,
                fileType: $fileType,
                fileSize: $fileSize,
                tags: $documentTags,
                createdAt: $createdAt,
                updatedAt: $updatedAt
            );

            $manager->persist($document);

            // Display info every 10 documents
            if (0 === $i % 10) {
                echo "Created $i documents...\n";
            }
        }

        $manager->flush();

        echo "\n=== FIXTURES SUMMARY ===\n";
        echo 'Users created: '.count($users)."\n";
        echo "Documents created: 50\n";
        echo "\nDocument distribution:\n";
        echo "- By status: Mixed (Draft, Pending, Approved, Archived)\n";
        echo "- By file type: Mixed (pdf, docx, xlsx, pptx, txt)\n";
        echo "- Tags per document: 1-3 random tags\n";
        echo "- Date range: Last 6 months\n";
        echo "\n=== Sample User IDs (for testing) ===\n";
        foreach ($users as $idx => $user) {
            echo ($idx + 1).". {$user->getName()}: {$user->getId()}\n";
        }
        echo "========================\n\n";
    }

    /**
     * @param array<int, string> $tags
     */
    private function generateTitle(int $number, DocumentStatus $status, array $tags): string
    {
        $prefixes = [
            'Annual Report',
            'Financial Statement',
            'Contract Agreement',
            'Project Proposal',
            'Meeting Minutes',
            'Policy Document',
            'Technical Specification',
            'Marketing Plan',
            'Sales Forecast',
            'Employee Handbook',
            'Product Roadmap',
            'Budget Analysis',
            'Compliance Report',
            'Strategic Plan',
            'Quarterly Review',
        ];

        $prefix = $prefixes[array_rand($prefixes)];
        $year = rand(2023, 2024);

        return sprintf(
            '%s %d - %s [%s]',
            $prefix,
            $year,
            strtoupper(implode(', ', array_slice($tags, 0, 1))),
            $number
        );
    }
}
