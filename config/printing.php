<?php

return [
    // Simular impresión en desarrollo (no requiere hardware)
    'simulate' => env('PRINTING_SIMULATE', env('APP_ENV') !== 'production'),

    // Guardar tickets simulados en storage/app/simulated-prints/
    'save_simulated' => env('PRINTING_SAVE_SIMULATED', true),

    // Timeout para conexiones de impresoras (en segundos)
    'connection_timeout' => env('PRINTING_TIMEOUT', 5),
];
