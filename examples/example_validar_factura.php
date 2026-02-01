<?php

require __DIR__ . '/src/bootstrap.php';

use Facturante\Infrastructure\ServiceContainer;

$container = require __DIR__ . '/src/bootstrap.php';

// Verificar que AFIP esté inicializado
try {
    $container->getAfipService();
} catch (\RuntimeException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Por favor, configura bootstrap.php con tus credenciales de AFIP.\n";
    exit(1);
}

// Obtener caso de uso
$validarFacturaUseCase = $container->getValidarFacturaUseCase();

echo "=== Validar Factura ===\n\n";

try {
    $voucherNumber = 2;  // Número de comprobante
    $ptoVta = 999;       // Punto de venta
    $cbteTipo = 6;       // Tipo (Factura B)

    echo "Validando factura:\n";
    echo "  - Número: $voucherNumber\n";
    echo "  - Punto de venta: $ptoVta\n";
    echo "  - Tipo: $cbteTipo\n\n";

    $info = $validarFacturaUseCase->execute($voucherNumber, $ptoVta, $cbteTipo);

    echo "=== INFORMACIÓN DE LA FACTURA ===\n";
    var_dump($info);

} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    exit(1);
}

echo "\n=== FIN ===\n";

