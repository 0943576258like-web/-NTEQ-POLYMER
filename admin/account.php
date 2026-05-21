<?php
$pageTitle = 'บัญชีผู้ดูแล';
require_once __DIR__ . '/_layout.php';
csrf_check();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $cur   = $_POST['current_password'] ?? '';
    $new   = $_POST['new_password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
    $stmt->execute([$_SESSION['uid']]);
    $u = $stmt->fetch();
    if (!password_verify($cur, $u['password_hash'])) {
        $err = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
    } else {
        $sql = 'UPDATE users SET email=?'; $params = [$email];
        if (strlen($new) >= 6) { $sql .= ',password_hash=?'; $params[] = password_hash($new, PASSWORD_DEFAULT); }
        elseif ($new !== '') { $err = 'รหัสผ่านใหม่ต้องอย่างน้อย 6 ตัว'; }
        if (empty($err)) {
            $sql .= ' WHERE id=?'; $params[] = $u['id'];
            $pdo->prepare($sql)->execute($params);
            $_SESSION['email'] = $email;
            $_SESSION['flash'] = ['type'=>'ok','msg'=>'อัปเดตบัญชีเรียบร้อย'];
            header('Location: account.php'); exit;
        }
    }
}
$stmt = $pdo->prepare('SELECT email FROM users WHERE id=?'); $stmt->execute([$_SESSION['uid']]); $u = $stmt->fetch();
?>
<h1>บัญชีผู้ดูแล</h1>
<?php if(!empty($err)): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
<form class="card-panel" method="post">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="form-row"><label>อีเมล</label><input type="email" name="email" value="<?= e($u['email']) ?>" required></div>
  <div class="form-row"><label>รหัสผ่านปัจจุบัน *</label><input type="password" name="current_password" required></div>
  <div class="form-row"><label>รหัสผ่านใหม่ (อย่างน้อย 6 ตัว — เว้นว่างถ้าไม่เปลี่ยน)</label><input type="password" name="new_password"></div>
  <button class="btn" type="submit"><i class="fa-solid fa-check"></i> บันทึก</button>
</form>
<?php require __DIR__ . '/_layout_end.php'; ?>
