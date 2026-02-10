<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$timezones = [];
$now = new DateTime('now', new DateTimeZone('UTC'));

foreach (DateTimeZone::listIdentifiers() as $tzId) {
    $tz = new DateTimeZone($tzId);
    $dt = new DateTime('now', $tz);

    $offsetMinutes = $tz->getOffset($now) / 60;
    $sign = $offsetMinutes >= 0 ? '+' : '-';
    $hours = floor(abs($offsetMinutes) / 60);
    $minutes = abs($offsetMinutes) % 60;
    $offsetLabel = sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);

    // Tentativa de extrair região / cidade
    $parts = explode('/', $tzId);
    $region = $parts[0] ?? '';
    $cityRaw = end($parts);
    $city = str_replace('_', ' ', $cityRaw);

    // País (simplificado — pode evoluir depois)
    $countryMap = [
        'America/Sao_Paulo' => ['Brazil', 'BR', 'BRT'],
        'America/New_York' => ['United States', 'US', 'EST'],
        'Europe/London' => ['United Kingdom', 'GB', 'GMT'],
        'Europe/Berlin' => ['Germany', 'DE', 'CET'],
        'Asia/Tokyo' => ['Japan', 'JP', 'JST']
    ];

    $country = '';
    $countryCode = '';
    $abbr = '';

    if (isset($countryMap[$tzId])) {
        [$country, $countryCode, $abbr] = $countryMap[$tzId];
    }

    $keywords = array_filter([
        strtolower($tzId),
        strtolower($city),
        strtolower($country),
        strtolower($countryCode),
        strtolower($abbr),
        strtolower(str_replace(':', '', $offsetLabel)),
        strtolower($region)
    ]);

    $timezones[] = [
        'id' => $tzId,
        'label' => trim("$city, $country ($offsetLabel)"),
        'region' => $region,
        'city' => $city,
        'country' => $country,
        'country_code' => $countryCode,
        'offset' => $offsetMinutes,
        'offset_label' => $offsetLabel,
        'abbreviation' => $abbr,
        'keywords' => array_values($keywords)
    ];
}

usort($timezones, fn($a, $b) => $a['offset'] <=> $b['offset']);

echo json_encode($timezones);