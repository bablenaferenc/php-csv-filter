<?php

if ($argc != 4) {
    echo 'Ambiguous number of parameters!';
    exit(1);
}


$dokumentumok = array();
$d = array();

$row = 1;
if (($handle = fopen('document_list.csv', 'r')) !== false) {
    while (($data = fgetcsv($handle, null, ';')) !== false) {
        if ($row == 1) {
            $d = $data;
        } else {
            $o = [];
            for ($i = 0; $i < count($d); $i++) {
                $value = json_decode($data[$i]);
                if (json_last_error() == 0) {
                    $o[$d[$i]] = $value;
                } else {
                    $o[$d[$i]] = $data[$i];

                }
            }
            $dokumentumok[] = $o;
        }
        $row++;
    }
    fclose($handle);
}

function is_empty($obj) {
    return empty((array) $obj);
}

$dokumentumok = array_filter($dokumentumok, function ($item) {
    global $argv;

    $p = (array)$item['partner'];
    $partner = (!empty($p['id']) && $p['id'] == $argv[2]);
    $type = $item['document_type'] == $argv[1];

    return $partner && $type ;
});

$d2 = array('document_id', 'document_type','partner name', 'total');

foreach ($d2 as $h) {
    echo str_pad($h, 20);
}
echo "\n";
foreach ($d2 as $h) {
    echo str_repeat('=', 20);
}
echo "\n";
foreach ($dokumentumok as $item) {
    $total = 0;
    $itemCounter = 0;
    do {
        $total += $item['items'][$itemCounter]->unit_price * $item['items'][$itemCounter]->quantity;
        $itemCounter++;
    } while($itemCounter < count($item['items']));

    if ($total > $argv[3]) {

        echo str_pad($item['id'], 20);
        echo str_pad($item['document_type'], 20);
        echo str_pad($item['partner']->name, 20);
        echo str_pad($total, 20);
        echo "\n";
    }
}