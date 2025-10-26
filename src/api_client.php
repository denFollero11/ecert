<?php
require_once __DIR__.'/config.php';

function callNode($endpoint, $method='POST', $data = null) {
    $url = rtrim(NODE_GATEWAY_URL, '/') . '/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $res = curl_exec($ch);
    if ($res === false) {
        throw new Exception('Node gateway error: '.curl_error($ch));
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return json_decode($res, true);
}