<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Document;
use App\Domain\Query\DocumentQuery;
use App\Domain\Query\PaginatedResult;
use App\Domain\Specification\SpecificationInterface;

interface DocumentRepositoryInterface
{
    /**
     * Find a document by ID.
     *
     * @throws \RuntimeException if document not found
     */
    public function findById(string $id): Document;

    /**
     * Save a document.
     */
    public function save(Document $document): void;

    /**
     * Find documents by query (with specification, sorting, pagination).
     */
    public function findByQuery(DocumentQuery $query): PaginatedResult;

    /**
     * Count documents matching a specification.
     */
    public function countBySpecification(?SpecificationInterface $specification): int;
}
