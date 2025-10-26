<?php
// test_node_call.php - simple PHP script to call node gateway issue endpoint
$data = [
  'certId' => 'CERT-' . strtoupper(bin2hex(random_bytes(4))),
  'issuer' => 'DemoOrg',
  'studentName' => 'Test Student',
  'course' => 'Test Course',
  'issueDate' => date('Y-m-d'),
  'hash' => hash('sha256', 'testdata')
];

$ch = curl_init('http://localhost:4000/api/issue');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
$res = curl_exec($ch);
curl_close($ch);
echo $res;