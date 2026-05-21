<?php
$pageTitle = 'แก้ไขบทความ';
require_once __DIR__ . '/_layout.php';
csrf_check();
$id = (int)($_GET['id'] ?? 0);
$a = null;
if ($id) { $s = $pdo->prepare('SELECT * FROM articles WHERE id=?'); $s->execute([$id]); $a = $s->fetch(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? ''); if (!$slug) $slug = slugify($title);
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? 1 : 0;
    $img = handle_upload('image');
    try {
        if ($a) {
            $sql = 'UPDATE articles SET slug=?,title=?,excerpt=?,content=?,published=?,updated_at=CURRENT_TIMESTAMP';
            $params = [$slug,$title,$excerpt,$content,$published];
            if ($img) { $sql .= ',image=?'; $params[] = $img; }
            if (!empty($_POST['remove_image'])) { $sql .= ',image=NULL'; }
            $sql .= ' WHERE id=?'; $params[] = $a['id'];
            $pdo->prepare($sql)->execute($params);
        } else {
            $pdo->prepare('INSERT INTO articles (slug,title,excerpt,content,image,published) VALUES (?,?,?,?,?,?)')
                ->execute([$slug,$title,$excerpt,$content,$img,$published]);
        }
        $_SESSION['flash'] = ['type'=>'ok','msg'=>'บันทึกเรียบร้อย'];
        header('Location: articles.php'); exit;
    } catch (PDOException $e) { $err = $e->getMessage(); }
}
?>
<h1><?= $a ? 'แก้ไขบทความ' : 'เพิ่มบทความ' ?></h1>
<?php if(!empty($err)): ?><div class="flash err"><?= e($err) ?></div><?php endif; ?>
<form class="card-panel" method="post" enctype="multipart/form-data">
  <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
  <div class="row-2">
    <div class="form-row"><label>ชื่อบทความ *</label><input type="text" name="title" value="<?= e($a['title'] ?? '') ?>" required></div>
    <div class="form-row"><label>สลัก (URL)</label><input type="text" name="slug" value="<?= e($a['slug'] ?? '') ?>" placeholder="อัตโนมัติ"></div>
  </div>
  <div class="form-row"><label>เกริ่นนำ (แสดงใต้ชื่อในการ์ด)</label><textarea name="excerpt" style="min-height:70px"><?= e($a['excerpt'] ?? '') ?></textarea></div>
  <div class="form-row"><label>เนื้อหา (HTML ได้)</label><textarea name="content" style="min-height:340px;font-family:monospace;font-size:13px"><?= e($a['content'] ?? '') ?></textarea></div>
  <div class="form-row">
    <label>ภาพหน้าปก</label>
    <input type="file" name="image" accept="image/*">
    <?php if(!empty($a['image'])): ?><img src="../<?= e($a['image']) ?>" class="preview-img"><label style="font-size:13px;color:var(--muted);margin-top:6px"><input type="checkbox" name="remove_image" value="1"> ลบภาพ</label><?php endif; ?>
  </div>
  <div class="form-row"><label><input type="checkbox" name="published" value="1" <?= (!$a || $a['published'])?'checked':'' ?>> เผยแพร่</label></div>
  <div style="display:flex;gap:10px">
    <button class="btn" type="submit"><i class="fa-solid fa-check"></i> บันทึก</button>
    <a class="btn ghost" href="articles.php">ยกเลิก</a>
  </div>
</form>
<?php require __DIR__ . '/_layout_end.php'; ?>
