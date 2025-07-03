$(document).ready(function () {
    const href = document.getElementById('btn-back').getAttribute('href');
    const urlParams = new URLSearchParams(href.split('?')[1]);
    const requestId = urlParams.get('id');

  // ✅ โหลดข้อมูลเมื่อเริ่มต้น
  loadMaterialData(requestId);

  // ✅ กด radio แล้ว toggle กล่อง fail + autosave + clear checkbox ถ้าเลือก "ผ่าน"
  $('input[type="radio"]').on('change', function () {
    const field = $(this).attr('name'); // เช่น status_2_1
    const value = $(this).val();        // pass หรือ fail
    const itemCode = field.replace('status_', ''); // เช่น 2_1
    const failGroup = $('#fail_group_' + itemCode);

    if (value === 'fail') {
      failGroup.slideDown();
    } else {
      failGroup.slideUp();

      // เคลียร์ checkbox ทุกอันในกลุ่ม
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

  // ✅ textarea → autosave
  $('textarea').on('input', function () {
    const field = this.id;
    const value = $(this).val();
    autosave(requestId, field, value);
  });

  // ✅ ฟังก์ชัน autosave กลาง
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

  // ✅ โหลดข้อมูลเดิมกลับเข้า form
  function loadMaterialData(requestId) {
    $.post(loadAllUrl, { request_id: requestId }, function (res) {
      if (res.success) {
        const data = res.data;
        for (let field in data) {
          const value = data[field];

          // Radio
          if (field.startsWith('status_')) {
            $(`input[name="${field}"][value="${value}"]`).prop('checked', true).trigger('change');
          }

          // Checkbox
          if (field.startsWith('fail_') && value === "1") {
            $(`#${field}`).prop('checked', true);
          }

          // Textarea
          if (field.startsWith('remark_')) {
            $(`#${field}`).val(value);
          }
        }
      } else {
        alert('โหลดข้อมูลไม่สำเร็จ: ' + res.message);
      }
    }, 'json');
  }

  //เช็คไม่ผ่าน แล้วไม่ check ไม่กรอก ตอนกดกลับหน้าหลัก
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

        if (!anyCheckbox && remarkText === '') {
          invalid = true;
        }
      }
    });

    if (invalid) {
      e.preventDefault(); // ยกเลิกลิงก์
      Swal.fire({
        icon: 'warning',
        title: 'กรุณาตรวจสอบข้อมูล',
        text: 'มีข้อที่เลือก "ไม่ผ่าน" โดยไม่ได้ติ๊กเงื่อนไขหรือกรอกหมายเหตุ',
        confirmButtonText: 'ตกลง'
      });
    }
  });

  //เช็คไม่ผ่าน แล้วไม่ check ไม่กรอก ตอนปิด accordion
   $('#inspectionAccordion .accordion-collapse').on('hide.bs.collapse', function (e) {

    const $accordionBody = $(this).find('.accordion-body');
    const $form = $accordionBody.find('.form-inspect');
    const code = $form.data('item-code');

    const failChecked = $form.find(`#status_${code}_fail`).is(':checked');
    const $failGroup = $form.find(`#fail_group_${code}`);
    const checkboxCount = $failGroup.find('input[type=checkbox]').length;

    console.log(`ตรวจข้อ ${code}: failChecked=${failChecked} checkboxCount=${checkboxCount}`);

    if (failChecked && checkboxCount > 0) {
      const anyCheckbox = $failGroup.find('input[type=checkbox]:checked').length > 0;
      const remarkText = $form.find(`#remark_${code}`).val().trim();

      console.log(`anyCheckbox=${anyCheckbox} remarkText="${remarkText}"`);

      if (!anyCheckbox && remarkText === '') {
        // 🚨 ยกเลิกการปิด accordion
        e.preventDefault();

        Swal.fire({
          icon: 'warning',
          title: 'กรุณาระบุเหตุผล',
          text: 'ข้อที่เลือก "ไม่ผ่าน" ต้องติ๊กเงื่อนไขอย่างน้อย 1 ข้อ หรือกรอกหมายเหตุ',
          confirmButtonText: 'ตกลง'
        });
      }
    }
  });


});
