<?php
//header('Content-Type: application/json');
$data = [
    'url' => $_GET['url']
];
$header = array();
$header[] = 'origin: https://www.getfvid.com';
$header[] = 'referer: https://www.getfvid.com/';
$header[] = 'user-agent: Mozilla/5.0 (Windows NT 10.0; WIn64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chromw/97.0.4692.99 Safari/537.36';
$ch = curl_init("https://www.getfvid.com/downloader");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$res = curl_exec($ch);
preg_match_all('/(<a href="(.+)" target="_blank" class)/', $res, $response);
foreach ($response[2] as $a) {
    $json[] = ['url' => $a];
}
$js = array(
    'ok' => true,
    'telegram'=>"@BRaimqulov",
    'result' => [
        'facebook' => $json,
    ]
);

echo json_encode($js, JSON_PRETTY_PRINT);
curl_close($ch);
