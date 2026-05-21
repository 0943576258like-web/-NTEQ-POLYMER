<?php
$pageTitle = 'แก้ไขหน้า';
require_once __DIR__ . '/_layout.php';
csrf_check();
$id = (int)($_GET['id'] ?? 0);
$page = null;
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM pages WHERE id=?');
    $stmt->execute([$id]);
    $page = $stmt->fetch();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    if (!$slug) $slug = slugify($title);
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content = $_POST['content'] ?? '';
    $sort = (int)($_POST['sort_order'] ?? 0);
    $show = isset($_POST['show_in_menu']) ? 1 : 0;
    $img = handle_upload('hero_image');
    try {
        if ($page) {
            $sql = 'UPDATE pages SET slug=?,title=?,subtitle=?,content=?,sort_order=?,show_in_menu=?,updated_at=CURRENT_TIMESTAMP';
            $params = [$slug,$title,$subtitle,$content,$sort,$show];
            if ($img) { $sql .= ',hero_image=?'; $params[] = $img; }
            if (!empty($_POST['remove_image'])) { $sql .= ',hero_image=NULL'; }
            $sql .= ' WHERE id=?'; $params[] = $page['id'];
            $pdo->prepare($sql)->execute($params);
        } else {
            $stmt = $pdo->prepare('INSERT INTO pages (slug,title,subtitle,content,sort_order,show_in_menu,hero_image) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$slug,$title,$subtitle,$content,$sort,$show,$img]);
        }
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'บันทึกเรียบร้อย'];
        header('Location: pages.php'); exit;
    } catch (PDOException $e) {
        $err = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    }
}
?>
<h1><?= $page ? 'แก้ไขหน้า' : 'เพิ่มหน้า' ?></h1>
<p class="sub">เนื้อหารองรับ HTML — ใส่รูป ใส่ลิงก์ ปรับแต่งได้</p>
<?php if(!empty($err)): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
<form class="card-panel" method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row-2">
    <div class="form-row"><label>ชื่อหน้า *</label><input type="text" name="title" value="<?= e($page['title'] ?? '') ?>" required></div>
    <div class="form-row"><label>สลัก (URL)</label><input type="text" name="slug" value="<?= e($page['slug'] ?? '') ?>" placeholder="เว้นว่างให้สร้างอัตโนมัติ"><span class="hint">เช่น about, products</span></div>
  </div>
  <div class="form-row"><label>คำโปรย / Subtitle</label><input type="text" name="subtitle" value="<?= e($page['subtitle'] ?? '') ?>"></div>
  <div class="form-row"><label>เนื้อหา (HTML ได้)</label><textarea name="content" style="min-height:300px;font-family:monospace;font-size:13px"><?= e($page['content'] ?? '') ?></textarea></div>
  <div class="form-row">
    <label>ภาพ Hero ของหน้า</label>
    <input type="file" name="hero_image" accept="image/*">
    <?php if(!empty($page['hero_image'])): ?><img src="../<?= e($page['hero_image']) ?>" class="preview-img"><label style="font-size:13px;color:var(--muted);margin-top:6px"><input type="checkbox" name="remove_image" value="1"> ลบภาพ</label><?php endif; ?>
  </div>
  <div class="row-2">
    <div class="form-row"><label>ลำดับ</label><input type="number" name="sort_order" value="<?= (int)($page['sort_order'] ?? 0) ?>"></div>
    <div class="form-row"><label>แสดงในเมนู?</label><label style="display:flex;align-items:center;gap:8px;padding-top:8px"><input type="checkbox" name="show_in_menu" value="1" <?= (!$page || $page['show_in_menu'])?'checked':'' ?>> แสดงในแถบเมนูด้านบน</label></div>
  </div>
  <div style="display:flex;gap:10px">
    <button class="btn" type="submit"><i class="fa-solid fa-check"></i> บันทึก</button>
    <a class="btn ghost" href="pages.php">ยกเลิก</a>
  </div>
</form>
<?php require __DIR__ . '/_layout_end.php'; ?>
