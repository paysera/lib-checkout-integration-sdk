<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Entity;

/**
 * Payment Currency constants following ISO 4217 standard.
 */
class PaymentCurrency
{
    public const SUPPORTED_CURRENCIES = [
        'EUR', 'ALL', 'XAU', 'XAG', 'AED', 'AUD', 'BGN', 'BRL', 'CAD', 'CHF',
        'CNY', 'CZK', 'DKK', 'EGP', 'GBP', 'HKD', 'HUF', 'ILS', 'INR', 'JPY',
        'MDL', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'RSD', 'RUB', 'SEK', 'SGD',
        'THB', 'TRY', 'UAH', 'USD', 'ZAR', 'ARS', 'AZN', 'BAM', 'BYR', 'CLP',
        'COP', 'DZD', 'GEL', 'IQD', 'JOD', 'KWD', 'KZT', 'LTL', 'LVL', 'MAD',
        'MKD', 'NGN', 'RON', 'BYN', 'CVE', 'HRK',
    ];
}
