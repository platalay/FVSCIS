<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
$request_id = $request->id;
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านน้ำจืดที่ใช้ในเรือและน้ำแข็งสำหรับเก็บรักษาสัตว์น้ำ (water and ice)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<!-- ข้อ 1 -->
  <div class="accordion-item">
  <h2 class="accordion-header" id="heading4_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_1"
            aria-expanded="false" aria-controls="collapse4_1">
      4.1 น้ำจืด และน้ำแข็งที่ใช้สำหรับเก็บรักษาสัตว์น้ำต้องทำจากน้ำที่สะอาด และเพียงพอกับการใช้งาน
    </button>
  </h2>
  <div id="collapse4_1" class="accordion-collapse collapse" aria-labelledby="heading4_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_1" id="status_4_1_pass" value="pass"
                   data-item-code="4_1">
            <label class="form-check-label" for="status_4_1_pass">
              ผ่าน - ใช้น้ำแข็งที่ได้มาตรฐาน GMP หรือ อย. และใช้น้ำอุปโภคบริโภคที่สะอาด ไม่มีสี ไม่มีกลิ่น และมีปริมาณเพียงพอ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_1" id="status_4_1_fail" value="fail"
                   data-item-code="4_1">
            <label class="form-check-label" for="status_4_1_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_4_1" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_1_1"
                   data-code="fail_4_1_1"
                   data-item-code="4_1"
                   data-text="น้ำแข็งไม่มีใบรับรองมาตรฐาน GMP หรือ อย.">
            <label class="form-check-label" for="fail_4_1_1">
              น้ำแข็งไม่มีใบรับรองมาตรฐาน GMP หรือ อย.
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_1_2"
                   data-code="fail_4_1_2"
                   data-item-code="4_1"
                   data-text="น้ำแข็งชื้น มีคราบ มีสิ่งสกปรก">
            <label class="form-check-label" for="fail_4_1_2">
              น้ำแข็งชื้น มีคราบ มีสิ่งสกปรก
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_1_3"
                   data-code="fail_4_1_3"
                   data-item-code="4_1"
                   data-text="น้ำอุปโภคบริโภค ไม่สะอาด มีสี มีกลิ่น">
            <label class="form-check-label" for="fail_4_1_3">
              น้ำอุปโภคบริโภค ไม่สะอาด มีสี มีกลิ่น
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_1_4"
                   data-code="fail_4_1_4"
                   data-item-code="4_1"
                   data-text="น้ำแข็งหรือน้ำอุปโภคบริโภคมีปริมาณไม่เพียงพอ">
            <label class="form-check-label" for="fail_4_1_4">
              น้ำแข็งหรือน้ำอุปโภคบริโภคมีปริมาณไม่เพียงพอ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_1"
                    data-code="remark_4_1"
                    data-item-code="4_1"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>




<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_2"
            aria-expanded="false" aria-controls="collapse4_2">
      4.2 สถานที่เก็บ และภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี สะอาด ถูกสุขลักษณะ ทำด้วยวัสดุปลอดสนิมและทำความสะอาดได้ง่าย
    </button>
  </h2>
  <div id="collapse4_2" class="accordion-collapse collapse" aria-labelledby="heading4_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_2" id="status_4_2_pass" value="pass"
                   data-item-code="4_2">
            <label class="form-check-label" for="status_4_2_pass">
              ผ่าน - สถานที่เก็บและภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องอยู่ในสภาพดี ไม่มีสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_2" id="status_4_2_fail" value="fail"
                   data-item-code="4_2">
            <label class="form-check-label" for="status_4_2_fail">
              ไม่ผ่าน - สถานที่เก็บและภาชนะที่บรรจุน้ำจืด และน้ำแข็งมีคราบสกปรกเป็นสนิม
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_2"
                    data-code="remark_4_2"
                    data-item-code="4_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_3"
            aria-expanded="false" aria-controls="collapse4_3">
      4.3 มีการถ่ายเท ขนถ่ายน้ำจืดและน้ำแข็งอย่างถูกสุขลักษณะ น้ำแข็งลงเรือ
    </button>
  </h2>
  <div id="collapse4_3" class="accordion-collapse collapse" aria-labelledby="heading4_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_3" id="status_4_3_pass" value="pass"
                   data-item-code="4_3">
            <label class="form-check-label" for="status_4_3_pass">
              ผ่าน - เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด ไม่เป็นสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_3" id="status_4_3_fail" value="fail"
                   data-item-code="4_3">
            <label class="form-check-label" for="status_4_3_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_4_3" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_3_1"
                   data-code="fail_4_3_1"
                   data-item-code="4_3"
                   data-text="ไม่ผ่าน - เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด เป็นสนิม">
            <label class="form-check-label" for="fail_4_3_1">
              เครื่องมือ ภาชนะขนถ่ายและรางขนส่งน้ำแข็งและน้ำจืด เป็นสนิม
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="fail_4_3_2"
                   data-code="fail_4_3_2"
                   data-item-code="4_3"
                   data-text="ไม่ผ่าน - น้ำแข็งวางกองอยู่บนพื้นก่อนลงเรือ">
            <label class="form-check-label" for="fail_4_3_2">
              น้ำแข็งวางกองอยู่บนพื้นก่อนลงเรือ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_3"
                    data-code="remark_4_3"
                    data-item-code="4_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading4_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse4_4"
            aria-expanded="false" aria-controls="collapse4_4">
      4.4 ภาชนะที่บรรจุน้ำจืด และน้ำแข็งต้องมีฝาปิดมิดชิด
    </button>
  </h2>
  <div id="collapse4_4" class="accordion-collapse collapse" aria-labelledby="heading4_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-4-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_4" id="status_4_4_pass" value="pass"
                   data-item-code="4_4">
            <label class="form-check-label" for="status_4_4_pass">
              ผ่าน - มีฝาปิดมิดชิด
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_4_4" id="status_4_4_fail" value="fail"
                   data-item-code="4_4">
            <label class="form-check-label" for="status_4_4_fail">
              ไม่ผ่าน - ไม่มีฝาปิด หรือฝาปิดชำรุด
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_4_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_4_4"
                    data-code="remark_4_4"
                    data-item-code="4_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 4 -->



