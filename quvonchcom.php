<?php
$name = $_GET['name'];

$url = 'https://quvonch.com/index.php?do=search';
$headers = array(
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
    'Accept-Encoding: gzip, deflate, br',
    'Accept-Language: ru,en;q=0.9,en-GB;q=0.8,en-US;q=0.7',
    'Cache-Control: max-age=0',
    'Content-Type: application/x-www-form-urlencoded',
    'Origin: https://quvonch.com',
    'Referer: https://quvonch.com/index.php?do=search&story=' . $name,
    'Sec-Ch-Ua: "Microsoft Edge";v="117", "Not;A=Brand";v="8", "Chromium";v="117"',
    'Sec-Ch-Ua-Mobile: ?0',
    'Sec-Ch-Ua-Platform: "Windows"',
    'Sec-Fetch-Dest: document',
    'Sec-Fetch-Mode: navigate',
    'Sec-Fetch-Site: same-origin',
    'Sec-Fetch-User: ?1',
    'Upgrade-Insecure-Requests: 1',
    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36 Edg/117.0.2045.36'
);

$postfields = array(
    'do' => 'search',
    'subaction' => 'search',
    'search_start' => 0,
    'full_search' => 0,
    'result_from' => 1,
    'story' => $name
);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo 'cURL Error: ' . curl_error($ch);
} else {
    preg_match_all('/<div class="play-btn" href="(.*?)"><\/div> <a href="(.*?)">(.*?)<\/a>/', $response, $matches, PREG_SET_ORDER);
    preg_match_all('/<span class="duration">(.*?)<\/span>/', $response, $dmatch);
    $count = 0;
    foreach ($matches as $key => $match) {
        $playBtnUrl = $match[1];
        $aHref = $match[2];
        $title = $match[3];
        $duration = $dmatch[1][$key];
        $js[] = ['title' => $title, 'duration' => $duration, 'url' => "https:" . $playBtnUrl];
        $count++;
    }
    preg_match_all('/<a onclick="javascript[^>]*>(\d+)<\/a>/', $response, $pages);
    $pages_count = count($pages[1]) + 1;
}

if ($count > 0) {
    if ($pages_count > 1) {
        for ($i = 2; $i <= $pages_count; $i++) {
            $postfields = array(
                'do' => 'search',
                'subaction' => 'search',
                'search_start' => $i,
                'full_search' => 0,
                'result_from' => $i * 25 - 24,
                'story' => $name
            );

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate, br');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            if ($response === false) {
                echo 'cURL Error: ' . curl_error($ch);
            } else {
                preg_match_all('/<div class="play-btn" href="(.*?)"><\/div> <a href="(.*?)">(.*?)<\/a>/', $response, $matches, PREG_SET_ORDER);
                preg_match_all('/<span class="duration">(.*?)<\/span>/', $response, $dmatch);
                foreach ($matches as $key => $match) {
                    $playBtnUrl = $match[1];
                    $aHref = $match[2];
                    $title = $match[3];
                    $duration = $dmatch[1][$key];
                    $js[] = ['title' => $title, 'duration' => $duration, 'url' => "https:" . $playBtnUrl];
                    $count++;
                }
            }
        }
        $js = array(
            'ok' => 'true',
            'result' => $js,
            'pages_count' => $pages_count,
            'music_count'=>$count
        );
    } else {
        $js = array(
            'ok' => 'true',
            'result' => $js,
            'pages_count' => $pages_count,
            'music_count'=>$count
        );
    }
} else {
    $js = array(
        'ok' => 'false'
    );
}

echo json_encode($js, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE);
curl_close($ch);
