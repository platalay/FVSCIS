$(function () {

  // ============================================================
  // 1) ดึง requestId จากปุ่ม back → ถ้าไม่เจอ ใช้ hidden input
  // ============================================================
  let requestId = null;

  const backBtn = document.getElementById('btn-back');
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

  if (!requestId) {
    console.warn("❌ requestId ไม่พบ → ไม่ทำ autosave");
    return;
  }

  console.log("📌 requestId =", requestId);

  // ============================================================
  // 2) ธงป้องกัน autosave ระหว่าง preload
  // ============================================================
  let PRELOADING = true;

  // ช่วยเปิด/ปิด fail group ตามสถานะ
  function updateFailGroupUI(code) {
    const $form = $(`.form-inspect[data-item-code="${code}"]`);
    if (!$form.length) return;

    const isFail = $form.find(`#status_${code}_fail`).is(':checked');
    const $failGroup = $form.find(`#fail_group_${code}`);

    if (!$failGroup.length) return;

    if (isFail) $failGroup.show();
    else $failGroup.hide();
  }

  // ============================================================
  // 3) โหลดข้อมูล (แบบปลอดภัย error ก็ resolve)
  // ============================================================
  function loadAll(reqId) {
    return new Promise((resolve) => {
      $.ajax({
        url: loadAllUrl,
        method: 'POST',
        data: { request_id: reqId },
        dataType: 'json'
      })
      .done(function (res) {
        console.log("📥 loadAll response:", res);

        // ซ่อน Fail group ทั้งหมดก่อน (ค่า default)
        $('.form-inspect').each(function () {
          const code = $(this).data('item-code');
          $(`#fail_group_${code}`).hide();
        });

        if (res && res.success) {
          const data = res.data || {};
          Object.keys(data).forEach((field) => {
            const val = data[field];

            // ---- status_x_x
            if (field.startsWith('status_')) {
              $(`input[name="${field}"][value="${val}"]`).prop('checked', true);
              const code = field.replace('status_', '');
              updateFailGroupUI(code);
            }

            // ---- fail_x_x_x
            else if (field.startsWith('fail_')) {
              if (String(val) === '1') {
                $(`#${field}`).prop('checked', true);
              }
            }

            // ---- remark_x_x
            else if (field.startsWith('remark_')) {
              $(`#${field}`).val(val);
            }
          });
        }
      })
      .fail(function (xhr, status, error) {
        console.error("❌ loadAll error:", status, error);
        console.log("❗ Response:", xhr.responseText);
      })
      .always(function () {
        // ให้ resolve เสมอไม่ว่าผิดหรือถูก → เพื่อเปิด autosave ต่อ
        resolve();
      });
    });
  }

  // เริ่มโหลดข้อมูล → จากนั้นเปิด autosave
  loadAll(requestId).then(() => {
    PRELOADING = false;
    console.log("✨ PRELOADING = false → autosave เริ่มทำงานแล้ว");
  });


  // ============================================================
  // 4) autosave function
  // ============================================================
  function autosave(requestId, field, value) {
    if (PRELOADING) {
      console.log("⛔ block autosave ช่วง PRELOADING:", field);
      return;
    }

    $.ajax({
      url: autosaveUrl,
      method: 'POST',
      data: { request_id: requestId, field: field, value: value },
      success: function () {
        console.log(`💾 autosaved: ${field} = ${value}`);
      },
      error: function () {
        console.error(`❌ autosave failed: ${field}`);
      }
    });
  }


  // ============================================================
  // 5) เปลี่ยนสถานะ (radio)
  // ============================================================
  $(document).on('change', 'input[type="radio"]', function () {
    if (PRELOADING) return;

    const field = this.name;          // status_1_4
    const value = this.value;         // pass|fail
    const code  = field.replace('status_', '');
    const $failGroup = $('#fail_group_' + code);

    if (value === 'fail') {
      $failGroup.slideDown();
    } else {
      // เลือก "ผ่าน" → ซ่อนและล้าง checkbox & remark
      $failGroup.slideUp();

      $failGroup.find('input[type="checkbox"]').each(function () {
        if (this.checked) {
          this.checked = false;
          autosave(requestId, this.id, 0);
        }
      });

      const $remark = $('#remark_' + code);
      if ($remark.length && $remark.val()) {
        $remark.val('');
        autosave(requestId, 'remark_' + code, '');
      }
    }

    autosave(requestId, field, value);
  });


  // ============================================================
  // 6) checkbox fail_x_x_x
  // ============================================================
  $(document).on('change', 'input[type="checkbox"]', function () {
    if (PRELOADING) return;

    if (!this.id.startsWith('fail_')) return;

    const value = this.checked ? 1 : 0;
    autosave(requestId, this.id, value);
  });


  // ============================================================
  // 7) remark textarea
  // ============================================================
  $(document).on('input', 'textarea', function () {
    if (PRELOADING) return;
    autosave(requestId, this.id, $(this).val());
  });

  $(document).on('blur', 'textarea', function () {
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


  // ============================================================
  // 8) validate ตอนกดปุ่มกลับ
  // ============================================================
  $('#btn-back').on('click', function (e) {
    let invalid = false;

    $('.form-inspect').each(function () {
      const $form = $(this);
      const code = $form.data('item-code');

      const $pass = $form.find(`#status_${code}_pass`);
      const $fail = $form.find(`#status_${code}_fail`);

      if (!$pass.is(':checked') && !$fail.is(':checked')) {
        return; // ไม่ได้เลือก → ไม่บังคับ
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
        text: 'กรุณาติ๊กเหตุผลไม่ผ่าน หรือกรอกหมายเหตุอย่างน้อย 3 ตัวอักษร',
        confirmButtonText: 'ตกลง'
      });
    }
  });


  // ============================================================
  // 9) กันพับ accordion ถ้ายังไม่ระบุเหตุผลใน "ไม่ผ่าน"
  // ============================================================
  $('#inspectionAccordion .accordion-collapse').on('hide.bs.collapse', function (e) {
    const $accordionBody = $(this).find('.accordion-body');
    const $form = $accordionBody.find('.form-inspect');
    const code = $form.data('item-code');

    const $pass = $form.find(`#status_${code}_pass`);
    const $fail = $form.find(`#status_${code}_fail`);

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
          text: 'ข้อที่เลือก "ไม่ผ่าน" ต้องติ๊กเหตุผลอย่างน้อย 1 ข้อ หรือกรอกหมายเหตุ 3 ตัวอักษร',
          confirmButtonText: 'ตกลง'
        });
      }
    }
  });

});
