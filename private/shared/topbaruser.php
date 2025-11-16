<?php
$uriFull   = $_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '');
$uriPath   = parse_url($uriFull, PHP_URL_PATH);
$uriPath   = is_string($uriPath) ? $uriPath : '';
$base      = strtolower(basename($uriPath));

// กำหนดว่าหน้าไหนให้แสดง search bar
$pagesWithSearch = [];
$showTopSearch = in_array($base, $pagesWithSearch, true);


$pageTitles = [
    'index.php'    => 'หน้าภาพรวม',
    'myvessel.php'   => 'เรือของฉัน',
    'mystatus.php'     => 'สถานะคำขอ',
    'myaccount.php'      => 'จัดการบัญชีผู้ใช้',
    // เติมไฟล์อื่น ๆ ตามจริงของคุณได้เลย
];

// ถ้าไม่ตรงอะไรเลย ใช้ชื่อระบบเป็นค่าเริ่มต้น
$page_title = $pageTitles[$base] ?? 'ระบบสารสนเทศเพื่อการรับรองสุขอนามัยเรือประมง';
?>

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">
  <!-- Main Content -->
  <div id="content">
    <!-- Topbar -->
    <nav
      class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow"
    >
      <!-- Sidebar Toggle (Topbar) -->
      <button
        id="sidebarToggleTop"
        class="btn btn-link d-md-none rounded-circle mr-3"
      >
        <i class="fa fa-bars"></i>
      </button>
      <?php if ($showTopSearch) { ?>
        <!-- Topbar Search (เผื่ออนาคตจะเปิดใช้บางหน้า) -->
        <form
          class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search"
        >
          <div class="input-group">
            <input
              id="topSearch"
              type="text"
              class="form-control bg-light border-0 small"
              placeholder="ค้นหา..."
              aria-label="Search"
              aria-describedby="basic-addon2"
            />
            <div class="input-group-append">
              <button class="btn btn-primary" type="button">
                <i class="fas fa-search fa-sm"></i>
              </button>
            </div>
          </div>
        </form>
      <?php } else { ?>
        <!-- แสดงชื่อหน้าแทน search -->
        <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0">
          <h1 class="h5 mb-0 text-gray-800">
            <?= h($page_title) ?>
          </h1>
        </div>
      <?php } ?>


      <!-- Topbar Navbar -->
      <ul class="navbar-nav ml-auto">
        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        <li class="nav-item dropdown no-arrow d-sm-none">
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="searchDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <i class="fas fa-search fa-fw"></i>
          </a>
          <!-- Dropdown - Messages -->
          <div
            class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
            aria-labelledby="searchDropdown"
          >
            <form class="form-inline mr-auto w-100 navbar-search">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control bg-light border-0 small"
                  placeholder="ค้นหา ..."
                  aria-label="Search"
                  aria-describedby="basic-addon2"
                />
                <div class="input-group-append">
                  <button class="btn btn-primary" type="button">
                    <i class="fas fa-search fa-sm"></i>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </li>

        <!-- Nav Item - Alerts -->
         <!-- 
        <li class="nav-item dropdown no-arrow mx-1">
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="alertsDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <i class="fas fa-bell fa-fw"></i>-->
            <!-- Counter - Alerts -->
          <!--   <span class="badge badge-danger badge-counter">3+</span>
          </a>-->
          <!-- Dropdown - Alerts -->
          <!-- <div
            class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
            aria-labelledby="alertsDropdown"
          >
            <h6 class="dropdown-header">Alerts Center</h6>
            <a class="dropdown-item d-flex align-items-center" href="#">
              <div class="mr-3">
                <div class="icon-circle bg-primary">
                  <i class="fas fa-file-alt text-white"></i>
                </div>
              </div>
              <div>
                <div class="small text-gray-500">December 12, 2019</div>
                <span class="font-weight-bold"
                  >A new monthly report is ready to download!</span
                >
              </div>
            </a>
            <a class="dropdown-item d-flex align-items-center" href="#">
              <div class="mr-3">
                <div class="icon-circle bg-success">
                  <i class="fas fa-donate text-white"></i>
                </div>
              </div>
              <div>
                <div class="small text-gray-500">December 7, 2019</div>
                $290.29 has been deposited into your account!
              </div>
            </a>
            <a class="dropdown-item d-flex align-items-center" href="#">
              <div class="mr-3">
                <div class="icon-circle bg-warning">
                  <i class="fas fa-exclamation-triangle text-white"></i>
                </div>
              </div>
              <div>
                <div class="small text-gray-500">December 2, 2019</div>
                Spending Alert: We've noticed unusually high spending for
                your account.
              </div>
            </a>
            <a
              class="dropdown-item text-center small text-gray-500"
              href="#"
              >Show All Alerts</a
            >
          </div>
        </li>-->
        <!-- Nav Item - Alerts -->
        <!-- Nav Item - Alerts ajax-->
        <li class="nav-item dropdown no-arrow mx-1">
          <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown"
            role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-bell fa-fw"></i>
            <span class="badge badge-danger badge-counter" id="alert-count">0</span>
          </a>

          <div class="dropdown-list dropdown-menu dropdown-menu-end shadow animated--grow-in"
              aria-labelledby="alertsDropdown">
            <h6 class="dropdown-header">แจ้งเตือนล่าสุด</h6>
            <div id="alert-list">
              <!-- AJAX จะโหลดรายการแจ้งเตือนมาที่นี่ -->
              <div class="dropdown-item text-gray-500 small">กำลังโหลด...</div>
            </div>
            <a class="dropdown-item text-center small text-gray-500" href="notifications.php">ดูทั้งหมด</a>
          </div>
        </li>
        <!-- Nav Item - Alerts ajax-->

        
        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
          <a
            class="nav-link dropdown-toggle"
            href="#"
            id="userDropdown"
            role="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
          >
            <span class="mr-2 d-none d-lg-inline text-gray-600 small"
              ><?= htmlspecialchars($session->get_display_name()) ?></span
            >
            <?php
            $default_image = '../img/default-user.svg';
            $picture = $session->user_picture;

            // ถ้าเป็น URL (เริ่มด้วย http หรือ https)
            if (!empty($picture) && preg_match('/^https?:\/\//', $picture)) {
                $profile_image = $picture;
            }
            // ถ้าเป็นไฟล์ในระบบ
            else if (!empty($picture)) {
                $path = '../uploads/profile/' . basename($picture);
                $profile_image = file_exists($path) ? $path : $default_image;
            }
            // ถ้าไม่มีรูปเลย
            else {
                $profile_image = $default_image;
            }
            ?>
            <img
              class="img-profile rounded-circle"
              id = "show_user_picture"
              
              src="<?= $profile_image ?>"
            />
          </a>
          <!-- Dropdown - User Information -->
          <div
            class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
            aria-labelledby="userDropdown"
          >
            <!--<a class="dropdown-item" href="#">
              <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
              Profile
            </a>
            <a class="dropdown-item" href="#">
              <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
              Settings
            </a>
            <a class="dropdown-item" href="#">
              <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
              Activity Log
            </a>
            <div class="dropdown-divider"></div>-->
            <a
              class="dropdown-item"
              href="#"
              data-bs-toggle="modal"
              data-bs-target="#logoutModal"
            >
              <i
                class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"
              ></i>
              ออกจากระบบ
            </a>
          </div>
        </li>
      </ul>
    </nav>
    <!-- End of Topbar -->

    <!-- Begin Page Content -->
    <div class="container-fluid">
      <!-- Page Heading -->
      
    
