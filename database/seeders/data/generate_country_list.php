<?php
/**
 * One-off script to generate CountryListData.php from country-codes.csv.
 * Run from project root: php database/seeders/data/generate_country_list.php
 */
$csvPath = __DIR__ . '/country-codes.csv';
$outPath = __DIR__ . '/CountryListData.php';

$currencyIds = [36, 48, 124, 156, 208, 344, 356, 392, 400, 410, 414, 484, 554, 578, 634, 682, 702, 710, 752, 756, 784, 826, 840, 949, 986, 978];
$priorityOrder = [840 => 1, 826 => 2, 124 => 3, 36 => 4, 372 => 5, 276 => 10, 250 => 11, 380 => 12, 724 => 13, 528 => 14, 56 => 15, 40 => 16, 300 => 17, 620 => 18, 246 => 19, 196 => 20, 233 => 21, 440 => 22, 442 => 23, 428 => 24, 470 => 25, 705 => 26, 703 => 27, 756 => 30, 208 => 31, 752 => 32, 578 => 33, 392 => 40, 156 => 41, 344 => 42, 702 => 43, 410 => 44, 356 => 45, 554 => 46, 764 => 47, 484 => 50, 76 => 51, 792 => 52, 784 => 53, 682 => 54, 634 => 55, 414 => 56, 48 => 57, 400 => 58, 710 => 60];

$fp = fopen($csvPath, 'r');
$header = fgetcsv($fp);
$dialIdx = array_search('Dial', $header);
$numIdx = array_search('ISO3166-1-numeric', $header);
$a2Idx = array_search('ISO3166-1-Alpha-2', $header);
$a3Idx = array_search('ISO3166-1-Alpha-3', $header);
$nameIdx = array_search('UNTERM English Short', $header);
$currIdx = array_search('ISO4217-currency_numeric_code', $header);

if ($dialIdx === false || $numIdx === false || $a2Idx === false || $nameIdx === false) {
    fclose($fp);
    throw new \RuntimeException('Missing required CSV columns');
}

$countries = [];
while (($row = fgetcsv($fp)) !== false) {
    if (count($row) < max($dialIdx, $numIdx, $a2Idx, $nameIdx) + 1) continue;
    $alpha2 = trim($row[$a2Idx] ?? '');
    $numeric = trim($row[$numIdx] ?? '');
    if ($alpha2 === '' || $numeric === '' || !ctype_digit($numeric)) continue;
    $id = (int) $numeric;
    $name = trim($row[$nameIdx] ?? '');
    if ($name === '') $name = $alpha2;
    $alpha3 = strtoupper(trim($row[$a3Idx] ?? ''));
    $dial = trim($row[$dialIdx] ?? '');
    $dialCode = '+';
    if ($dial !== '') {
        $digits = preg_replace('/[^0-9]/', '', $dial);
        // E.164 country codes are 1–3 digits; allow up to 4 for NANP area codes like +1809
        $dialCode .= substr($digits, 0, 4);
        if ($dialCode === '+') $dialCode = '+0';
    } else {
        $dialCode = '+0';
    }
    $currRaw = trim($row[$currIdx] ?? '');
    $currency = (ctype_digit($currRaw) && in_array((int)$currRaw, $currencyIds)) ? (int) $currRaw : null;
    $orderIndex = $priorityOrder[$id] ?? 100;
    $countries[] = [
        'id' => $id,
        'name' => $name,
        'alpha_2' => strtoupper($alpha2),
        'alpha_3' => $alpha3,
        'dial_code' => $dialCode,
        'order_index' => $orderIndex,
        'enabled' => true,
        'currency' => $currency,
        'currency_override' => null,
    ];
}
fclose($fp);

usort($countries, function ($a, $b) {
    if ($a['order_index'] !== $b['order_index']) return $a['order_index'] - $b['order_index'];
    return strcasecmp($a['name'], $b['name']);
});

$php = "<?php\n\nreturn [\n";
foreach ($countries as $c) {
    $name = addcslashes($c['name'], "'\\");
    $php .= "    ['id' => {$c['id']}, 'name' => '{$name}', 'alpha_2' => '{$c['alpha_2']}', 'alpha_3' => '{$c['alpha_3']}', 'dial_code' => '{$c['dial_code']}', 'order_index' => {$c['order_index']}, 'enabled' => true, 'currency' => " . ($c['currency'] === null ? 'null' : $c['currency']) . ", 'currency_override' => null],\n";
}
$php .= "];\n";
file_put_contents($outPath, $php);
echo "Written " . count($countries) . " countries to {$outPath}\n";
