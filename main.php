<?php

if($argc < 3) {
    echo '[!] Usage: ' . $argv[0] . ' [INPUT SYSLOG] [OUTPUT CSV]' . PHP_EOL;
    exit(-1);
}

if(!file_exists($argv[1])) {
    echo '[!] Error: ' . $argv[1] . ' is not found' . PHP_EOL;
    exit(-1);
}

$log = file_get_contents($argv[1]);
$log = explode("\n", $log);

$csv_header = [];
$csv_header[] = "EventID";
$max_event_count = 25;
for($i=1; $i<=$max_event_count; $i++) {
    $csv_header[] = "Data${i}";
}
$csv_header[] = "Raw";
$csv_header = implode(',', $csv_header);

$csv = $csv_header . "\r\n";

foreach($log as $line) {
    $pos = strpos($line, "<Event>");
    if($pos === false) {
        continue;
    }

    $xml = substr($line, $pos);
    $raw = $xml;
    $xml = simplexml_load_string($xml);
    $xml = json_decode(json_encode($xml), true);

    $tmp = [];

    $tmp[] = $xml['System']['EventID'];
    foreach($xml['EventData']['Data'] as $key => $value) {
        $tmp[] = $value;
    }

    for($i=0; $i<26; $i++) {
        if(!isset($tmp[$i])) {
            $tmp[$i] = "";
        }
    }
    $tmp[26] = $raw;

    $tmp = implode(',', $tmp);
    $csv .= $tmp . "\r\n";
}

file_put_contents($argv[2], $csv);
