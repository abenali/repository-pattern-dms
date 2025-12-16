<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchDocuments;

final class SearchDocumentsCommand
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public readonly array $filters = [],
        public readonly ?string $orderBy = null,
        public readonly string $orderDirection = 'ASC',
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
    }
}
