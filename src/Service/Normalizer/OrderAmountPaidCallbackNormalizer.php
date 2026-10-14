<?php

declare(strict_types=1);

namespace Paysera\CheckoutSdk\Service\Normalizer;

use Paysera\CheckoutSdk\Entity\CallbackEventInterface;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\MerchantData;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\Order;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\OrderInfo;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\Payment;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PaymentCollection;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PaymentInfo;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PaymentLink;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PaymentLinkCollection;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PayerInfo;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\PurchaseInfo;
use Paysera\CheckoutSdk\Entity\OrderAmountPaidCallback\Timestamps;
use Paysera\CheckoutSdk\Util\TypeConverter;

class OrderAmountPaidCallbackNormalizer
{
    private TypeConverter $typeConverter;

    public function __construct(TypeConverter $typeConverter)
    {
        $this->typeConverter = $typeConverter;
    }

    /**
     * @param array<string, mixed> $rawData
     * @return OrderAmountPaidCallback
     */
    public function denormalize(array $rawData, CallbackEventInterface $event): OrderAmountPaidCallback
    {
        $order = $this->denormalizeOrder($rawData['order'] ?? []);

        return new OrderAmountPaidCallback($event, $order);
    }

    /**
     * @param array<string, mixed> $orderData
     * @return Order
     */
    private function denormalizeOrder(array $orderData): Order
    {
        $orderInfo = $this->denormalizeOrderInfo($orderData);
        $timestamps = $this->denormalizeTimestamps($orderData);
        $merchantData = new MerchantData($orderData['merchant_data'] ?? []);
        $paymentLinkCollection = $this->denormalizePaymentLinkCollection($orderData['payment_links'] ?? []);

        return new Order(
            $this->typeConverter->convert($orderData['paysera_order_id'] ?? '', TypeConverter::STRING),
            $orderInfo,
            $timestamps,
            $merchantData,
            $paymentLinkCollection,
            $this->typeConverter->convert($orderData['payer_sets_amount'] ?? false, TypeConverter::BOOL),
            $this->denormalizeNullableInt($orderData, 'minimum_amount'),
            $this->denormalizeNullableInt($orderData, 'maximum_amount')
        );
    }

