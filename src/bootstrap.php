<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\ServiceContainer;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$config = require __DIR__ . '/config/app.php';
$certFile = __DIR__ . '/../cert/certificado_homo.crt';
$keyFile  = __DIR__ . '/../cert/keytest.key';

if (!file_exists($certFile) || !file_exists($keyFile)) {
    throw new RuntimeException(
        "No se encontraron los archivos de certificado:\n" .
        " - {$certFile}\n" .
        " - {$keyFile}"
    );
}

$cert = file_get_contents($certFile);
$key  = file_get_contents($keyFile);

if ($cert === false || $key === false) {
    throw new RuntimeException("No se pudieron leer los certificados AFIP.");
}

$CUIT = $_ENV['CUIT'] ?? null;

if (empty($CUIT)) {
    throw new RuntimeException('La variable de entorno CUIT no está definida.');
}

$container = ServiceContainer::getInstance();

$container->initializeAfip(
    $CUIT,
    (bool) ($config['production'] ?? false),
    $cert,
    $key,
    $config['access_token'] ?? null
);

return $container;
