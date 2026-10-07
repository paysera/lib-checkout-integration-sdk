<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Validator;

use Paysera\CheckoutSdk\Entity\PaymentApiCallbackHeaders;
use Paysera\CheckoutSdk\Exception\ValidationException;
use Paysera\CheckoutSdk\Service\Validator\Common\StringValidator;

class PaymentApiCallbackHeadersValidator
{
    public const EXPECTED_SIGNATURE_ALG = 'HMAC-SHA256';

    private StringValidator $stringValidator;

    public function __construct(StringValidator $stringValidator)
    {
        $this->stringValidator = $stringValidator;
    }

    /**
     * @throws ValidationException
     */
    public function validate(PaymentApiCallbackHeaders $paymentApiCallbackHeaders): void
    {
        if ($paymentApiCallbackHeaders->getSignature() === '') {
            throw new ValidationException('Missing callback signature');
        }
        if (preg_match('/^[0-9a-f]{64}\z/', $paymentApiCallbackHeaders->getSignature()) === 0) {
            throw new ValidationException('Invalid callback signature provided');
        }
        if (strcasecmp($paymentApiCallbackHeaders->getSignatureAlg(), self::EXPECTED_SIGNATURE_ALG) !== 0) {
            throw new ValidationException('Unsupported signature algorithm provided');
        }
        if ($paymentApiCallbackHeaders->getCreatedAt() <= 0) {
            throw new ValidationException('Missing callback created date');
        }

        $this->stringValidator->validateUuid(
            $paymentApiCallbackHeaders->getRequestId(),
            PaymentApiCallbackHeaders::REQUEST_ID_HEADER_NAME
        );
        $this->stringValidator->validateUuid(
            $paymentApiCallbackHeaders->getCallbackId(),
            PaymentApiCallbackHeaders::CALLBACK_ID_HEADER_NAME
        );
    }
}
