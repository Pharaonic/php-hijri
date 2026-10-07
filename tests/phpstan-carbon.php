<?php

// Only one of the HijriDifference traits is loaded at runtime (see
// src/Concerns/HijriDifference.php), so analyse the one matching the
// installed Carbon version.

require_once __DIR__ . '/../vendor/autoload.php';

$carbon3 = (new ReflectionMethod(Carbon\Carbon::class, 'diffInYears'))->hasReturnType();

return [
    'parameters' => [
        'excludePaths' => [
            'analyseAndScan' => [
                __DIR__ . '/../src/Concerns/' . ($carbon3 ? 'Carbon2' : 'Carbon3'),
            ],
        ],
    ],
];
