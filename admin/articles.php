<?php
$pageTitle = 'ข่าวสาร / บทความ';
require_once __DIR__ . '/_layout.php';
csrf_check();
if (($_GET['action'] ?? '') === 'delete' && !empty($_GET['id'])) {
    $pdo->prepare('DELETE FROM articles WHERE id=?')->execute([(int)$_GET['id']]);
    $_SESSION['flash'] = ['type'=>'ok','msg'=>'ลบเรียบร้อย'];
    header('Location: articles.php'); exit;
}
$list = $pdo->query('SELECT * FROM articles ORDER BY created_at DESC')->fetchAll();
?>
<div class="toolbar">
  <div><h1>ข่าวสาร / บทความ</h1><p class="sub">แสดงบนหน้าแรก และในหน้าข่าวสาร พร้อมปุ่ม READ MORE</p></div>
  <a class="btn" href="article_edit.php"><i class="fa-solid fa-plus"></i> เพิ่มบทความ</a>
</div>
<table class="table">
  <thead><tr><th>ชื่อ</th><th>สลัก</th><th>สถานะ</th><th>วันที่</th><th></th></tr></thead>
  <tbody>
  <?php foreach($list as $a): ?>
    <tr>
      <td><strong><?= e($a['title']) ?></strong><br><span class="sub" style="font-size:12px"><?= e(mb_substr($a['excerpt'],0,80)) ?></span></td>
      <td><code><?= e($a['slug']) ?></code></td>
      <td><?= $a['published']?'<span class="badge">เผยแพร่</span>':'<span class="badge off">ร่าง</span>' ?></td>
      <td><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
      <td class="actions">
        <a class="btn-sm" href="article_edit.php?id=<?= (int)$a['id'] ?>"><i class="fa-solid fa-pen"></i></a>
        <a class="btn-sm" href="../article.php?slug=<?= e($a['slug']) ?>" target="_blank"><i class="fa-solid fa-eye"></i></a>
        <a class="btn-sm del" href="articles.php?action=delete&id=<?= (int)$a['id'] ?>" onclick="return confirm('ลบบทความนี้?')"><i class="fa-solid fa-trash"></i></a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require __DIR__ . '/_layout_end.php'; ?>
