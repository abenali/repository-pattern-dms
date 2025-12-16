<?php

declare(strict_types=1);

namespace App\Domain\Specification;

interface SpecificationVisitorInterface
{
    public function visitAuthor(AuthorSpecification $specification): void;
    public function visitStatus(StatusSpecification $specification): void;
    public function visitTags(TagsSpecification $specification): void;
    public function visitCreatedAfter(CreatedAfterSpecification $specification): void;
    public function visitCreatedBefore(CreatedBeforeSpecification $specification): void;
    public function visitFileType(FileTypeSpecification $specification): void;
    public function visitAnd(AndSpecification $specification): void;
    public function visitOr(OrSpecification $specification): void;
    public function visitNot(NotSpecification $specification): void;
}
