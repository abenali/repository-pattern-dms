<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchDocuments;

use App\Domain\Entity\Document;

final class SearchDocumentsResponse
{
    /**
     * @param array<int, Document> $documents
     */
    public function __construct(
        public readonly array $documents,
        public readonly int $total,
        public readonly int $page,
        public readonly int $limit,
        public readonly int $totalPages,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'data' => array_map(fn (Document $doc) => [
                'id' => $doc->getId(),
                'title' => $doc->getTitle(),
                'author' => [
                    'id' => $doc->getAuthor()->getId(),
                    'name' => $doc->getAuthor()->getName(),
                ],
                'status' => $doc->getStatus()->value,
                'fileType' => $doc->getFileType(),
                'fileSize' => $doc->getFileSize(),
                'tags' => $doc->getTags(),
                'createdAt' => $doc->getCreatedAt()->format(\DateTimeInterface::ATOM),
                'updatedAt' => $doc->getUpdatedAt()->format(\DateTimeInterface::ATOM),
            ], $this->documents),
            'pagination' => [
                'total' => $this->total,
                'page' => $this->page,
                'limit' => $this->limit,
                'totalPages' => $this->totalPages,
            ],
        ];
    }
}
