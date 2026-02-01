<?php

require __DIR__ . '/bootstrap.php';

$afipOptions = array(
    'CUIT' => $CUIT,
    'production' => FALSE,
    'cert' => $cert,
    'key' => $key
);

// Agregar access_token si está configurado
if (!empty($config['access_token'])) {
    $afipOptions['access_token'] = $config['access_token'];
}

$afip = new Afip($afipOptions);

$info = $afip->ElectronicBilling->GetVoucherInfo(
    2, // Número de comprobante
    999,  // Punto de venta
    6   // Tipo (Factura B)
);

var_dump($info);