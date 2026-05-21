<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$current = basename($_SERVER['PHP_SELF'], '.php');
?><!DOCTYPE html>
<html lang="th"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — <?= e($pageTitle ?? 'Dashboard') ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../assets/style.css"></head>
<body><div class="admin-shell">
<aside class="admin-side">
  <a class="brand" href="index.php"><i class="fa-solid fa-leaf"></i> NTEQ Admin</a>
  <a href="index.php" class="<?= $current==='index'?'active':'' ?>"><i class="fa-solid fa-gauge"></i> แดชบอร์ด</a>
  <a href="hero.php" class="<?= $current==='hero'?'active':'' ?>"><i class="fa-solid fa-house"></i> หน้าแรก / Hero</a>
  <a href="pages.php" class="<?= in_array($current,['pages','page_edit'])?'active':'' ?>"><i class="fa-solid fa-file-lines"></i> เมนู / หน้า</a>
  <a href="articles.php" class="<?= in_array($current,['articles','article_edit'])?'active':'' ?>"><i class="fa-solid fa-newspaper"></i> ข่าวสาร / บทความ</a>
  <a href="contact.php" class="<?= $current==='contact'?'active':'' ?>"><i class="fa-solid fa-address-card"></i> ข้อมูลติดต่อ</a>
  <a href="account.php" class="<?= $current==='account'?'active':'' ?>"><i class="fa-solid fa-user-gear"></i> บัญชีผู้ดูแล</a>
  <a href="../index.php" target="_blank"><i class="fa-solid fa-eye"></i> ดูเว็บไซต์</a>
  <a href="logout.php" style="margin-top:24px;color:#e57368"><i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ</a>
</aside>
<main class="admin-main">
<?php if(!empty($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="flash <?= $f['type']==='err'?'err':'ok' ?>"><?= e($f['msg']) ?></div>
<?php endif; ?>
