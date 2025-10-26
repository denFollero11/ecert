<?php
require_once __DIR__ . '/../src/config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>E-Certificate System</title>
  <link href="/assets/css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card mx-auto" style="max-width:900px;">
      <div class="card-body">
        <h2 class="card-title">E-Certificate System</h2>
        <p class="lead">Issue, verify and audit certificates backed by Hyperledger Fabric.</p>
        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="/login.php">Login</a>
          <a class="btn btn-outline-secondary" href="/student.php">Request Certificate (Student)</a>
          <a class="btn btn-outline-success" href="/verify.php">Verify Certificate</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>