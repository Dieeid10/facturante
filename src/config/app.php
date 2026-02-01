<?php

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'diego_facturante',
    'version' => $_ENV['APP_VERSION'] ?? '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'desarrollo',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'afip_api_url' => $_ENV['APP_ENV'] == 'producción' ? $_ENV['AFIP_API_URL'] : $_ENV['AFIP_API_DESARROLLO'],
    'access_token' => $_ENV['ACCESS_TOKEN'] ?? null,
];
