<?php
/**
 * Generates CurrencyFlagSvgData.php: for each currency, uses a representative country's
 * flag SVG from database/seeders/data/flags/{alpha2}.svg.
 * Run from project root: php database/seeders/data/generate_currency_flag_svg_data.php
 */
$dataDir = __DIR__;
$flagsDir = $dataDir . '/flags';
$countryList = require $dataDir . '/CountryListData.php';

// Build currency_id => alpha_2 (first country that uses this currency)
$currencyToAlpha2 = [];
foreach ($countryList as $row) {
    $cid = $row['currency'] ?? null;
    if ($cid !== null && !isset($currencyToAlpha2[$cid])) {
        $currencyToAlpha2[$cid] = $row['alpha_2'];
    }
}

// currency_id => [code, symbol]; must match CurrencySeeder (used only to get the list of currency ids)
$currencies = [
    36   => ['AUD', 'A$'],   48 => ['BHD', 'BD'],   986 => ['BRL', 'R$'],  124 => ['CAD', 'C$'],
    156  => ['CNY', '¥'],    756 => ['CHF', 'Fr'],   208 => ['DKK', 'kr'],  978 => ['EUR', '€'],
    826  => ['GBP', '£'],    344 => ['HKD', 'HK$'],  356 => ['INR', '₹'],  392 => ['JPY', '¥'],
    400  => ['JOD', 'JD'],   410 => ['KRW', '₩'],   414 => ['KWD', 'KD'],  484 => ['MXN', '$'],
    554  => ['NZD', 'NZ$'],  578 => ['NOK', 'kr'],  634 => ['QAR', 'QR'],  682 => ['SAR', '﷼'],
    752  => ['SEK', 'kr'],   702 => ['SGD', 'S$'],  764 => ['THB', '฿'],   949 => ['TRY', '₺'],
    784  => ['AED', 'د.إ'],  840 => ['USD', '$'],   710 => ['ZAR', 'R'],
];

$out = [];
foreach ($currencies as $currencyId => $info) {
    $alpha2 = $currencyToAlpha2[$currencyId] ?? null;
    if ($alpha2 === null) {
        $out[$currencyId] = null;
        continue;
    }
    $path = $flagsDir . '/' . strtolower($alpha2) . '.svg';
    if (!is_file($path)) {
        $out[$currencyId] = null;
        continue;
    }
    $svg = file_get_contents($path);
    $out[$currencyId] = ($svg !== false && strlen($svg) > 0) ? trim($svg) : null;
}

$lines = ["<?php\n\n/**\n * Currency flag SVG keyed by currency id (from database/seeders/data/flags/*.svg).\n */\n\nreturn [\n"];
foreach ($out as $id => $svg) {
    if ($svg === null) {
        $lines[] = "    {$id} => null,\n";
        continue;
    }
    $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $svg);
    $lines[] = "    {$id} => '{$escaped}',\n";
}
$lines[] = "];\n";
$outPath = $dataDir . '/CurrencyFlagSvgData.php';
file_put_contents($outPath, implode('', $lines));
echo "Written " . count($out) . " currency flag SVGs to {$outPath}\n";
