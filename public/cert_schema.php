<?php
// Certificate Schema validator - php-app/modules/cert_schema.php
// Validates that certificate data conforms to the standard schema (JSON-LD or simple JSON structure).

function validate_certificate_schema($data) {
    // Simple required fields check
    $required = ['certId','studentName','course','issuer','issueDate','hash'];
    foreach ($required as $r) {
        if (!isset($data[$r]) || $data[$r] === '') return false;
    }
    return true;
}