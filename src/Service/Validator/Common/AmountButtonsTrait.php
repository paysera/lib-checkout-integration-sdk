<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator\Common;

trait AmountButtonsTrait
{
    /**
     * Checks the range only against the limits that were sent; the API applies its floor and platform maximum.
     *
     * @param array<mixed> $amountButtons
     */
    private function validateAmountButtons(ErrorBag $errorBag, array $amountButtons, ?int $minimum, ?int $maximum): void
    {
        foreach ($amountButtons as $amountButton) {
            if (!is_int($amountButton)) {
                $errorBag->addError(
                    'purchase.amount_buttons',
                    'purchase.amount_buttons must contain whole amounts in minor units.'
                );

                return;
            }
        }

        $amountButtons = array_values($amountButtons);
        $count = count($amountButtons);
        if ($count < 3 || $count > 4) {
            $errorBag->addError(
                'purchase.amount_buttons',
                sprintf('purchase.amount_buttons must contain 3 or 4 amounts, %d given (amount_buttons_count).', $count)
            );

            return;
        }

        for ($i = 1; $i < $count; $i++) {
            if ($amountButtons[$i] <= $amountButtons[$i - 1]) {
                $errorBag->addError(
                    'purchase.amount_buttons',
                    'purchase.amount_buttons must be unique and in ascending order (amount_buttons_not_ascending).'
                );

                return;
            }
        }

        foreach ($amountButtons as $amountButton) {
            if (($minimum !== null && $amountButton < $minimum) || ($maximum !== null && $amountButton > $maximum)) {
                $errorBag->addError(
                    'purchase.amount_buttons',
                    sprintf('purchase.amount_buttons must each be %s (amount_buttons_out_of_limits).', $this->describeRange($minimum, $maximum))
                );

                return;
            }
        }
    }

    private function describeRange(?int $minimum, ?int $maximum): string
    {
        if ($minimum !== null && $maximum !== null) {
            return sprintf('between %d and %d', $minimum, $maximum);
        }

        return $minimum !== null ? sprintf('at least %d', $minimum) : sprintf('at most %d', $maximum);
    }
}
