<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity\Collection;

use Paysera\CheckoutSdk\Entity\ProjectEligibilityReason;

/**
 * @extends Collection<ProjectEligibilityReason>
 *
 * @method void append(ProjectEligibilityReason $value)
 * @method ProjectEligibilityReason|null get(int $index = null)
 */
class ProjectEligibilityReasonCollection extends Collection
{
    public function isCompatible(object $item): bool
    {
        return $item instanceof ProjectEligibilityReason;
    }

    public function getItemType(): string
    {
        return ProjectEligibilityReason::class;
    }

    /**
     * @return string[]
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this as $reason) {
            $values[] = $reason->getValue();
        }

        return $values;
    }
}
