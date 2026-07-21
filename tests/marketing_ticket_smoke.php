<?php
require_once __DIR__ . '/../php/marketing_helpers.php';

function assertSameValue($expected, $actual, $label) {
    if ($expected !== $actual) {
        fwrite(STDERR, $label . " failed. Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

assertSameValue('MKT-20260721-0007', cdaMarketingFolio('2026-07-21', 7), 'folio format');
assertSameValue(true, cdaMarketingStatusAllowed('En evaluacion'), 'known status allowed');
assertSameValue(false, cdaMarketingStatusAllowed('Inventado'), 'unknown status rejected');
assertSameValue('Texto con espacios', cdaMarketingClean("  Texto con espacios  "), 'clean trims text');
assertSameValue('Urgente', cdaMarketingPriority('Urgente'), 'known priority kept');
assertSameValue('Normal', cdaMarketingPriority('Fuera'), 'unknown priority defaults');

echo "marketing ticket smoke ok" . PHP_EOL;
