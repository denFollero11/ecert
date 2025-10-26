<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/certificate.php';
require_once __DIR__ . '/../src/api_client.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $studentName = $_POST['student_name'];
    $studentEmail = $_POST['student_email'];
    $course = $_POST['course'];
    $certId = generateCertId();
    $issueDate = date('Y-m-d');

    $payload = ['certId'=>$certId,'studentName'=>$studentName,'course'=>$course,'issueDate'=>$issueDate,'issuer'=>'Pending'];
    $hash = certHash($payload);

    $html = file_get_contents(__DIR__ . '/templates/certificate-template.html');
    $html = str_replace('{{student_name}}', htmlspecialchars($studentName), $html);
    $html = str_replace('{{course_name}}', htmlspecialchars($course), $html);
    $html = str_replace('{{date_issued}}', htmlspecialchars($issueDate), $html);
    $html = str_replace('{{certificate_id}}', htmlspecialchars($certId), $html);

    $pdfPath = savePDF($html, $certId . '.pdf');
    $verifyUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/verify.php?certId={$certId}";
    $qrPath = generateQR($verifyUrl, $certId . '.png');

    $stmt = $pdo->prepare("INSERT INTO certificates (cert_id, student_name, student_email, course, issued_by, issue_date, pdf_path, qr_path, hash, blockchain_status) VALUES (?,?,?,?,?,?,?,?,?, 'pending')");
    $stmt->execute([$certId, $studentName, $studentEmail, $course, 'Pending', $issueDate, $pdfPath, $qrPath, $hash]);

    $success = "Request submitted. Certificate ID: {$certId}";
}
?>
<!doctype html><html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:900px;">
    <div class="card-body">
      <h4>Request Certificate (Student)</h4>
      <?php if(!empty($success)):?><div class="alert alert-success"><?=$success?></div><?php endif;?>
      <form method="post">
        <input type="hidden" name="action" value="request">
        <div class="row mb-2">
          <div class="col"><input name="student_name" class="form-control" placeholder="Full Name" required></div>
          <div class="col"><input name="student_email" class="form-control" placeholder="Email"></div>
        </div>
        <div class="row mb-2">
          <div class="col"><input name="course" class="form-control" placeholder="Course" required></div>
        </div>
        <button class="btn btn-primary">Submit Request</button>
      </form>
    </div>
  </div>
</div>
</body></html>