    /**
     * @param array<string, mixed> $orderData
     * @return OrderInfo
     */
    private function denormalizeOrderInfo(array $orderData): OrderInfo
    {
        return new OrderInfo(
            $this->typeConverter->convert($orderData['merchant_order_id'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($orderData['source'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($orderData['amount'] ?? 0, TypeConverter::INT),
            $this->typeConverter->convert($orderData['amount_paid'] ?? 0, TypeConverter::INT),
            $this->typeConverter->convert($orderData['currency'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($orderData['status'] ?? '', TypeConverter::STRING)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return Timestamps
     */
    private function denormalizeTimestamps(array $data): Timestamps
    {
        $timestamps = new Timestamps();

        if (isset($data['created_at'])) {
            $timestamps->setCreatedAt($this->typeConverter->convert($data['created_at'], TypeConverter::INT));
        }

        if (isset($data['updated_at'])) {
            $timestamps->setUpdatedAt($this->typeConverter->convert($data['updated_at'], TypeConverter::INT));
        }

        return $timestamps;
    }

    /**
     * @param array<int, array<string, mixed>> $paymentLinksData
     * @return PaymentLinkCollection
     */
    private function denormalizePaymentLinkCollection(array $paymentLinksData): PaymentLinkCollection
    {
        $collection = new PaymentLinkCollection();

        foreach ($paymentLinksData as $paymentLinkData) {
            $collection->append($this->denormalizePaymentLink($paymentLinkData));
        }

        return $collection;
    }

    /**
     * @param array<string, mixed> $paymentLinkData
     * @return PaymentLink
     */
    private function denormalizePaymentLink(array $paymentLinkData): PaymentLink
    {
        $timestamps = $this->denormalizeTimestamps($paymentLinkData);
        $payerInfo = $this->denormalizePayerInfo($paymentLinkData);
        $paymentCollection = $this->denormalizePaymentCollection($paymentLinkData['payments'] ?? []);

        return new PaymentLink(
            $this->typeConverter->convert($paymentLinkData['id'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($paymentLinkData['name'] ?? '', TypeConverter::STRING),
            $timestamps,
            $payerInfo,
            $paymentCollection,
            $this->typeConverter->convert($paymentLinkData['payer_sets_amount'] ?? false, TypeConverter::BOOL)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function denormalizeNullableInt(array $data, string $key): ?int
    {
        return isset($data[$key]) ? $this->typeConverter->convert($data[$key], TypeConverter::INT) : null;
    }

    /**
     * @param array<string, mixed> $data
     * @return PayerInfo
     */
    private function denormalizePayerInfo(array $data): PayerInfo
    {
        $payerInfo = new PayerInfo();

        if (isset($data['payer_name'])) {
            $payerInfo->setPayerName($this->typeConverter->convert($data['payer_name'], TypeConverter::STRING));
        }

        if (isset($data['payer_email'])) {
            $payerInfo->setPayerEmail($this->typeConverter->convert($data['payer_email'], TypeConverter::STRING));
        }

        if (isset($data['payer_ip_country'])) {
            $payerInfo->setPayerIpCountry($this->typeConverter->convert($data['payer_ip_country'], TypeConverter::STRING));
        }

        if (isset($data['payer_country'])) {
            $payerInfo->setPayerCountry($this->typeConverter->convert($data['payer_country'], TypeConverter::STRING));
        }

        return $payerInfo;
    }

    /**
     * @param array<int, array<string, mixed>> $paymentsData
     * @return PaymentCollection
     */
    private function denormalizePaymentCollection(array $paymentsData): PaymentCollection
    {
        $collection = new PaymentCollection();

        foreach ($paymentsData as $paymentData) {
            $collection->append($this->denormalizePayment($paymentData));
        }

        return $collection;
    }

    /**
     * @param array<string, mixed> $paymentData
     * @return Payment
     */
    private function denormalizePayment(array $paymentData): Payment
    {
        $paymentInfo = $this->denormalizePaymentInfo($paymentData);
        $purchaseInfo = $this->denormalizePurchaseInfo($paymentData);
        $payerInfo = $this->denormalizePayerInfo($paymentData);

        return new Payment(
            $this->typeConverter->convert($paymentData['id'] ?? '', TypeConverter::STRING),
            $paymentInfo,
            $purchaseInfo,
            $payerInfo
        );
    }

    /**
     * @param array<string, mixed> $paymentData
     * @return PaymentInfo
     */
    private function denormalizePaymentInfo(array $paymentData): PaymentInfo
    {
        $timestamps = $this->denormalizeTimestamps($paymentData);

        $paymentInfo = new PaymentInfo(
            $this->typeConverter->convert($paymentData['method'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($paymentData['status'] ?? '', TypeConverter::STRING),
            $timestamps,
            $this->typeConverter->convert($paymentData['purpose'] ?? '', TypeConverter::STRING)
        );

        if (isset($paymentData['payment_country'])) {
            $paymentInfo->setPaymentCountry($this->typeConverter->convert($paymentData['payment_country'], TypeConverter::STRING));
        }

        return $paymentInfo;
    }

    /**
     * @param array<string, mixed> $paymentData
     * @return PurchaseInfo
     */
    private function denormalizePurchaseInfo(array $paymentData): PurchaseInfo
    {
        $purchaseInfo = new PurchaseInfo(
            $this->typeConverter->convert($paymentData['payment_currency'] ?? '', TypeConverter::STRING),
            $this->typeConverter->convert($paymentData['payment_amount'] ?? 0, TypeConverter::INT)
        );

        if (isset($paymentData['original_amount'])) {
            $purchaseInfo->setOriginalAmount($this->typeConverter->convert($paymentData['original_amount'], TypeConverter::INT));
        }

        if (isset($paymentData['original_currency'])) {
            $purchaseInfo->setOriginalCurrency($this->typeConverter->convert($paymentData['original_currency'], TypeConverter::STRING));
        }

        return $purchaseInfo;
    }
}
