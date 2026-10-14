<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

trait SuggestedAmountRangeTrait
{
    /**
     * Checks only the limits that were sent; the API applies its floor and platform maximum to omitted ones.
     */
    private function validateSuggestedAmountRange(ErrorBag $errorBag, ?int $suggestedAmount, ?int $minimum, ?int $maximum): void
    {
        if ($suggestedAmount === null) {
            return;
        }

        if (($minimum === null || $suggestedAmount >= $minimum) && ($maximum === null || $suggestedAmount <= $maximum)) {
            return;
        }

        if ($minimum !== null && $maximum !== null) {
            $range = sprintf('between %d and %d', $minimum, $maximum);
        } elseif ($minimum !== null) {
            $range = sprintf('at least %d', $minimum);
        } else {
            $range = sprintf('at most %d', $maximum);
        }

        $errorBag->addError(
            'purchase.suggested_amount',
            sprintf('purchase.suggested_amount must be %s (suggested_amount_out_of_limits).', $range)
        );
    }
}
