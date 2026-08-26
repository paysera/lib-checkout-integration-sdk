<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\ProjectWebsite;

/**
 * @template ProjectWebsite
 * @extends Collection<ProjectWebsite>
 *
 * @method ProjectWebsiteCollection<ProjectWebsite> filter(callable $filterFunction)
 * @method void append(ProjectWebsite $value)
 * @method ProjectWebsite|null get(int $index = null)
 */
class ProjectWebsiteCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof ProjectWebsite;
    }

    public function current(): ProjectWebsite
    {
        return parent::current();
    }

    public function getItemType(): string
    {
        return ProjectWebsite::class;
    }
}
