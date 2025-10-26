<?php
require_once __DIR__.'/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Dompdf\Dompdf;

function generateCertId() {
    return 'CERT-' . strtoupper(bin2hex(random_bytes(6)));
}

function certHash($data) {
    ksort($data);
    return hash('sha256', json_encode($data));
}

function savePDF($html, $filename) {
    $pdf = new Dompdf();
    $pdf->loadHtml($html);
    $pdf->setPaper('A4', 'landscape');
    $pdf->render();
    $path = __DIR__ . '/../public/uploads/' . $filename;
    file_put_contents($path, $pdf->output());
    return '/uploads/' . $filename;
}

function generateQR($text, $filename) {
    $writer = new PngWriter();
    $qr = QrCode::create($text)->setSize(300);
    $result = $writer->write($qr);
    $path = __DIR__ . '/../public/uploads/' . $filename;
    $result->saveToFile($path);
    return '/uploads/' . $filename;
}