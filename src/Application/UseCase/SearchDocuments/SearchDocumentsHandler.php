<?php

declare(strict_types=1);

namespace App\Application\UseCase\SearchDocuments;

use App\Domain\Query\DocumentQuery;
use App\Domain\Repository\DocumentRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Specification\AndSpecification;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\Specification\CreatedAfterSpecification;
use App\Domain\Specification\CreatedBeforeSpecification;
use App\Domain\Specification\FileTypeSpecification;
use App\Domain\Specification\StatusSpecification;
use App\Domain\Specification\TagsSpecification;
use App\Domain\ValueObject\DocumentStatus;

final class SearchDocumentsHandler
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(SearchDocumentsCommand $command): SearchDocumentsResponse
    {
        // 1. Build specifications from filters
        $specifications = [];

        if (isset($command->filters['authorId'])) {
            $author = $this->userRepository->findById($command->filters['authorId']);
            $specifications[] = new AuthorSpecification($author);
        }

        if (isset($command->filters['status'])) {
            $status = DocumentStatus::from($command->filters['status']);
            $specifications[] = new StatusSpecification($status);
        }

        if (isset($command->filters['tags']) && !empty($command->filters['tags'])) {
            $specifications[] = new TagsSpecification($command->filters['tags']);
        }

        if (isset($command->filters['createdAfter'])) {
            $date = new \DateTimeImmutable($command->filters['createdAfter']);
            $specifications[] = new CreatedAfterSpecification($date);
        }

        if (isset($command->filters['createdBefore'])) {
            $date = new \DateTimeImmutable($command->filters['createdBefore']);
            $specifications[] = new CreatedBeforeSpecification($date);
        }

        if (isset($command->filters['fileType'])) {
            $specifications[] = new FileTypeSpecification($command->filters['fileType']);
        }

        // 2. Combine specifications with AND
        $specification = match (count($specifications)) {
            0 => null,
            1 => $specifications[0],
            default => new AndSpecification($specifications),
        };

        // 3. Create query object
        $query = new DocumentQuery(
            specification: $specification,
            orderBy: $command->orderBy,
            orderDirection: $command->orderDirection,
            page: $command->page,
            limit: $command->limit
        );

        // 4. Execute query
        $result = $this->documentRepository->findByQuery($query);

        // 5. Return response
        return new SearchDocumentsResponse(
            documents: $result->items,
            total: $result->total,
            page: $result->page,
            limit: $result->limit,
            totalPages: $result->getTotalPages()
        );
    }
}
