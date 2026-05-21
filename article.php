<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM articles WHERE slug=? AND published=1');
$stmt->execute([$slug]);
$a = $stmt->fetch();
if (!$a) { http_response_code(404); $pageTitle='ไม่พบบทความ'; include 'includes/header.php'; echo '<div class="prose"><h1>404</h1></div>'; include 'includes/footer.php'; exit; }
$pageTitle = $a['title'];
include 'includes/header.php';
?>
<div class="page-hero"><h1><?= e($a['title']) ?></h1><p><?= e(date('d M Y', strtotime($a['created_at']))) ?></p></div>
<?php if($a['image']): ?><div class="container"><div class="article-hero" style="background-image:url('<?= e($a['image']) ?>')"></div></div><?php endif; ?>
<div class="prose">
  <?php if($a['excerpt']): ?><p style="font-size:18px;color:var(--muted)"><?= e($a['excerpt']) ?></p><?php endif; ?>
  <?= $a['content'] ?>
  <p style="margin-top:40px"><a class="btn ghost" href="page.php?slug=news"><i class="fa-solid fa-arrow-left"></i> กลับไปหน้าข่าว</a></p>
</div>
<?php include 'includes/footer.php'; ?>
