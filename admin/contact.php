<?php
$pageTitle = 'ข้อมูลติดต่อ';
require_once __DIR__ . '/_layout.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['contact_phone','contact_fax','contact_email','contact_email2','contact_address','contact_website','contact_map'] as $k) {
        set_setting($pdo, $k, trim($_POST[$k] ?? ''));
    }
    $_SESSION['flash'] = ['type'=>'ok','msg'=>'บันทึกเรียบร้อย'];
    header('Location: contact.php'); exit;
}
?>
<h1>ข้อมูลติดต่อ</h1>
<p class="sub">แสดงในส่วนติดต่อเราของหน้าแรก</p>
<form class="card-panel" method="post">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row-2">
    <div class="form-row"><label>เบอร์โทรศัพท์</label><input type="text" name="contact_phone" value="<?= e(setting($pdo,'contact_phone')) ?>"></div>
    <div class="form-row"><label>แฟกซ์</label><input type="text" name="contact_fax" value="<?= e(setting($pdo,'contact_fax')) ?>"></div>
  </div>
  <div class="row-2">
    <div class="form-row"><label>อีเมลหลัก</label><input type="email" name="contact_email" value="<?= e(setting($pdo,'contact_email')) ?>"></div>
    <div class="form-row"><label>อีเมลรอง (ไม่บังคับ)</label><input type="email" name="contact_email2" value="<?= e(setting($pdo,'contact_email2')) ?>"></div>
  </div>
  <div class="form-row"><label>ที่อยู่ (กด Enter เพื่อขึ้นบรรทัดใหม่)</label><textarea name="contact_address" style="min-height:80px"><?= e(setting($pdo,'contact_address')) ?></textarea></div>
  <div class="form-row"><label>เว็บไซต์</label><input type="text" name="contact_website" value="<?= e(setting($pdo,'contact_website')) ?>"></div>
  <div class="form-row"><label>Google Maps Embed URL (ไม่บังคับ)</label><textarea name="contact_map" style="min-height:80px;font-family:monospace;font-size:13px"><?= e(setting($pdo,'contact_map')) ?></textarea><span class="hint">คัดลอกเฉพาะค่า src="..." จาก Google Maps → Share → Embed a map</span></div>
  <button class="btn" type="submit"><i class="fa-solid fa-check"></i> บันทึก</button>
</form>
<?php require __DIR__ . '/_layout_end.php'; ?>
