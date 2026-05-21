<?php
$pageTitle = 'เมนู / หน้า';
require_once __DIR__ . '/_layout.php';
csrf_check();
if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM pages WHERE id=?');
    $stmt->execute([(int)$_GET['id']]);
    $_SESSION['flash'] = ['type'=>'ok','msg'=>'ลบเรียบร้อย'];
    header('Location: pages.php'); exit;
}
$pages = $pdo->query('SELECT * FROM pages ORDER BY sort_order, id')->fetchAll();
?>
<div class="toolbar">
  <div><h1>เมนู / หน้า</h1><p class="sub">หน้าที่แสดงในแถบเมนูด้านบน</p></div>
  <a class="btn" href="page_edit.php"><i class="fa-solid fa-plus"></i> เพิ่มหน้า</a>
</div>
<table class="table">
  <thead><tr><th>ลำดับ</th><th>ชื่อหน้า</th><th>สลัก</th><th>เมนู</th><th></th></tr></thead>
  <tbody>
  <?php foreach($pages as $p): ?>
    <tr>
      <td><?= (int)$p['sort_order'] ?></td>
      <td><strong><?= e($p['title']) ?></strong><br><span class="sub" style="font-size:12px"><?= e($p['subtitle']) ?></span></td>
      <td><code><?= e($p['slug']) ?></code></td>
      <td><?= $p['show_in_menu'] ? '<span class="badge">แสดง</span>' : '<span class="badge off">ซ่อน</span>' ?></td>
      <td class="actions">
        <a class="btn-sm" href="page_edit.php?id=<?= (int)$p['id'] ?>"><i class="fa-solid fa-pen"></i> แก้ไข</a>
        <a class="btn-sm" href="../page.php?slug=<?= e($p['slug']) ?>" target="_blank"><i class="fa-solid fa-eye"></i></a>
        <a class="btn-sm del" href="pages.php?action=delete&id=<?= (int)$p['id'] ?>" onclick="return confirm('ลบหน้านี้?')"><i class="fa-solid fa-trash"></i></a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_layout_end.php'; ?>
