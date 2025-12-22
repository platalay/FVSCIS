<!-- modalApproveRequest -->
<div class="modal fade" id="modalApproveRequest" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="approveInspectionForm">
        <div class="modal-header">
          <h5 class="modal-title">อนุมัติคำขอตรวจเรือ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body" id="modalApproveBody">
          <!-- ✅ รายละเอียดคำขอจะโหลดมาใส่ตรงนี้โดย JS -->
        </div>

        <div class="modal-footer flex-column align-items-stretch">
          <input type="hidden" name="request_id" id="approve_request_id">

          <!-- ✅ วันที่มีผลบังคับใช้ -->
          <div class="mb-3 w-100">
            <label for="effective_date_approve" class="form-label">วันที่มีผลบังคับใช้</label>
            <input type="date"
                   class="form-control"
                   name="effective_date"
                   id="effective_date_approve"
                   required>
          </div>

          <div class="mb-3">
            <label for="temporary_reason" class="form-label">เหตุผลออกใบรับรองชั่วคราว</label>
            <textarea class="form-control" name="temporary_reason" id="temporary_reason" rows="2"></textarea>
          </div>

          <div class="mb-3">
            <label for="approval_note_approve" class="form-label">หมายเหตุ (ถ้ามี)</label>
            <textarea class="form-control"
                      name="approval_note"
                      id="approval_note_approve"
                      rows="2"></textarea>
          </div>

          <button type="submit" class="btn btn-success w-100" id="btnApproveRequest">
            ✅ อนุมัติคำขอและออกใบรับรอง
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- /modalApproveRequest -->
