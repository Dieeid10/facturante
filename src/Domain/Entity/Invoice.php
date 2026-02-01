<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\InvalidInvoiceAmountException;
use App\Domain\ValueObject\MoneyAmount;
use App\Domain\ValueObject\InvoiceDate;

final class Invoice
{
    private ?int $id = null;
    private ?int $voucherNumber = null;
    private ?string $cae = null;
    private ?InvoiceDate $caeExpirationDate = null;
    private string $status = self::STATUS_DRAFT;

    public const STATUS_DRAFT   = 'draft';
    public const STATUS_CREATED = 'created';
    public const STATUS_ERROR   = 'error';

    public function __construct(
        private int $pointOfSale,
        private int $voucherType,
        private int $concept,
        private int $documentType,
        private int $documentNumber,
        private InvoiceDate $voucherDate,
        private MoneyAmount $totalAmount,
        private MoneyAmount $untaxedAmount,
        private MoneyAmount $netAmount,
        private MoneyAmount $exemptAmount,
        private MoneyAmount $vatAmount,
        private MoneyAmount $taxAmount,
        private float $currencyRate,
        private array $vatItems,
        private ?InvoiceDate $serviceFromDate = null,
        private ?InvoiceDate $serviceToDate = null,
        private ?InvoiceDate $paymentDueDate = null,
        private ?int $receiverVatConditionId = null
    ) {
        $this->totalAmount   = $totalAmount;
        $this->untaxedAmount = $untaxedAmount;
        $this->netAmount     = $netAmount;
        $this->exemptAmount  = $exemptAmount;
        $this->vatAmount     = $vatAmount;
        $this->taxAmount     = $taxAmount;

        $this->assertValidAmounts();
    }

    /**
     * Invariante:
     * ImpTotal = ImpTotConc + ImpNeto + ImpOpEx + ImpTrib + ImpIVA
     */
    private function assertValidAmounts(): void
    {
        $calculatedTotal =
            $this->untaxedAmount
                ->add($this->netAmount)
                ->add($this->exemptAmount)
                ->add($this->taxAmount)
                ->add($this->vatAmount);

        if (!$calculatedTotal->equals($this->totalAmount)) {
            throw new InvalidInvoiceAmountException(
                sprintf(
                    'Importe total inválido. Esperado: %.2f, recibido: %.2f',
                    $calculatedTotal->toFloat(),
                    $this->totalAmount->toFloat()
                )
            );
        }
    }

    public function markAsCreated(
        int $voucherNumber,
        string $cae,
        InvoiceDate $caeExpirationDate
    ): void {
        $this->voucherNumber = $voucherNumber;
        $this->cae = $cae;
        $this->caeExpirationDate = $caeExpirationDate;
        $this->status = self::STATUS_CREATED;
    }

    public function markAsFailed(string $reason): void
    {
        $this->status = self::STATUS_ERROR;
    }

    // -----------------
    // Setters
    // -----------------

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    // -----------------
    // Getters
    // -----------------

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getStatus(): string
    {
        return $this->status;
    }

    public function getVoucherNumber(): ?int
    {
        return $this->voucherNumber;
    }
    public function getCae(): ?string
    {
        return $this->cae;
    }
    public function getCaeExpirationDate(): ?InvoiceDate
    {
        return $this->caeExpirationDate;
    }

    public function getPointOfSale(): int
    {
        return $this->pointOfSale;
    }
    public function getVoucherType(): int
    {
        return $this->voucherType;
    }
    public function getConcept(): int
    {
        return $this->concept;
    }
    public function getDocumentType(): int
    {
        return $this->documentType;
    }
    public function getDocumentNumber(): int
    {
        return $this->documentNumber;
    }

    public function getVoucherDate(): InvoiceDate
    {
        return $this->voucherDate;
    }

    public function getTotalAmount(): MoneyAmount
    {
        return $this->totalAmount;
    }
    public function getUntaxedAmount(): MoneyAmount
    {
        return $this->untaxedAmount;
    }
    public function getNetAmount(): MoneyAmount
    {
        return $this->netAmount;
    }
    public function getExemptAmount(): MoneyAmount
    {
        return $this->exemptAmount;
    }
    public function getVatAmount(): MoneyAmount
    {
        return $this->vatAmount;
    }
    public function getTaxAmount(): MoneyAmount
    {
        return $this->taxAmount;
    }

    public function getCurrencyId(): string
    {
        return $this->totalAmount->currency();
    }

    public function getCurrencyRate(): float
    {
        return $this->currencyRate;
    }
    public function getVatItems(): array
    {
        return $this->vatItems;
    }

    public function getServiceFromDate(): ?InvoiceDate
    {
        return $this->serviceFromDate;
    }
    public function getServiceToDate(): ?InvoiceDate
    {
        return $this->serviceToDate;
    }
    public function getPaymentDueDate(): ?InvoiceDate
    {
        return $this->paymentDueDate;
    }

    public function getReceiverVatConditionId(): ?int
    {
        return $this->receiverVatConditionId;
    }
}
