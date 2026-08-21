<!-- Modal ข้อมูลผู้ยื่นคำขอ สร.1 -->
<div class="modal fade" id="modalApplicant" tabindex="-1" role="dialog" aria-labelledby="modalApplicantLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="form_applicant">
        <div class="modal-header">
          <h5 class="modal-title" id="modalApplicantLabel">ข้อมูลผู้ยื่นคำขอ สร.1</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">

          <!-- hidden -->
          <input type="hidden" name="request_id" id="request_id">

          <!-- เขียนที่ / วันที่ -->
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>เขียนที่</label>
              <input type="text" class="form-control" name="written_at" id="written_at" readonly>
            </div>
            <div class="form-group col-md-6">
              <label>วันที่</label>
              <input type="text" class="form-control" name="written_date_text" id="written_date_text" readonly>
              <input type="hidden" class="form-control" name="written_date" id="written_date">
            </div>
          </div>

          <hr>

          <!-- ประเภทผู้ยื่น -->
          <div class="form-group">
            <label>ประเภทผู้ยื่นคำขอ</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_juristic" id="is_juristic_0" value="0"  readonly>
                <label class="form-check-label" for="is_juristic_0">บุคคลธรรมดา (ข้าพเจ้า)</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_juristic" id="is_juristic_1" value="1"  readonly>
                <label class="form-check-label" for="is_juristic_1">นิติบุคคล</label>
              </div>
            </div>
          </div>

          <!-- ส่วน ข้าพเจ้า ... -->
          <div class="border rounded p-3 mb-3" id="block_person">
            <h6 class="font-weight-bold mb-3">ข้อมูลผู้ยื่น (บุคคลธรรมดา / ผู้แทน)</h6>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label id="label_applicant_name">ข้าพเจ้า (ชื่อ–สกุล)</label>
                <input type="text" class="form-control" name="applicant_name" id="applicant_name" required>
              </div>
              <div class="form-group col-md-3">
                <label>อายุ (ปี)</label>
                <input type="number" class="form-control" name="applicant_age" id="applicant_age" min="0" required>
              </div>
              <div class="form-group col-md-3">
                <label>สัญชาติ</label>
                <input type="text" class="form-control" name="applicant_nationality" id="applicant_nationality" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>มีภูมิลำเนาอยู่บ้านเลขที่</label>
                <input type="text" class="form-control" name="applicant_address_no" id="applicant_address_no" required>
              </div>
              <div class="form-group col-md-2">
                <label>หมู่ที่</label>
                <input type="text" class="form-control" name="applicant_moo" id="applicant_moo">
              </div>
              <div class="form-group col-md-4">
                <label>ตำบล</label>
                <input type="text" class="form-control" name="applicant_tambon" id="applicant_tambon" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>อำเภอ</label>
                <input type="text" class="form-control" name="applicant_amphoe" id="applicant_amphoe" required>
              </div>
              <div class="form-group col-md-4">
                <label>จังหวัด</label>
                <input type="text" class="form-control" name="applicant_province" id="applicant_province" required>
              </div>
              <div class="form-group col-md-4">
                <label>โทรศัพท์</label>
                <input type="text" class="form-control" name="applicant_phone" id="applicant_phone" required readonly>
              </div>
            </div>
          </div><!-- /block_person -->

          <!-- ส่วน นิติบุคคล -->
          <div class="border rounded p-3 d-none" id="block_juristic">
            <h6 class="font-weight-bold mb-3">ข้อมูลนิติบุคคล (กรณีเป็นบริษัท/ห้างหุ้นส่วน)</h6>

            <div class="form-group">
              <label>ชื่อนิติบุคคล</label>
              <input type="text" class="form-control juristic-field" name="juristic_name" id="juristic_name"  readonly>
            </div>

            <div class="form-group">
              <label>ซึ่งมีสำนักงานอยู่ (อาคาร/สำนักงาน)</label>
              <input type="text" class="form-control juristic-field" name="juristic_office" id="juristic_office"  readonly>
            </div>

            <div class="form-row">
              <div class="form-group col-md-6">
                <label>เลขที่</label>
                <input type="text" class="form-control juristic-field" name="juristic_address_no" id="juristic_address_no"  readonly>
              </div>
              <div class="form-group col-md-2">
                <label>หมู่ที่</label>
                <input type="text" class="form-control juristic-field" name="juristic_moo" id="juristic_moo"  readonly>
              </div>
              <div class="form-group col-md-4">
                <label>ตำบล</label>
                <input type="text" class="form-control juristic-field" name="juristic_tambon" id="juristic_tambon"  readonly>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>อำเภอ</label>
                <input type="text" class="form-control juristic-field" name="juristic_amphoe" id="juristic_amphoe"  readonly>
              </div>
              <div class="form-group col-md-4">
                <label>จังหวัด</label>
                <input type="text" class="form-control juristic-field" name="juristic_province" id="juristic_province"  readonly>
              </div>
            </div>
          </div><!-- /block_juristic -->

        </div><!-- /.modal-body -->

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
</div>
