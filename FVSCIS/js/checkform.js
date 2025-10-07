$(function () {
  // ===== ค่าพื้นฐาน =====
  const backBtn = document.getElementById('btn-back');
  let requestId = null;
  if (backBtn && backBtn.getAttribute('href')) {
    const href = backBtn.getAttribute('href');
    const params = new URLSearchParams((href.split('?')[1] || ''));
    requestId = params.get('id');
  }
  // fallback: หา request_id จาก hidden ในฟอร์มแรก
  if (!requestId) {
    const hid = document.querySelector('.form-inspect input[name="request_id"]');
    requestId = hid ? hid.value : null;
  }

  // ถ้าไม่มี requestId ก็พอแค่นี้
  if (!requestId) return;

  // ===== ธงป้องกัน autosave ระหว่าง preload =====
  let PRELOADING = true;

  // ===== ช่วยแสดง/ซ่อนกลุ่มเหตุผล โดยไม่ยิง change =====
  function updateFailGroupUI(code) {
    const $form = $(`.form-inspect[data-item-code="${code}"]`);
    const isFail = $form.find(`#status_${code}_fail`).is(':checked');
    const $failGroup = $form.find(`#fail_group_${code}`);
    if ($failGroup.length) {
      if (isFail) $failGroup.show(); else $failGroup.hide();
    }
  }

  // ===== โหลดข้อมูลครั้งแรก (ไม่ให้ autosave ทำงาน) =====
  loadAll(requestId).then(() => {
    PRELOADING = false; // เปิด autosave หลังเติมค่าเสร็จ
  });

  async function loadAll(reqId) {
    return new Promise((resolve) => {
      $.post(loadAllUrl, { request_id: reqId }, function (res) {
        // ซ่อน Fail group ทั้งหมดก่อน (ค่าเริ่มต้น)
        $('.form-inspect').each(function(){
          const code = $(this).data('item-code');
          $(`#fail_group_${code}`).hide();
        });

        if (res && res.success) {
          const data = res.data || {};
          // เติมค่าทีละฟิลด์ "แบบเงียบ" ไม่ trigger change
          Object.keys(data).forEach((field) => {
            const val = data[field];

            if (field.startsWith('status_')) {
              // เลือก radio ให้ตรงค่า แต่ไม่ trigger change
              $(`input[name="${field}"][value="${val}"]`).prop('checked', true);
              const code = field.replace('status_', '');
              updateFailGroupUI(code); // แค่ปรับ UI
            } else if (field.startsWith('fail_')) {
              // เช็คเฉพาะที่เป็น 1
              if (String(val) === '1') { $(`#${field}`).prop('checked', true); }
            } else if (field.startsWith('remark_')) {
              $(`#${field}`).val(val);
            }
          });
        }
        resolve();
      }, 'json');
    });
  }

  // ===== autosave (กันยิงตอน PRELOADING) =====
  function autosave(requestId, field, value) {
    if (PRELOADING) return; // กันยิงตอนโหลด
    $.ajax({
      url: autosaveUrl,
      method: 'POST',
      data: { request_id: requestId, field: field, value: value },
      success: function () {
        console.log('✅ autosaved:', field, '=', value);
      },
      error: function () {
        console.error('❌ autosave failed:', field);
      }
    });
  }

  // ===== เปลี่ยนสถานะ ผ่าน/ไม่ผ่าน =====
  $(document).on('change', 'input[type="radio"]', function (e) {
    if (PRELOADING) return;
    const field = this.name;              // เช่น status_1_4
    const value = this.value;             // pass/fail
    const itemCode = field.replace('status_', '');
    const $failGroup = $('#fail_group_' + itemCode);

    if (value === 'fail') {
      $failGroup.slideDown();
    } else {
      // เลือก "ผ่าน" → ซ่อนและเคลียร์เช็คบ็อกซ์ + หมายเหตุ
      $failGroup.slideUp();
      $failGroup.find('input[type="checkbox"]').each(function () {
        if (this.checked) {
          this.checked = false;
          autosave(requestId, this.id, 0);
        }
      });
      const $remark = $('#remark_' + itemCode);
      if ($remark.length && $remark.val()) {
        $remark.val('');
        autosave(requestId, 'remark_' + itemCode, '');
      }
    }
    autosave(requestId, field, value);
  });

  // ===== เช็คบ็อกซ์เหตุผลไม่ผ่าน =====
  $(document).on('change', 'input[type="checkbox"]', function () {
    if (PRELOADING) return;
    // ถ้าเป็นเช็คบ็อกซ์ในไฟล์อื่น ให้กรองเฉพาะที่มี id (fail_x_x_x)
    if (!this.id) return;
    const value = this.checked ? 1 : 0;
    autosave(requestId, this.id, value);
  });

  // ===== หมายเหตุ =====
  $(document).on('input', 'textarea', function () {
    if (PRELOADING) return;
    autosave(requestId, this.id, $(this).val());
  }).on('blur', 'textarea', function () {
    const value = $(this).val().trim();
    if (value !== '' && value.length < 3) {
      Swal.fire({
        icon: 'warning',
        title: 'หมายเหตุสั้นเกินไป',
        text: 'กรุณากรอกหมายเหตุอย่างน้อย 3 ตัวอักษร',
        timer: 2000,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
      });
    }
  });

  // ===== ตรวจตอนกดกลับ =====
  $('#btn-back').on('click', function (e) {
    let invalid = false;

    $('.form-inspect').each(function () {
      const $form = $(this);
      const code = $form.data('item-code');

      // ถ้าไม่ได้เลือก pass/fail เลย → ไม่บังคับ
      const $pass = $form.find(`#status_${code}_pass`);
      const $fail = $form.find(`#status_${code}_fail`);
      if (!$pass.is(':checked') && !$fail.is(':checked')) {
        return; // ข้าม
      }

      const failChecked = $fail.is(':checked');
      const $failGroup = $form.find(`#fail_group_${code}`);
      const checkboxCount = $failGroup.find('input[type=checkbox]').length;

      if (failChecked && checkboxCount > 0) {
        const anyCheckbox = $failGroup.find('input[type=checkbox]:checked').length > 0;
        const remarkText = $form.find(`#remark_${code}`).val().trim();
        if (!anyCheckbox && (remarkText === '' || remarkText.length < 3)) {
          invalid = true;
        }
      }
    });

    if (invalid) {
      e.preventDefault();
      Swal.fire({
        icon: 'warning',
        title: 'กรุณาตรวจสอบข้อมูล',
        text: 'มีข้อที่เลือก "ไม่ผ่าน" โดยไม่ได้ติ๊กเงื่อนไข หรือหมายเหตุสั้นเกินไป',
        confirmButtonText: 'ตกลง'
      });
    }
  });

  // ===== กันปิด accordion ถ้ายังไม่กรอกเหตุผลในกรณีเลือก "ไม่ผ่าน" =====
  $('#inspectionAccordion .accordion-collapse').on('hide.bs.collapse', function (e) {
    const $accordionBody = $(this).find('.accordion-body');
    const $form = $accordionBody.find('.form-inspect');
    const code = $form.data('item-code');

    const $pass = $form.find(`#status_${code}_pass`);
    const $fail = $form.find(`#status_${code}_fail`);

    // ถ้ายังไม่เลือกสถานะเลย → อนุญาตให้พับ
    if (!$pass.is(':checked') && !$fail.is(':checked')) return;

    const failChecked = $fail.is(':checked');
    const $failGroup = $form.find(`#fail_group_${code}`);
    const checkboxCount = $failGroup.find('input[type=checkbox]').length;

    if (failChecked && checkboxCount > 0) {
      const anyCheckbox = $failGroup.find('input[type=checkbox]:checked').length > 0;
      const remarkText = $form.find(`#remark_${code}`).val().trim();

      if (!anyCheckbox && (remarkText === '' || remarkText.length < 3)) {
        e.preventDefault();
        Swal.fire({
          icon: 'warning',
          title: 'กรุณาระบุเหตุผล',
          text: 'ข้อที่เลือก "ไม่ผ่าน" ต้องติ๊กเงื่อนไขอย่างน้อย 1 ข้อ หรือกรอกหมายเหตุอย่างน้อย 3 ตัวอักษร',
          confirmButtonText: 'ตกลง'
        });
      }
    }
  });
});
