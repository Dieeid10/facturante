<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Application\DTO\InvoiceCreatedResponse;
use App\Application\Port\AfipServiceInterface;
use App\Domain\Entity\Invoice;
use App\Domain\ValueObject\InvoiceDate;
use RuntimeException;

final class AfipServiceAdapter implements AfipServiceInterface
{
    private object $afip;

    /**
     * Mapeo de códigos ISO de moneda a códigos AFIP
     */
    private const CURRENCY_MAP = [
        'ARS' => 'PES',  // Peso Argentino
        'USD' => 'DOL',  // Dólar Estadounidense
        'EUR' => 'EUR',  // Euro
    ];

    public function __construct(object $afip)
    {
        $this->afip = $afip;
    }

    /**
     * Convierte un código ISO de moneda al código que espera AFIP
     */
    private function mapCurrencyToAfip(string $isoCode): string
    {
        return self::CURRENCY_MAP[$isoCode] ?? $isoCode;
    }

    /**
     * Convierte una entidad Invoice a un array compatible con AFIP
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    public function toAfipArray(Invoice $invoice): array
    {
        $array = $this->buildBaseArray($invoice);
        $array = $this->addVatItems($array, $invoice);
        $array = $this->addServiceDates($array, $invoice);
        
        return $array;
    }

    /**
     * Construye el array base con los campos comunes de la factura
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    private function buildBaseArray(Invoice $invoice): array
    {
        return [
            'PtoVta'     => $invoice->getPointOfSale(),
            'CbteTipo'   => $invoice->getVoucherType(),
            'Concepto'   => $invoice->getConcept(),
            'DocTipo'    => $invoice->getDocumentType(),
            'DocNro'     => $invoice->getDocumentNumber(),
            'CondicionIVAReceptorId' => $invoice->getReceiverVatConditionId(),
            'CbteFch'    => $invoice->getVoucherDate()->toAfipFormat(),
            'ImpTotal'   => $invoice->getTotalAmount()->toFloat(),
            'ImpTotConc' => $invoice->getUntaxedAmount()->toFloat(),
            'ImpNeto'    => $invoice->getNetAmount()->toFloat(),
            'ImpOpEx'    => $invoice->getExemptAmount()->toFloat(),
            'ImpIVA'     => $invoice->getVatAmount()->toFloat(),
            'ImpTrib'    => $invoice->getTaxAmount()->toFloat(),
            'MonId'      => $this->mapCurrencyToAfip($invoice->getCurrencyId()),
            'MonCotiz'   => $invoice->getCurrencyRate()
        ];
    }

    /**
     * Agrega los ítems de IVA al array si existen
     * @param array<string, mixed> $array
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    private function addVatItems(array $array, Invoice $invoice): array
    {
        $vatItems = $this->extractVatItems($invoice);
        if (!empty($vatItems)) {
            $array['Iva'] = $vatItems;
        }
        return $array;
    }

    /**
     * Extrae los ítems de IVA de la factura
     * @param Invoice $invoice
     * @return array<int, array<string, mixed>>
     */
    private function extractVatItems(Invoice $invoice): array
    {
        $vatItemsArray = [];
        foreach ($invoice->getVatItems() as $item) {
            if (is_array($item)) {
                $vatItemsArray[] = $item;
            }
        }
        return $vatItemsArray;
    }

    /**
     * Agrega las fechas de servicio al array si es necesario
     * @param array<string, mixed> $array
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    private function addServiceDates(array $array, Invoice $invoice): array
    {
        if (!$this->requiresServiceDates($invoice)) {
            return $array;
        }
        
        return array_merge($array, $this->buildServiceDatesArray($invoice));
    }

    /**
     * Determina si la factura requiere fechas de servicio
     * @param Invoice $invoice
     * @return bool
     */
    private function requiresServiceDates(Invoice $invoice): bool
    {
        return in_array($invoice->getConcept(), [2, 3], true);
    }

    /**
     * Construye el array de fechas de servicio
     * @param Invoice $invoice
     * @return array<string, string>
     */
    private function buildServiceDatesArray(Invoice $invoice): array
    {
        $dates = [];
        
        if ($invoice->getServiceFromDate()) {
            $dates['FchServDesde'] = $invoice->getServiceFromDate()->toAfipFormat();
        }
        
        if ($invoice->getServiceToDate()) {
            $dates['FchServHasta'] = $invoice->getServiceToDate()->toAfipFormat();
        }
        
        if ($invoice->getPaymentDueDate()) {
            $dates['FchVtoPago'] = $invoice->getPaymentDueDate()->toAfipFormat();
        }
        
        return $dates;
    }

    /**
     * Crea una factura en AFIP
     * @param Invoice $invoice
     * @return InvoiceCreatedResponse
     * @throws RuntimeException
     */
    public function createInvoice(Invoice $invoice): InvoiceCreatedResponse
    {
        try {
            $payload = $this->toAfipArray($invoice);
            $response = $this->afip->ElectronicBilling->CreateNextVoucher($payload);
            return InvoiceCreatedResponse::fromAfipArray($response);
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Error comunicándose con AFIP: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Obtiene la información de un comprobante en AFIP
     *
     * @param int $voucherNumber
     * @param int $pointOfSale
     * @param int $voucherType
     * @return array{
     *   CAE: string,
     *   CAEFchVto: string,
     *   CbteDesde: int,
     *   Resultado: string
     * }
     * @throws RuntimeException
     */
    public function getInformationInvoice(
        int $voucherNumber,
        int $pointOfSale,
        int $voucherType
    ): array {
        try {
            $response = $this->afip->ElectronicBilling
                ->getVoucherInfo($voucherNumber, $pointOfSale, $voucherType);

            return $response['ResultGet'] ?? [];
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Error obteniendo información de comprobante AFIP',
                0,
                $e
            );
        }
    }
}
