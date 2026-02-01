<?php

require __DIR__ . '/src/bootstrap.php';

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

echo "=== Ejemplo de Factura Electrónica de Prueba ===\n\n";

try {
    // ============================================
    // 1. VERIFICAR ESTADO DEL SERVIDOR
    // ============================================
    echo "1. Verificando estado del servidor AFIP...\n";
    $status = $afip->ElectronicBilling->GetServerStatus();
    echo "   - AppServer: " . $status->AppServer . "\n";
    echo "   - DbServer: " . $status->DbServer . "\n";
    echo "   - AuthServer: " . $status->AuthServer . "\n";
    echo "   ✓ Servidor disponible\n\n";

    // ============================================
    // 2. OBTENER PUNTOS DE VENTA DISPONIBLES
    // ============================================
    echo "2. Obteniendo puntos de venta disponibles...\n";
    /* $salesPoints = $afip->ElectronicBilling->GetSalesPoints();
    
    if (empty($salesPoints)) {
        die("Error: No hay puntos de venta disponibles. Debes configurar al menos uno en AFIP.\n");
    } */
    
    // Usar el primer punto de venta disponible
    $ptoVta = 999;
    echo "   - Punto de venta seleccionado: $ptoVta\n\n";

    // ============================================
    // 3. OBTENER TIPOS DE COMPROBANTE
    // ============================================
    echo "3. Obteniendo tipos de comprobante...\n";
    $voucherTypes = $afip->ElectronicBilling->GetVoucherTypes();
    
    $cbteTipo = 6;
    
    // Verificar que el tipo existe
    $tipoExiste = false;
    $tiposArray = is_array($voucherTypes) ? $voucherTypes : [$voucherTypes];
    foreach ($tiposArray as $tipo) {
        if ($tipo->Id == $cbteTipo) {
            $tipoExiste = true;
            echo "   - Tipo de comprobante: {$tipo->Desc} (ID: {$tipo->Id})\n";
            break;
        }
    }
    
    if (!$tipoExiste) {
        $cbteTipo = $tiposArray[0]->Id;
        echo "   - Tipo de comprobante seleccionado: {$tiposArray[0]->Desc} (ID: $cbteTipo)\n";
    }
    echo "\n";

    // ============================================
    // 3.5. OBTENER CONDICIONES IVA DEL RECEPTOR
    // ============================================
    echo "3.5. Obteniendo condiciones IVA del receptor...\n";
    $condicionesIva = $afip->ElectronicBilling->GetCondicionIvaReceptor();
    $condicionesArray = is_array($condicionesIva) ? $condicionesIva : [$condicionesIva];
    
    $ivaReceptorId = null;
    foreach ($condicionesArray as $cond) {
        if (stripos($cond->Desc, 'Consumidor Final') !== false || 
            stripos($cond->Desc, 'No Responsable') !== false ||
            $cond->Id == 5) {
            $ivaReceptorId = $cond->Id;
            echo "   - Condición IVA seleccionada: {$cond->Desc} (ID: {$cond->Id})\n";
            break;
        }
    }
    
    if ($ivaReceptorId === null && !empty($condicionesArray)) {
        $ivaReceptorId = $condicionesArray[0]->Id;
        echo "   - Condición IVA seleccionada: {$condicionesArray[0]->Desc} (ID: $ivaReceptorId)\n";
    }
    
    if ($ivaReceptorId === null) {
        die("Error: No se pudieron obtener las condiciones IVA del receptor.\n");
    }
    echo "\n";

    // ============================================
    // 4. OBTENER ÚLTIMO COMPROBANTE AUTORIZADO
    // ============================================
    echo "4. Obteniendo último comprobante autorizado...\n";
    try {
        $lastVoucher = $afip->ElectronicBilling->GetLastVoucher($ptoVta, $cbteTipo);
        $nextNumber = $lastVoucher + 1;
        echo "   - Último comprobante: $lastVoucher\n";
        echo "   - Próximo número: $nextNumber\n\n";
    } catch (Exception $e) {
        // Si no hay comprobantes previos, empezar desde 1
        $nextNumber = 1;
        echo "   - No hay comprobantes previos, empezando desde: $nextNumber\n\n";
    }

    // ============================================
    // 5. PREPARAR DATOS DE LA FACTURA
    // ============================================
    echo "5. Preparando datos de la factura...\n";
    
    // Fecha del comprobante (formato: YYYYMMDD)
    $fechaComprobante = date('Ymd');
    
    // Datos del cliente (en testing puedes usar datos de prueba)
    $docTipo = 96;
    $docNro = 12345678;
    
    // Concepto: 1 = Productos, 2 = Servicios, 3 = Productos y Servicios
    $concepto = 1;
    
    // Importes (en testing, usar valores pequeños)
    $impNeto = 1000.00; // Importe neto gravado
    $impIva = 210.00;   // IVA 21% (21% de 1000 = 210)
    // ImpTotal debe ser igual a: ImpTotConc + ImpNeto + ImpOpEx + ImpTrib + ImpIVA
    // 0 + 1000.00 + 0 + 0 + 210.00 = 1210.00
    $impTotal = 1210.00; // Total
    
    // Moneda: Peso Argentino
    $monId = 'PES';
    $monCotiz = 1; // Cotización 1 para pesos
    
    // Alícuota de IVA
    $ivaTipo = 5; // 5 = 21% (verificar con GetAliquotTypes si es necesario)
    
   $docTipo = 96; // 96 = DNI, 99 = Consumidor Final
   $docNro = 12345678; // Número de documento (en testing puede ser cualquier número)
    // Forzar Consumidor Final para la prueba
   $docTipo = 99; // 99 = Consumidor Final
   $docNro = 0;   // Documento 0 para consumidor final en homologacion
    
    $data = array(
        'PtoVta' => $ptoVta,
        'CbteTipo' => $cbteTipo,
        'Concepto' => $concepto,
        'DocTipo' => $docTipo,
        'DocNro' => $docNro,
        'CondicionIVAReceptorId' => $ivaReceptorId, // Campo obligatorio según RG 5616
        'CbteFch' => $fechaComprobante,
        'ImpTotal' => $impTotal,
        'ImpTotConc' => 0, // Importe neto no gravado
        'ImpNeto' => $impNeto,
        'ImpOpEx' => 0, // Importe exento
        'ImpIVA' => $impIva,
        'ImpTrib' => 0, // Importe de tributos
        'MonId' => $monId,
        'MonCotiz' => $monCotiz,
        'Iva' => array(
            array(
                'Id' => $ivaTipo,
                'BaseImp' => $impNeto, // Base imponible (debe coincidir con ImpNeto)
                'Importe' => $impIva   // Importe de IVA (debe coincidir con ImpIVA)
            )
        )
    );
    
    // FchServDesde, FchServHasta y FchVtoPago solo son obligatorios si Concepto es 2 (Servicios) o 3 (Productos y Servicios)
    if ($concepto == 2 || $concepto == 3) {
        $data['FchServDesde'] = $fechaComprobante; // Fecha inicio servicio
        $data['FchServHasta'] = $fechaComprobante; // Fecha fin servicio
        $data['FchVtoPago'] = $fechaComprobante;   // Fecha vencimiento pago
    }
    
    echo "   - Fecha: " . date('Y-m-d') . "\n";
    echo "   - Cliente: Doc Tipo $docTipo, Nro $docNro\n";
    echo "   - Condición IVA Receptor: $ivaReceptorId\n";
    echo "   - Importe Neto: $" . number_format($impNeto, 2, ',', '.') . "\n";
    echo "   - IVA: $" . number_format($impIva, 2, ',', '.') . "\n";
    echo "   - Total: $" . number_format($impTotal, 2, ',', '.') . "\n\n";

    // ============================================
    // 6. CREAR LA FACTURA
    // ============================================
    echo "6. Creando factura electrónica...\n";
    
    // Usar CreateNextVoucher que automáticamente obtiene el siguiente número
    $result = $afip->ElectronicBilling->CreateNextVoucher($data);
    
    echo "   ✓ Factura creada exitosamente!\n\n";
    
    // ============================================
    // 7. MOSTRAR RESULTADO
    // ============================================
    echo "=== RESULTADO ===\n";
    echo "Número de comprobante: " . $result['voucher_number'] . "\n";
    echo "CAE (Código de Autorización Electrónico): " . $result['CAE'] . "\n";
    echo "Fecha de vencimiento CAE: " . $result['CAEFchVto'] . "\n";
    echo "\n";
    echo "✓ La factura ha sido autorizada por AFIP.\n";
    echo "  Este es un comprobante de PRUEBA en ambiente de homologación.\n";
    echo "  NO es una factura real y NO tiene validez fiscal.\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    
    // Mostrar más detalles si hay errores de OpenSSL
    while ($err = openssl_error_string()) {
        echo "OpenSSL: $err\n";
    }
    
    exit(1);
}

echo "\n=== FIN DEL EJEMPLO ===\n";