<?php

use App\Application\DTO\CreateInvoiceRequest;
use App\Domain\ValueObject\MoneyAmount;
use App\Domain\ValueObject\InvoiceDate;

$container = require __DIR__ . '/../src/bootstrap.php';

// Verificar que AFIP esté inicializado
try {
    $container->getAfipService();
} catch (\RuntimeException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Por favor, configura bootstrap.php con tus credenciales de AFIP.\n";
    exit(1);
}

$createInvoiceUseCase = $container->getCreateInvoiceUseCase();
$processInvoiceUseCase = $container->getProcessInvoiceUseCase();
$eventQueue = $container->getEventQueue();

echo "=== Crear Factura con Arquitectura Hexagonal ===\n\n";

try {
    // Preparar fechas usando ValueObjects
    $voucherDate = InvoiceDate::fromIsoString(date('Y-m-d'));
    
    // Preparar montos usando ValueObjects
    $currency = 'ARS';
    $netAmount = MoneyAmount::fromFloat(1000.00, $currency);
    $vatAmount = MoneyAmount::fromFloat(210.00, $currency);
    $untaxedAmount = MoneyAmount::zero($currency);
    $exemptAmount = MoneyAmount::zero($currency);
    $taxAmount = MoneyAmount::zero($currency);
    
    $totalAmount = $netAmount->add($vatAmount);

    // Crear DTO de request usando CreateInvoiceRequest
    $request = new CreateInvoiceRequest(
        pointOfSale: 999,
        voucherType: 6,
        concept: 1,
        documentType: 99,
        documentNumber: 0,
        voucherDate: $voucherDate->toIsoString(),

        totalAmount: $totalAmount->toFloat(),
        untaxedAmount: $untaxedAmount->toFloat(),
        netAmount: $netAmount->toFloat(),
        exemptAmount: $exemptAmount->toFloat(),
        vatAmount: $vatAmount->toFloat(),
        taxAmount: $taxAmount->toFloat(),

        currency: $currency,
        currencyRate: 1.00,
        vatItems: [
            [
                'Id' => 5, // 21%
                'BaseImp' => $netAmount->toFloat(),
                'Importe' => $vatAmount->toFloat(),
            ]
        ],

        serviceFromDate: null,
        serviceToDate: null,
        paymentDueDate: null,
        receiverVatConditionId: 4
    );

    echo "1. Creando factura y encolando para procesamiento...\n";
    $invoice = $createInvoiceUseCase->execute($request);
    
    echo "   ✓ Factura creada con ID: " . $invoice->getId() . "\n";
    echo "   Estado: " . $invoice->getStatus() . "\n";
    echo "   Eventos en cola: " . $eventQueue->count() . "\n";

    echo "\n2. Procesando cola de facturas...\n";
    $processed = $processInvoiceUseCase->processPendingInvoices();

    echo "   ✓ Facturas procesadas: $processed\n";

    // Obtener factura actualizada
    $facturaActualizada = $container->getInvoiceRepository()->findById($invoice->getId());

    if ($facturaActualizada && $facturaActualizada->getStatus() === 'creada') {
        echo "\n=== RESULTADO ===\n";
        echo "Número de comprobante: " . $facturaActualizada->getVoucherNumber() . "\n";
        echo "CAE: " . $facturaActualizada->getCae() . "\n";
        echo "Fecha de vencimiento CAE: " . $facturaActualizada->getCaeExpirationDate()?->toIsoString() . "\n";
        echo "Estado: " . $facturaActualizada->getStatus() . "\n";
    }

} catch (\Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    if ($e->getPrevious()) {
        echo "Error anterior: " . $e->getPrevious()->getMessage() . "\n";
    }
    exit(1);
}

echo "\n=== FIN ===\n";

