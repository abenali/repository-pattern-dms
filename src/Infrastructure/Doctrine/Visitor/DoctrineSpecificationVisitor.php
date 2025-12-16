<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine\Visitor;

use App\Domain\Specification\AndSpecification;
use App\Domain\Specification\AuthorSpecification;
use App\Domain\Specification\CreatedAfterSpecification;
use App\Domain\Specification\CreatedBeforeSpecification;
use App\Domain\Specification\FileTypeSpecification;
use App\Domain\Specification\NotSpecification;
use App\Domain\Specification\OrSpecification;
use App\Domain\Specification\SpecificationVisitorInterface;
use App\Domain\Specification\StatusSpecification;
use App\Domain\Specification\TagsSpecification;
use Doctrine\ORM\QueryBuilder;

final class DoctrineSpecificationVisitor implements SpecificationVisitorInterface
{
    private int $parameterIndex = 0;

    public function __construct(
        private QueryBuilder $queryBuilder,
    ) {
    }

    public function visitAuthor(AuthorSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('author');

        $this->queryBuilder
            ->andWhere("d.author = :$paramName")
            ->setParameter($paramName, $specification->getAuthor());
    }

    public function visitStatus(StatusSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('status');

        $this->queryBuilder
            ->andWhere("d.status = :$paramName")
            ->setParameter($paramName, $specification->getStatus());
    }

    public function visitTags(TagsSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('tags');

        // Check if document has ANY of the tags (OR logic)
        $this->queryBuilder
            ->andWhere(":$paramName MEMBER OF d.tags")
            ->setParameter($paramName, $specification->getTags());
    }

    public function visitCreatedAfter(CreatedAfterSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('createdAfter');

        $this->queryBuilder
            ->andWhere("d.createdAt >= :$paramName")
            ->setParameter($paramName, $specification->getDate());
    }

    public function visitCreatedBefore(CreatedBeforeSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('createdBefore');

        $this->queryBuilder
            ->andWhere("d.createdAt <= :$paramName")
            ->setParameter($paramName, $specification->getDate());
    }

    public function visitFileType(FileTypeSpecification $specification): void
    {
        $paramName = $this->getUniqueParameterName('fileType');

        $this->queryBuilder
            ->andWhere("d.fileType = :$paramName")
            ->setParameter($paramName, $specification->getFileType());
    }

    public function visitAnd(AndSpecification $specification): void
    {
        // Visit each specification recursively
        // They will all be combined with AND (via andWhere)
        foreach ($specification->getSpecifications() as $spec) {
            $spec->accept($this);
        }
    }

    public function visitOr(OrSpecification $specification): void
    {
        // Create OR expression
        $orX = $this->queryBuilder->expr()->orX();

        foreach ($specification->getSpecifications() as $spec) {
            // Create a temporary QueryBuilder to collect this spec's conditions
            $tempQb = clone $this->queryBuilder;
            $tempVisitor = new self($tempQb);

            $spec->accept($tempVisitor);

            // Extract the WHERE part from temp QB
            $wherePart = $tempQb->getDQLPart('where');
            if (null !== $wherePart) {
                $orX->add($wherePart);

                // Merge parameters
                foreach ($tempQb->getParameters() as $param) {
                    if (!$this->queryBuilder->getParameters()->contains($param)) {
                        $this->queryBuilder->setParameter($param->getName(), $param->getValue());
                    }
                }
            }
        }

        if ($orX->count() > 0) {
            $this->queryBuilder->andWhere($orX);
        }
    }

    public function visitNot(NotSpecification $specification): void
    {
        // Create NOT expression
        $tempQb = clone $this->queryBuilder;
        $tempVisitor = new self($tempQb);

        $specification->getSpecification()->accept($tempVisitor);

        $wherePart = $tempQb->getDQLPart('where');
        if (null !== $wherePart) {
            $this->queryBuilder->andWhere(
                $this->queryBuilder->expr()->not($wherePart)
            );

            // Merge parameters
            foreach ($tempQb->getParameters() as $param) {
                if (!$this->queryBuilder->getParameters()->contains($param)) {
                    $this->queryBuilder->setParameter($param->getName(), $param->getValue());
                }
            }
        }
    }

    private function getUniqueParameterName(string $prefix): string
    {
        return $prefix.'_'.$this->parameterIndex++;
    }
}
