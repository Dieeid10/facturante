<?php

use App\Application\DTO\CreateInvoiceRequest;
use App\Domain\ValueObject\MoneyAmount;
use App\Domain\ValueObject\InvoiceDate;

$container = require __DIR__ . '/../src/bootstrap.php';

// Verificar que AFIP esté inicializado
try {
    $container->getAfipService();
} catch (\RuntimeException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Por favor, configura bootstrap.php con tus credenciales de AFIP.\n";
    exit(1);
}

$createInvoiceUseCase = $container->getCreateInvoiceUseCase();
$processInvoiceUseCase = $container->getProcessInvoiceUseCase();
$eventQueue = $container->getEventQueue();

$voucherDate     = InvoiceDate::fromIsoString('2026-01-15');
$serviceFromDate = InvoiceDate::fromIsoString('2026-01-01');
$serviceToDate   = InvoiceDate::fromIsoString('2026-01-31');
$paymentDueDate  = InvoiceDate::fromIsoString('2026-02-10');

$currency = 'ARS';

$netAmount     = MoneyAmount::fromFloat(100.00, $currency);
$vatAmount     = MoneyAmount::fromFloat(21.00, $currency);
$untaxedAmount = MoneyAmount::zero($currency);
$exemptAmount  = MoneyAmount::zero($currency);
$taxAmount     = MoneyAmount::zero($currency);

$totalAmount = $netAmount->add($vatAmount);

try {
    echo "1. Creando 3 facturas y encolándolas...\n";
    
    for ($i = 1; $i <= 3; $i++) {
        // Crear DTO de request usando CreateInvoiceRequest
        $request = new CreateInvoiceRequest(
            pointOfSale: 1,
            voucherType: 1,
            concept: 2,
            documentType: 80,
            documentNumber: 20123456789,
            voucherDate: $voucherDate->toIsoString(),

            totalAmount:   $totalAmount->toFloat(),
            untaxedAmount: $untaxedAmount->toFloat(),
            netAmount:     $netAmount->toFloat(),
            exemptAmount:  $exemptAmount->toFloat(),
            vatAmount:     $vatAmount->toFloat(),
            taxAmount:     $taxAmount->toFloat(),

            currency: $currency,
            currencyRate: 1.0,
            vatItems: [
                [
                    'Id' => 5,
                    'BaseImp' => $netAmount->toFloat(),
                    'Importe' => $vatAmount->toFloat(),
                ]
            ],

            serviceFromDate: $serviceFromDate->toIsoString(),
            serviceToDate:   $serviceToDate->toIsoString(),
            paymentDueDate:  $paymentDueDate->toIsoString(),
            receiverVatConditionId: 1
        );

        $invoice = $createInvoiceUseCase->execute($request);
        echo "   ✓ Factura #{$i} creada (ID: {$invoice->getId()}) y encolada\n";
    }

    echo "\n   Total de eventos en cola: " . $eventQueue->count() . "\n";

    // Procesar todas las facturas de la cola
    echo "2. Procesando todas las facturas de la cola...\n";

    $procesadas = $processInvoiceUseCase->processPendingInvoices();

    echo "   ✓ Facturas procesadas: $procesadas\n";

    // Mostrar resultados
    echo "3. Resultados:\n";

    $repository = $container->getInvoiceRepository();
    
    for ($i = 1; $i <= 3; $i++) {
        $factura = $repository->findById($i);
        if ($factura && $factura->getStatus() === 'creada') {
            echo "   Factura #{$i}:\n";
            echo "     - Número: " . $factura->getVoucherNumber() . "\n";
            echo "     - CAE: " . $factura->getCae() . "\n";
            echo "     - Estado: " . $factura->getStatus() . "\n\n";
        }
    }
    
    echo "   Eventos restantes en cola: " . $eventQueue->count() . "\n";

} catch (\Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    exit(1);
}

echo "\n=== FIN ===\n";

