<?php
require_once __DIR__ . '/../includes/auth.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email=?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($pass, $u['password_hash'])) {
        $_SESSION['uid'] = $u['id'];
        $_SESSION['email'] = $u['email'];
        header('Location: index.php'); exit;
    }
    $err = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}
?><!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css"></head>
<body class="login-page">
<form class="login-card" method="post">
  <h1>NTEQ Admin</h1>
  <p class="sub">เข้าสู่ระบบเพื่อจัดการเนื้อหา</p>
  <?php if($err): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
  <div class="form-row"><label>อีเมล</label><input type="email" name="email" required autofocus value="admin@nteq.local"></div>
  <div class="form-row"><label>รหัสผ่าน</label><input type="password" name="password" required></div>
  <button class="btn" style="width:100%;justify-content:center" type="submit">เข้าสู่ระบบ</button>
  <p class="sub" style="margin-top:18px;font-size:12px">ค่าเริ่มต้น: admin@nteq.local / admin1234<br>(เปลี่ยนรหัสผ่านในเมนูบัญชี)</p>
</form>
</body></html>
