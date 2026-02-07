
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
