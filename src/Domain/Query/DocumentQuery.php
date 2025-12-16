<?php

declare(strict_types=1);

namespace App\Domain\Query;

use App\Domain\Specification\SpecificationInterface;

final class DocumentQuery
{
    public function __construct(
        public readonly ?SpecificationInterface $specification = null,
        public readonly ?string $orderBy = null,
        public readonly string $orderDirection = 'ASC',
        public readonly int $page = 1,
        public readonly int $limit = 20,
    ) {
        if (!in_array($this->orderDirection, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException('Order direction must be ASC or DESC');
        }

        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be >= 1');
        }

        if ($this->limit < 1 || $this->limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
