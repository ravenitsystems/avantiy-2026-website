<?php
/**
 * One-off script to generate CountryFlagSvgData.php by fetching SVGs from flagcdn.com.
 * Run from project root: php database/seeders/data/generate_flag_svg_data.php
 */
$countryData = require __DIR__ . '/CountryListData.php';
$outPath = __DIR__ . '/CountryFlagSvgData.php';

$baseUrl = 'https://flagcdn.com/%s.svg';
$map = [];
$failed = [];

foreach ($countryData as $row) {
    $alpha2 = $row['alpha_2'];
    $key = strtoupper($alpha2);
    $url = sprintf($baseUrl, strtolower($alpha2));
    $svg = @file_get_contents($url);
    if ($svg === false || strlen($svg) < 50) {
        $failed[] = $key;
        $map[$key] = null;
        continue;
    }
    $svg = trim($svg);
    $map[$key] = $svg;
}

$php = "<?php\n\n/**\n * Country flag SVG markup keyed by ISO 3166-1 alpha-2 (uppercase).\n * Generated from flagcdn.com. Use null for missing/failed fetches.\n */\n\nreturn [\n";
foreach ($map as $key => $svg) {
    if ($svg === null) {
        $php .= "    '{$key}' => null,\n";
        continue;
    }
    $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $svg);
    $php .= "    '{$key}' => '{$escaped}',\n";
}
$php .= "];\n";
file_put_contents($outPath, $php);
echo "Written " . count($map) . " entries (" . (count($map) - count($failed)) . " with SVG) to {$outPath}\n";
if (!empty($failed)) {
    echo "Missing/failed: " . implode(', ', array_slice($failed, 0, 20)) . (count($failed) > 20 ? ' ...' : '') . "\n";
}
