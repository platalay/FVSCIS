<!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
  <!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

  <!-- Sidebar - Brand -->
  <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.html">
    <div class="sidebar-brand-icon rotate-n-15">
      <i class="fas fa-ship"></i>
    </div>
    <div class="sidebar-brand-text mx-3">FVSCIS <br> ผู้อนุมัติ</div>
  </a>

  <hr class="sidebar-divider my-0" />

  <?php
// ===== Helpers: current path + is_active (รองรับ PHP 7.x) =====
$uriFull = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$uriPath = parse_url($uriFull, PHP_URL_PATH);
$uriPath = is_string($uriPath) ? $uriPath : '';
$pathLower = strtolower($uriPath);
$base = strtolower(basename($uriPath));

/**
 * ตรวจว่าเมนูนี้ active ไหม
 * - $patterns: รายชื่อไฟล์ หรือส่วนของ path (ขึ้นต้นด้วย '/')
 */
function is_active(array $patterns, $base, $pathLower) {
  foreach ($patterns as $p) {
    $p = strtolower($p);
    if (strlen($p) > 0 && $p[0] === '/') {
      if (strpos($pathLower, $p) !== false) return true; // match ด้วยส่วน path
    } else {
      if ($base === $p) return true;                     // match ชื่อไฟล์ตรงๆ
    }
  }
  return false;
}

// ===== ตรวจว่าเราอยู่ในโฟลเดอร์ /signer/ หรือ subfolder ของมันไหม =====
$issigner = (preg_match('~/signer(/|$)~i', $uriPath) === 1);

// ===== กำหนด whitelist =====
// หมายเหตุ: ถ้า whitelist ใช้ "departments_id" ให้ตั้งตัวแปรนี้ตามความหมายจริงเพื่อกันสับสน:
$allowedDepartmentIds = [2,3,4,5,6,7,8,9];

// ===== หาค่า officerId ที่ใช้เทียบ whitelist =====
// พยายามใช้ departments_id ก่อน → ถ้าไม่มี ค่อยใช้ id → ค่อย fallback session
$officerId = null;
if (isset($Officer)) {
  if (isset($Officer->departments_id) && $Officer->departments_id !== '') {
    $officerId = (int)$Officer->departments_id;
  } elseif (isset($Officer->id) && $Officer->id !== '') {
    $officerId = (int)$Officer->id;
  }
}
if ($officerId === null && isset($_SESSION['officer_id'])) {
  $officerId = (int)$_SESSION['officer_id'];
}

// เงื่อนไขการแสดงเมนู "ข้อมูลที่รับผิดชอบ"
$canSeeRespMenu = $issigner && $officerId !== null && in_array($officerId, $allowedDepartmentIds, true);
?>

<!-- Nav Item - Dashboard -->
<li class="nav-item <?= is_active(['index.php','index.html'], $base, $pathLower) ? 'active' : '' ?>">
  <a class="nav-link" href="index.php">
    <i class="fas fa-fw fa-tachometer-alt"></i>
    <span>หน้าภาพรวม</span>
  </a>
</li>

<!-- Nav Item - Incoming Requests -->
<li class="nav-item <?= is_active(['incoming_requests.php'], $base, $pathLower) ? 'active' : '' ?>">
  <a class="nav-link" href="incoming_requests.php">
    <i class="fas fa-fw fa-inbox"></i>
    <span>รออนุมัติ</span>
  </a>
</li>
<!-- Nav Item - Incoming Requests -->
<li class="nav-item <?= is_active(['inspection_requests.php'], $base, $pathLower) ? 'active' : '' ?>">
  <a class="nav-link" href="inspection_requests.php">
    <i class="fas fa-fw fa-inbox"></i>
    <span>คำขอทั้งหมดในสังกัด</span>
  </a>
</li>
<!-- Nav Item - Old Certification -->
<li class="nav-item <?= is_active(['old_certification.php'], $base, $pathLower) ? 'active' : '' ?>">
  <a class="nav-link" href="old_certification.php">
    <i class="fas fa-fw fa-file-alt"></i>
    <span>ข้อมูลการอนุมัติ</span>
  </a>
</li>

<?php if ($canSeeRespMenu): ?>
  <!-- Nav Item - ข้อมูลที่รับผิดชอบ (เฉพาะ officer/dept id ใน whitelist) -->
  <li class="nav-item <?= is_active(['all_old_certification.php'], $base, $pathLower) ? 'active' : '' ?>">
    <a class="nav-link" href="all_old_certification.php">
      <i class="fas fa-fw fa-clipboard-check"></i>
      <span>ข้อมูลที่รับผิดชอบ</span>
    </a>
  </li>
<?php endif; ?>

<li class="nav-item <?= is_active(['myaccount.php'], $base, $pathLower) ? 'active' : '' ?>">
  <a class="nav-link" href="myaccount.php">
    <i class="fas fa-fw fa-file-alt"></i>
    <span>จัดการบัญชีผู้ใช้</span>
  </a>
</li>

  <!-- Sidebar Toggler -->
  <div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
  </div>

</ul>
<!-- End of Sidebar -->