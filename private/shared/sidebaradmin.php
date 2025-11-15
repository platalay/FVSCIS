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
      href="index.php"
    >
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fa fa-ship"></i>
      </div>
      <div class="sidebar-brand-text mx-3">FVSCIS <br> admin</div>
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
          // เทียบชื่อไฟล์โดยตรง
          if ($base === $p) {
            return true;
          }
        }
      }
      return false;
    }

    // ===== โฟลเดอร์ admin ไม่มีเมนูที่ต้องซ่อนสำหรับ role admin =====
    // (กัน error ใน template เดิม ถ้ามีใช้ตัวแปรนี้)
    $canSeeRespMenu = true;
    ?>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= is_active(['index.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="index.php">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>หน้าภาพรวม</span></a
      >
    </li>
    <!-- Nav Item - Charts -->
     <li class="nav-item <?= is_active(['fisherman.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="fisherman.php">
        <i class="fas fa-fw fa-table"></i>
        <span>บัญชีชาวประมง</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['officer.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="officer.php">
        <i class="fas fa-fw fa-table"></i>
        <span>บัญชีเจ้าหน้าที่</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['department.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="department.php">
        <i class="fas fa-fw fa-table"></i>
        <span>บัญชีหน่วยงาน</span></a
      >
    </li>

    <!-- Nav Item - Tables -->
    <li class="nav-item <?= is_active(['departmentgroup.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="departmentgroup.php">
        <i class="fas fa-fw fa-table"></i>
        <span>บัญชีกลุ่มหน่วยงาน</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['inspection_requests.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="inspection_requests.php">
        <i class="fas fa-fw fa-table"></i>
        <span>คำขอรับรองสุขอนามัยเรือ</span></a
      >
    </li>
    <li class="nav-item <?= is_active(['myaccount.php'], $base, $pathLower) ? 'active' : '' ?>">
      <a class="nav-link" href="myaccount.php">
        <i class="fas fa-fw fa-table"></i>
        <span>จัดการบัญชีผู้ใช้</span></a
      >
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block" />

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
  </ul>
  <!-- End of Sidebar -->