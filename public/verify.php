<?php
require_once __DIR__.'/../src/config.php';
require_once __DIR__.'/../src/api_client.php';

$certId = $_GET['certId'] ?? null;
$verifyResult = null;
if ($certId) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE cert_id = ? LIMIT 1");
    $stmt->execute([$certId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        try {
            $res = callNode('verify', 'POST', ['certId' => $certId, 'hash' => $row['hash']]);
            $verifyResult = $res;
        } catch (Exception $e) {
            $verifyResult = ['success'=>false,'message'=>'Node gateway error: '.$e->getMessage()];
        }
    } else {
        $verifyResult = ['success'=>false,'message'=>'Certificate not found in DB'];
    }
}
?>
<!doctype html><html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<div class="container py-5">
  <div class="card mx-auto" style="max-width:800px;">
    <div class="card-body">
      <h4>Verify Certificate</h4>
      <form method="get" class="mb-3">
        <div class="input-group">
          <input class="form-control" placeholder="Enter Certificate ID or scan QR (paste URL)" name="certId" value="<?=htmlspecialchars($certId)?>">
          <button class="btn btn-primary">Check</button>
        </div>
      </form>

      <?php if($verifyResult): ?>
        <?php if(isset($verifyResult['success']) && $verifyResult['success'] && isset($verifyResult['result'])): ?>
          <?php $r = $verifyResult['result']; ?>
          <div class="alert alert-<?=($r['valid'] ? 'success':'danger')?>">
            <?= $r['valid'] ? 'Valid certificate' : 'Invalid certificate' ?>
          </div>
          <pre><?=htmlspecialchars(json_encode($r['cert'], JSON_PRETTY_PRINT))?></pre>
        <?php else: ?>
          <div class="alert alert-warning"><?=htmlspecialchars($verifyResult['message'] ?? 'No response')?></div>
        <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</div>
</body></html>