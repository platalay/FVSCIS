<?php
require_once('../private/initialize.php');

$gearOptions = [];
$certificateStatusOptions = [];
foreach (['gear_type', 'certificate_status'] as $field) {
    $options = [];
    $result = $database->query("SELECT DISTINCT `{$field}` FROM fv_sanitation_certification_old WHERE `{$field}` IS NOT NULL AND TRIM(`{$field}`) <> '' ORDER BY `{$field}` ASC");
    if ($result) {
        while ($record = $result->fetch_assoc()) {
            $options[] = trim((string)$record[$field]);
        }
        $result->free();
    }
    if ($field === 'gear_type') {
        $gearOptions = $options;
    } else {
        $certificateStatusOptions = $options;
    }
}

$searchState = [];
foreach (['vessel_name', 'ship_code', 'vessel_mark', 'gear_type', 'certificate_number', 'expiration_date', 'certificate_status'] as $field) {
    $searchState[$field] = trim((string)($_GET[$field] ?? ''));
}
$updatedAt = thai_date(date('Y-m-d H:i:s'), ['format' => 'long', 'show_time' => true]);

include('../private/shared/headeruser.php');
?>
<link rel="stylesheet" href="css/sb-admin-2.min.css">
<link rel="stylesheet" href="vendor/datatables/dataTables.bootstrap4.min.css">
<div id="wrapper">
  <div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
      <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
        <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2 my-md-0">
          <h1 class="h5 mb-0 text-gray-800">ค้นหาใบรับรองสุขอนามัยเรือประมง</h1>
        </div>
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="btn btn-primary btn-sm" href="login.php">
              <i class="fas fa-sign-in-alt fa-sm mr-1" aria-hidden="true"></i> เข้าสู่ระบบ / Login
            </a>
          </li>
        </ul>
      </nav>

      <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
          <div>
            <h2 class="h3 mb-1 text-gray-800">ค้นหาใบรับรองสุขอนามัยเรือประมง</h2>
            <div class="small text-gray-600">อัพเดทข้อมูลเมื่อ <?= h($updatedAt) ?> (เวลาที่หน้านี้อ่านข้อมูลจากฐานข้อมูล)</div>
          </div>
          <div class="small text-gray-600 mt-2 mt-md-0">กรอกข้อมูลและกดปุ่มเอ็นเทอร์ (Enter)</div>
        </div>

        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-search mr-1" aria-hidden="true"></i> ค้นหา</h6>
          </div>
          <div class="card-body">
            <form id="certificateSearchForm" method="get" action="index.php">
              <div class="form-row align-items-end">
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="vessel_name">ชื่อเรือประมง</label>
                  <input type="text" class="form-control" id="vessel_name" name="vessel_name" value="<?= h($searchState['vessel_name']) ?>">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="ship_code">ทะเบียนเรือ</label>
                  <input type="text" class="form-control" id="ship_code" name="ship_code" value="<?= h($searchState['ship_code']) ?>">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="vessel_mark">เครื่องหมายประจำเรือ</label>
                  <input type="text" class="form-control" id="vessel_mark" name="vessel_mark" value="<?= h($searchState['vessel_mark']) ?>">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="gear_type">ชนิดเครื่องมือทำการประมง</label>
                  <select class="custom-select" id="gear_type" name="gear_type">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($gearOptions as $option): ?><option value="<?= h($option) ?>" <?= $searchState['gear_type'] === $option ? 'selected' : '' ?>><?= h($option) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="certificate_number">เลขที่หนังสือรับรอง</label>
                  <input type="text" class="form-control" id="certificate_number" name="certificate_number" value="<?= h($searchState['certificate_number']) ?>">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="expiration_date">วันหมดอายุ</label>
                  <input type="date" class="form-control" id="expiration_date" name="expiration_date" value="<?= h($searchState['expiration_date']) ?>">
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <label for="certificate_status">สถานะหนังสือรับรอง</label>
                  <select class="custom-select" id="certificate_status" name="certificate_status">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($certificateStatusOptions as $option): ?><option value="<?= h($option) ?>" <?= $searchState['certificate_status'] === $option ? 'selected' : '' ?>><?= h($option) ?></option><?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group col-xl-3 col-lg-4 col-md-6">
                  <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-search mr-1" aria-hidden="true"></i> ค้นหา</button>
                </div>
              </div>
            </form>
          </div>
        </div>

        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">รายการใบรับรอง</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                  <tr style="font-size: 14px;">
                    <th>ชื่อเรือประมง</th>
                    <th>ทะเบียนเรือ</th>
                    <th>เครื่องหมายประจำเรือ</th>
                    <th>ชนิดเครื่องมือทำการประมง</th>
                    <th>สถานะ</th>
                    <th>เลขที่หนังสือรับรอง</th>
                    <th>วันที่บังคับใช้</th>
                    <th>วันหมดอายุ</th>
                    <th>สถานะหนังสือรับรอง</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include('../private/shared/footeruser.php'); ?>
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/datatables/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script>
(function ($) {
  'use strict';

  const form = $('#certificateSearchForm');
  const fieldNames = ['vessel_name', 'ship_code', 'vessel_mark', 'gear_type', 'certificate_number', 'expiration_date', 'certificate_status'];
  const initialSearch = <?= json_encode($searchState, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function escapeHtml(value) {
    return $('<div>').text(value == null || value === '' ? '-' : value).html();
  }

  function statusBadge(value) {
    const labels = {
      active: ['เปิดใช้งาน', 'success'],
      inactive: ['ไม่ใช้งาน', 'secondary'],
      fail: ['ไม่ผ่าน', 'danger'],
      pending: ['รอดำเนินการ', 'warning'],
      pass: ['ผ่าน', 'info']
    };
    const key = String(value || '').toLowerCase();
    const item = labels[key];
    if (!item) return '<span class="badge badge-light">' + escapeHtml(value) + '</span>';
    return '<span class="badge badge-' + item[1] + '">' + item[0] + '</span>';
  }

  function thaiDate(value) {
    if (!value || value === '0000-00-00') return '-';
    const parts = String(value).split('-');
    if (parts.length !== 3) return escapeHtml(value);
    const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    const month = parseInt(parts[1], 10);
    if (!months[month - 1]) return escapeHtml(value);
    return escapeHtml(parseInt(parts[2], 10) + ' ' + months[month - 1] + ' ' + (parseInt(parts[0], 10) + 543));
  }

  function currentFilters() {
    const filters = {};
    fieldNames.forEach(function (name) { filters[name] = $('#' + name).val() || ''; });
    return filters;
  }

  function updateUrl() {
    const params = new URLSearchParams();
    const filters = currentFilters();
    Object.keys(filters).forEach(function (name) { if (filters[name]) params.set(name, filters[name]); });
    const query = params.toString();
    window.history.replaceState({}, '', 'index.php' + (query ? '?' + query : ''));
  }

  const table = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    searching: false,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
    order: [],
    ajax: {
      url: 'ajax/public_certificates.php',
      type: 'GET',
      data: function (request) {
        return $.extend({}, request, currentFilters());
      }
    },
    language: {
      processing: 'กำลังโหลด...',
      lengthMenu: 'แสดง _MENU_ รายการ',
      zeroRecords: 'ไม่พบข้อมูลตามเงื่อนไขที่ค้นหา',
      info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
      infoEmpty: 'แสดง 0 ถึง 0 จาก 0 รายการ',
      paginate: { previous: 'ก่อนหน้า', next: 'ถัดไป' }
    },
    columns: [
      { data: 'vessel_name', defaultContent: '-', render: escapeHtml },
      { data: 'ship_code', defaultContent: '-', render: escapeHtml },
      { data: 'vessel_mark', defaultContent: '-', render: escapeHtml },
      { data: 'gear_type', defaultContent: '-', render: escapeHtml },
      { data: 'status', defaultContent: '-', render: statusBadge },
      { data: 'certificate_number', defaultContent: '-', render: escapeHtml },
      { data: 'effective_date', defaultContent: '-', render: thaiDate },
      { data: 'expiration_date', defaultContent: '-', render: thaiDate },
      { data: 'certificate_status', defaultContent: '-', render: escapeHtml }
    ]
  });

  form.on('submit', function () {
    updateUrl();
    table.ajax.reload();
    return false;
  });

  fieldNames.forEach(function (name) {
    if (initialSearch[name]) $('#' + name).val(initialSearch[name]);
  });
})(jQuery);
</script>
