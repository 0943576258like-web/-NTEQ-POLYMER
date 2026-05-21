<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/_layout.php';
$pages = $pdo->query('SELECT COUNT(*) c FROM pages')->fetch()['c'];
$articles = $pdo->query('SELECT COUNT(*) c FROM articles')->fetch()['c'];
?>
<h1>ยินดีต้อนรับ 👋</h1>
<p class="sub">จัดการเนื้อหาเว็บไซต์ <?= e(setting($pdo,'site_name')) ?></p>
<div class="card-grid" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
  <div class="card-panel"><div class="sub">เมนู/หน้า</div><div style="font-size:32px;font-weight:700;color:var(--green)"><?= (int)$pages ?></div><a class="read-more" href="pages.php">จัดการ <i class="fa-solid fa-arrow-right"></i></a></div>
  <div class="card-panel"><div class="sub">บทความ/ข่าว</div><div style="font-size:32px;font-weight:700;color:var(--green)"><?= (int)$articles ?></div><a class="read-more" href="articles.php">จัดการ <i class="fa-solid fa-arrow-right"></i></a></div>
  <div class="card-panel"><div class="sub">การเข้าใช้</div><div style="font-size:18px;font-weight:600"><?= e($_SESSION['email']) ?></div><a class="read-more" href="account.php">เปลี่ยนรหัสผ่าน <i class="fa-solid fa-arrow-right"></i></a></div>
</div>
<div class="card-panel">
  <h2 style="font-size:18px;margin-bottom:10px">เริ่มต้นใช้งาน</h2>
  <ul style="color:var(--muted);padding-left:20px;line-height:2">
    <li>แก้ไขข้อความหน้าแรกที่เมนู <strong>หน้าแรก / Hero</strong></li>
    <li>เพิ่มหรือแก้เมนูในส่วน <strong>เมนู / หน้า</strong> — ใช้ HTML ในเนื้อหาได้</li>
    <li>เพิ่มบทความใหม่ในส่วน <strong>ข่าวสาร / บทความ</strong> — แสดงปุ่ม READ MORE อัตโนมัติ</li>
    <li>อัปเดตเบอร์ / อีเมล / ที่อยู่ ที่ <strong>ข้อมูลติดต่อ</strong></li>
  </ul>
</div>
<?php require __DIR__ . '/_layout_end.php'; ?>
