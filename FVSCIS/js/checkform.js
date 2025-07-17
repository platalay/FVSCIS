$(document).ready(function () {
  const href = document.getElementById('btn-back').getAttribute('href');
  const urlParams = new URLSearchParams(href.split('?')[1]);
  const requestId = urlParams.get('id');

  // ✅ โหลดข้อมูลเมื่อเริ่มต้น
  loadMaterialData(requestId);

  // ✅ กด radio toggle + autosave
  $('input[type="radio"]').on('change', function () {
    const field = $(this).attr('name');
    const value = $(this).val();
    const itemCode = field.replace('status_', '');
    const failGroup = $('#fail_group_' + itemCode);

    if (value === 'fail') {
      failGroup.slideDown();
    } else {
      failGroup.slideUp();
      failGroup.find('input[type="checkbox"]').each(function () {
        if (this.checked) {
          this.checked = false;
          autosave(requestId, this.id, 0);
        }
      });
    }

    autosave(requestId, field, value);
  });

  // ✅ checkbox → autosave
  $('input[type="checkbox"]').on('change', function () {
    const field = this.id;
    const value = this.checked ? 1 : 0;
    autosave(requestId, field, value);
  });

  // ✅ textarea → autosave + เตือนทันทีถ้าสั้นเกินไป
  $('textarea').on('input', function () {
    const field = this.id;
    const value = $(this).val();
    autosave(requestId, field, value);
  }).on('blur', function () {
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

  function autosave(requestId, field, value) {
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

  function loadMaterialData(requestId) {
    $.post(loadAllUrl, { request_id: requestId }, function (res) {
      if (res.success) {
        const data = res.data;
        for (let field in data) {
          const value = data[field];
          if (field.startsWith('status_')) {
            $(`input[name="${field}"][value="${value}"]`).prop('checked', true).trigger('change');
          }
          if (field.startsWith('fail_') && value === "1") {
            $(`#${field}`).prop('checked', true);
          }
          if (field.startsWith('remark_')) {
            $(`#${field}`).val(value);
          }
        }
      } else {
        alert('โหลดข้อมูลไม่สำเร็จ: ' + res.message);
      }
    }, 'json');
  }

  // ✅ ตรวจตอนกดกลับหน้าหลัก
  $('#btn-back').on('click', function (e) {
    let invalid = false;

    $('.form-inspect').each(function () {
      const $form = $(this);
      const code = $form.data('item-code');

      const failChecked = $form.find(`#status_${code}_fail`).is(':checked');
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

  // ✅ ตรวจตอนปิด accordion
  $('#inspectionAccordion .accordion-collapse').on('hide.bs.collapse', function (e) {
    const $accordionBody = $(this).find('.accordion-body');
    const $form = $accordionBody.find('.form-inspect');
    const code = $form.data('item-code');

    const failChecked = $form.find(`#status_${code}_fail`).is(':checked');
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
