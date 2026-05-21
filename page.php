<?php
require_once 'includes/db.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM pages WHERE slug=?');
$stmt->execute([$slug]);
$page = $stmt->fetch();
if (!$page) { http_response_code(404); $pageTitle='ไม่พบหน้า'; include 'includes/header.php'; echo '<div class="prose"><h1>404</h1><p>ไม่พบหน้านี้</p></div>'; include 'includes/footer.php'; exit; }
$pageTitle = $page['title'];
$isContact = $slug === 'contact';
$isNews = $slug === 'news';
include 'includes/header.php';
?>
<div class="page-hero" <?php if($page['hero_image']): ?>style="background:linear-gradient(180deg,rgba(1,4,2,.6),rgba(1,4,2,.95)),url('<?= e($page['hero_image']) ?>') center/cover no-repeat"<?php endif; ?>>
  <h1><?= e($page['title']) ?></h1>
  <?php if($page['subtitle']): ?><p><?= e($page['subtitle']) ?></p><?php endif; ?>
</div>
<div class="prose"><?= $page['content'] ?></div>

<?php if ($isNews):
  $list = $pdo->query('SELECT * FROM articles WHERE published=1 ORDER BY created_at DESC')->fetchAll(); ?>
  <div class="container">
    <div class="card-grid">
      <?php foreach($list as $a): ?>
        <article class="card">
          <div class="card-img" <?php if($a['image']): ?>style="background-image:url('<?= e($a['image']) ?>')"<?php endif; ?>></div>
          <div class="card-body">
            <h3><?= e($a['title']) ?></h3>
            <p><?= e($a['excerpt']) ?></p>
            <a class="read-more" href="article.php?slug=<?= e($a['slug']) ?>">READ MORE <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<?php if ($isContact): ?>
  <div class="container">
    <div class="contact-grid">
      <div class="contact-card"><i class="fa-solid fa-phone"></i><h3>โทรศัพท์</h3><p><?= e(setting($pdo,'contact_phone')) ?></p></div>
      <div class="contact-card"><i class="fa-solid fa-envelope"></i><h3>อีเมล</h3><p><?= e(setting($pdo,'contact_email')) ?></p></div>
      <div class="contact-card"><i class="fa-solid fa-location-dot"></i><h3>ที่อยู่</h3><p><?= e(setting($pdo,'contact_address')) ?></p></div>
    </div>
    <?php if(setting($pdo,'contact_map')): ?>
      <div style="margin-top:30px;border-radius:14px;overflow:hidden;border:1px solid var(--border)"><?= setting($pdo,'contact_map') ?></div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
