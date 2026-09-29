<?php
$env = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$vars = [];
foreach ($env as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2);
    $vars[trim($name)] = trim($value);
}
$url = rtrim($vars['SUPABASE_URL'], '/') . '/rest/v1/';
$key = $vars['SUPABASE_SECRET_KEY'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $key, 'Authorization: Bearer ' . $key, 'Accept: application/openapi+json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
$res = curl_exec($ch);
file_put_contents('schema.json', $res);
echo "Done";
