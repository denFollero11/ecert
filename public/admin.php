<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/certificate.php';
require_once __DIR__ . '/../src/api_client.php';

requireRole('admin');

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'issue') {
    $studentName = $_POST['student_name'];
    $studentEmail = $_POST['student_email'];
    $course = $_POST['course'];
    $issueDate = $_POST['issue_date'] ?: date('Y-m-d');
    $issuer = $_SESSION['user']['username'];
    $certId = generateCertId();

    $payload = [
        'certId' => $certId,
        'studentName' => $studentName,
        'course' => $course,
        'issueDate' => $issueDate,
        'issuer' => $issuer
    ];
    $hash = certHash($payload);

    $html = file_get_contents(__DIR__ . '/templates/certificate-template.html');
    $html = str_replace('{{student_name}}', htmlspecialchars($studentName), $html);
    $html = str_replace('{{course_name}}', htmlspecialchars($course), $html);
    $html = str_replace('{{date_issued}}', htmlspecialchars($issueDate), $html);
    $html = str_replace('{{certificate_id}}', htmlspecialchars($certId), $html);

    $pdfPath = savePDF($html, $certId . '.pdf');

    $verifyUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . "/verify.php?certId={$certId}";
    $qrPath = generateQR($verifyUrl, $certId . '.png');

    $stmt = $pdo->prepare("INSERT INTO certificates (cert_id, student_name, student_email, course, issued_by, issue_date, pdf_path, qr_path, hash) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$certId, $studentName, $studentEmail, $course, $issuer, $issueDate, $pdfPath, $qrPath, $hash]);

    try {
        $nodeRes = callNode('issue', 'POST', [
            'certId' => $certId,
            'issuer' => $issuer,
            'studentName' => $studentName,
            'course' => $course,
            'issueDate' => $issueDate,
            'hash' => $hash
        ]);
        if ($nodeRes['success']) {
            $txId = is_array($nodeRes['result']) && isset($nodeRes['result']['txId']) ? $nodeRes['result']['txId'] : json_encode($nodeRes['result']);
            $pdo->prepare("UPDATE certificates SET tx_id=?, blockchain_status='confirmed' WHERE cert_id=?")
                ->execute([$txId, $certId]);
        }
    } catch (Exception $e) {
    }

    $success = "Certificate issued: {$certId}";
}

$certs = $pdo->query("SELECT * FROM certificates ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Admin Dashboard</h3>
    <div>
      <a href="/index.php" class="btn btn-outline-secondary">Home</a>
      <a href="/logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>

  <?php if(!empty($success)):?><div class="alert alert-success"><?=$success?></div><?php endif;?>

  <div class="card mb-3">
    <div class="card-body">
      <h5>Create / Issue Certificate</h5>
      <form method="post">
        <input type="hidden" name="action" value="issue">
        <div class="row">
          <div class="col-md-6 mb-2"><input name="student_name" class="form-control" placeholder="Student Full Name" required></div>
          <div class="col-md-6 mb-2"><input name="student_email" class="form-control" placeholder="Student Email"></div>
        </div>
        <div class="row">
          <div class="col-md-6 mb-2"><input name="course" class="form-control" placeholder="Course/Program" required></div>
          <div class="col-md-3 mb-2"><input name="issue_date" type="date" class="form-control"></div>
          <div class="col-md-3 mb-2"><button class="btn btn-primary w-100">Issue Certificate</button></div>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5>Certificates</h5>
      <table class="table">
        <thead><tr><th>Cert ID</th><th>Student</th><th>Course</th><th>Issued</th><th>Blockchain</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($certs as $c): ?>
          <tr>
            <td><?=htmlspecialchars($c['cert_id'])?></td>
            <td><?=htmlspecialchars($c['student_name'])?></td>
            <td><?=htmlspecialchars($c['course'])?></td>
            <td><?=htmlspecialchars($c['issue_date'])?></td>
            <td><?=htmlspecialchars($c['blockchain_status'])?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="<?=$c['pdf_path']?>" target="_blank">View PDF</a>
              <a class="btn btn-sm btn-outline-secondary" href="<?=$c['qr_path']?>" target="_blank">QR</a>
              <a class="btn btn-sm btn-outline-info" href="/verify.php?certId=<?=$c['cert_id']?>" target="_blank">Verify</a>
            </td>
          </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body></html>