<!-- Page Wrapper -->
<div id="wrapper">
  <!-- Sidebar -->
  <ul
    class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
    id="accordionSidebar"
  >
    <!-- Sidebar - Brand -->
    <a
      class="sidebar-brand d-flex align-items-center justify-content-center"
      href="index.html"
    >
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fas fa-ship"></i>
      </div>
      <div class="sidebar-brand-text mx-3">FVSCIS <br> ชาวประมง</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0" />

    <?php
    // ===== Helpers: current path + is_active (รองรับ PHP 7.x) =====
    $uriFull   = isset($_SERVER['REQUEST_URI']) 
                ? $_SERVER['REQUEST_URI'] 
                : (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
    $uriPath   = parse_url($uriFull, PHP_URL_PATH);
    $uriPath   = is_string($uriPath) ? $uriPath : '';
    $pathLower = strtolower($uriPath);
    $base      = strtolower(basename($uriPath));

    /**
    * ตรวจว่าเมนูนี้ active ไหม
    * - $patterns: รายชื่อไฟล์ หรือส่วนของ path (ขึ้นต้นด้วย '/')
    */
    function is_active(array $patterns, $base, $pathLower) {
      foreach ($patterns as $p) {
        $p = strtolower($p);

        // ถ้าขึ้นต้นด้วย '/' ให้ match จาก path
        if (strlen($p) > 0 && $p[0] === '/') {
          if (strpos($pathLower, $p) !== false) {
            return true;
          }
        } else {
          // ไม่ขึ้นต้นด้วย '/' ให้เทียบชื่อไฟล์
          if ($base === $p) {
            return true;
          }
        }
      }
      return false;
    }

    // ===== โฟลเดอร์ fisherman ไม่มีเมนูต้องซ่อน =====
    // เผื่อใน template เดิมมีใช้ตัวแปร $canSeeRespMenu อยู่
    // ก็ให้กำหนดให้เป็น true ตลอดไป
    $canSeeRespMenu = true;
    ?>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= is_active(['index.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="index.php">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>หน้าภาพรวม</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['myvessel.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="myvessel.php">
        <i class="fas fa-fw fa-table"></i>
        <span>เรือของฉัน</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['mystatus.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="mystatus.php">
        <i class="fas fa-fw fa-table"></i>
        <span>สถานะคำขอ</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['myaccount.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="myaccount.php">
        <i class="fas fa-fw fa-table"></i>
        <span>จัดการบัญชีผู้ใช้</span></a
      >
    </li>
    

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
  </ul>
  <!-- End of Sidebar -->