</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>
<script>
$(document).ready(function () {
  // ✅ autosave radio ทุกข้อ
  $('input[type="radio"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('name');
    const value = $(this).val();

    // 👉 toggle checklist group ถ้ามี
    const groupId = '#fail_group_' + field.replace('status_', '');
    if ($(groupId).length) {
      $(groupId).toggle(value === 'fail');
    }

    autosave(requestId, field, value);
  });

  // ✅ autosave checkbox ทุกข้อ
  $('input[type="checkbox"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).is(':checked') ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ autosave textarea ทุกข้อ
  $('textarea').on('input', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // 🔁 core autosave
  function autosave(requestId, field, value) {
    $.ajax({
      url: 'ajax/autosave_structure.php',
      method: 'POST',
      data: {
        request_id: requestId,
        field: field,
        value: value
      },
      success: function (res) {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }
});
</script>


<script>
  $(document).ready(function () {
    const requestId = <?= json_encode($request_id) ?>;

    $.post('ajax/load_waterice_all.php', { request_id: requestId }, function (res) {
      if (!res.success) return;

      const data = res.data;
      console.log(data);

      // 🧠 วนทุก field ที่ได้มา
      for (const [key, value] of Object.entries(data)) {
        if (value === null || value === "") continue;

        // ✅ radio (status_1_x)
        if (key.startsWith('status_')) {
          $(`input[name="${key}"][value="${value}"]`).prop('checked', true);

          // ถ้าเป็น fail → แสดงกล่อง checklist
          if (value === 'fail') {
            const code = key.replace('status_', '');
            $(`#fail_group_${code}`).show();
          }
        }

        // ✅ checkbox (fail_1_x_x)
        else if (key.startsWith('fail_') && value == '1') {
          $(`input[id="${key}"]`).prop('checked', true);
        }

        // ✅ textarea (remark_1_x)
        else if (key.startsWith('remark_')) {
          $(`#${key}`).val(value);
        }
      }
    }, 'json');
  });
</script>


<script>
$(document).ready(function () {

  // ✅ แก้ปัญหาเมื่อเลือก "ผ่าน" ต้อง uncheck checkbox ทั้งหมดใน fail group
  $('input[type="radio"].form-status-radio').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const itemCode = $(this).data('item-code'); // เช่น 2_1, 2_4
    const field = $(this).attr('name'); // เช่น status_2_1
    const value = $(this).val(); // pass / fail
    const failGroup = $('#fail_group_' + itemCode);

    // 👉 toggle group
    if (value === 'fail') {
      failGroup.slideDown();
    } else {
      failGroup.slideUp();

      // ✅ ยกเลิก checkbox ทั้งหมดในกลุ่ม และ autosave = 0
      failGroup.find('input[type="checkbox"]').each(function () {
        if ($(this).is(':checked')) {
          $(this).prop('checked', false);
          const checkboxId = $(this).attr('id');
          autosave(requestId, checkboxId, 0);
        }
      });
    }

    autosave(requestId, field, value);
  });

  // ✅ autosave checkbox ทุกข้อ
  $('input[type="checkbox"]').on('change', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).is(':checked') ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ autosave textarea ทุกข้อ
  $('textarea').on('input', function () {
    const requestId = $(this).closest('form').find('input[name="request_id"]').val();
    const field = $(this).attr('id');
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // 🔁 autosave core
  function autosave(requestId, field, value) {
    $.ajax({
      url: 'ajax/autosave_waterice.php',
      method: 'POST',
      data: {
        request_id: requestId,
        field: field,
        value: value
      },
      success: function () {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }

});
</script>


<?
include("../../private/shared/footerall.php");
?>