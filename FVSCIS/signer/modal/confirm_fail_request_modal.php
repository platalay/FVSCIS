<!-- modalConfirmFail -->
<div class="modal fade" id="modalConfirmFail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form id="confirmFailForm">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">❌ ยืนยันผลไม่ผ่านการตรวจ</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div id="modalConfirmFailBody" class="mb-3">
            <!-- ✅ รายละเอียดคำขอจะโหลดมาใส่ตรงนี้โดย JS -->
          </div>

          <input type="hidden" name="request_id" id="fail_request_id">

          <div class="mb-3">
            <label for="effective_date_fail" class="form-label">วันที่มีผลบังคับใช้ผลไม่ผ่าน</label>
            <input type="date"
                   class="form-control"
                   name="effective_date"
                   id="effective_date_fail"
                   required>
          </div>

          <div class="mb-3">
            <label for="approval_note_fail" class="form-label">หมายเหตุ (ถ้ามี)</label>
            <textarea class="form-control"
                      name="approval_note"
                      id="approval_note_fail"
                      rows="3"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-danger" id="btnConfirmFail">ยืนยันผลไม่ผ่าน</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /modalConfirmFail -->

