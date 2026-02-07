<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
$zones = DateTimeZone::listIdentifiers();
$result = [];

foreach ($zones as $zone) {
    $tz = new DateTimeZone($zone);
    $dt = new DateTime('now', $tz);
    $offset = $dt->getOffset() / 3600;
    $sign = $offset >= 0 ? '+' : '-';
    $result[] = [
        'label' => "(GMT{$sign}" . abs($offset) . ") {$zone}",
        'value' => $zone
    ];
}

echo json_encode($result);