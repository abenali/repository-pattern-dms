<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Repository;

use App\Domain\Entity\Document;
use App\Domain\Query\DocumentQuery;
use App\Domain\Query\PaginatedResult;
use App\Domain\Repository\DocumentRepositoryInterface;
use App\Domain\Specification\SpecificationInterface;
use App\Infrastructure\Doctrine\Visitor\DoctrineSpecificationVisitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Document>
 */
final class DocumentRepository extends ServiceEntityRepository implements DocumentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    public function findById(string $id): Document
    {
        $document = $this->find($id);

        if (!$document instanceof Document) {
            throw new \RuntimeException(sprintf('Document with ID "%s" not found', $id));
        }

        return $document;
    }

    public function save(Document $document): void
    {
        $this->getEntityManager()->persist($document);
        $this->getEntityManager()->flush();
    }

    public function findByQuery(DocumentQuery $query): PaginatedResult
    {
        // 1. Create QueryBuilder
        $qb = $this->createQueryBuilder('d');

        // 2. Apply specification if present
        if (null !== $query->specification) {
            $visitor = new DoctrineSpecificationVisitor($qb);
            $query->specification->accept($visitor);
        }

        // 3. Apply sorting
        if (null !== $query->orderBy) {
            $qb->orderBy('d.'.$query->orderBy, $query->orderDirection);
        }

        // 4. Count total (before pagination)
        $countQb = clone $qb;
        $total = (int) $countQb
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // 5. Apply pagination
        $qb->setFirstResult($query->getOffset())
            ->setMaxResults($query->limit);

        // 6. Execute query
        $items = $qb->getQuery()->getResult();

        // 7. Return paginated result
        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit
        );
    }

    public function countBySpecification(?SpecificationInterface $specification): int
    {
        $qb = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)');

        if (null !== $specification) {
            $visitor = new DoctrineSpecificationVisitor($qb);
            $specification->accept($visitor);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
