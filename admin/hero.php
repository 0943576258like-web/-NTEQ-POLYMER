<?php
$pageTitle = 'Hero';
require_once __DIR__ . '/_layout.php';
csrf_check();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['site_name','hero_title','hero_subtitle','hero_cta_text','hero_cta_link','about_text'] as $k) {
        set_setting($pdo, $k, trim($_POST[$k] ?? ''));
    }
    $img = handle_upload('hero_image');
    if ($img) set_setting($pdo, 'hero_image', $img);
    if (!empty($_POST['remove_hero'])) set_setting($pdo, 'hero_image', '');
    $_SESSION['flash'] = ['type'=>'ok','msg'=>'บันทึกเรียบร้อย'];
    header('Location: hero.php'); exit;
}
$heroImg = setting($pdo,'hero_image');
?>
<h1>หน้าแรก / Hero</h1>
<p class="sub">ข้อความ โลโก้ และปุ่ม CTA บนหน้าแรก</p>
<form class="card-panel" method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="form-row"><label>ชื่อเว็บไซต์ / แบรนด์</label><input type="text" name="site_name" value="<?= e(setting($pdo,'site_name')) ?>" required></div>
  <div class="form-row"><label>หัวข้อใหญ่ (Hero Title) — กด Enter เพื่อขึ้นบรรทัดใหม่</label><textarea name="hero_title" style="min-height:80px" required><?= e(setting($pdo,'hero_title')) ?></textarea></div>
  <div class="form-row"><label>คำโปรย (Subtitle)</label><textarea name="hero_subtitle" style="min-height:80px"><?= e(setting($pdo,'hero_subtitle')) ?></textarea></div>
  <div class="row-2">
    <div class="form-row"><label>ข้อความปุ่ม CTA</label><input type="text" name="hero_cta_text" value="<?= e(setting($pdo,'hero_cta_text')) ?>"></div>
    <div class="form-row"><label>ลิงก์ปุ่ม CTA</label><input type="text" name="hero_cta_link" value="<?= e(setting($pdo,'hero_cta_link')) ?>"></div>
  </div>
  <div class="form-row">
    <label>ภาพพื้นหลัง Hero (ไม่บังคับ)</label>
    <input type="file" name="hero_image" accept="image/*">
    <?php if($heroImg): ?>
      <img src="../<?= e($heroImg) ?>" class="preview-img">
      <label style="font-size:13px;color:var(--muted);margin-top:6px"><input type="checkbox" name="remove_hero" value="1"> ลบภาพพื้นหลัง</label>
    <?php endif; ?>
  </div>
  <div class="form-row"><label>ข้อความ "เกี่ยวกับเรา" บนหน้าแรก</label><textarea name="about_text"><?= e(setting($pdo,'about_text')) ?></textarea></div>
  <button class="btn" type="submit"><i class="fa-solid fa-check"></i> บันทึก</button>
</form>
<?php require __DIR__ . '/_layout_end.php'; ?>
