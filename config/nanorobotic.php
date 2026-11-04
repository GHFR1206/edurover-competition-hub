<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telemetry ingestion
    |--------------------------------------------------------------------------
    | When disabled (default), the EduRover telemetry endpoint rejects all
    | ingest attempts. Telemetry never contributes to official scores.
    */
    'telemetry_enabled' => (bool) env('FEATURE_TELEMETRY', false),

    /*
    |--------------------------------------------------------------------------
    | Results visibility
    |--------------------------------------------------------------------------
    */
    'public_results_require_publication' => true,
];
