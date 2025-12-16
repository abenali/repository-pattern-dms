<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\DocumentStatus;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'documents')]
class Document
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        #[ORM\Column(type: 'string', length: 255)]
        private string $title,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false)]
        private User $author,
        #[ORM\Column(type: 'string', enumType: DocumentStatus::class)]
        private DocumentStatus $status,
        #[ORM\Column(type: 'string', length: 10)]
        private string $fileType,
        #[ORM\Column(type: 'integer')]
        private int $fileSize,
        #[ORM\Column(type: 'json')]
        private array $tags = [],
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
        ?string $id = null,
    ) {
        $this->id = $id ?? Uuid::v4()->toRfc4122();
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getStatus(): DocumentStatus
    {
        return $this->status;
    }

    public function getFileType(): string
    {
        return $this->fileType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @return array<int, string>
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * @param array<int, string> $tags
     */
    public function hasAnyTag(array $tags): bool
    {
        foreach ($tags as $tag) {
            if ($this->hasTag($tag)) {
                return true;
            }
        }

        return false;
    }

    public function updateStatus(DocumentStatus $status): void
    {
        $this->status = $status;
        $this->updatedAt = new \DateTimeImmutable();
    }
}
