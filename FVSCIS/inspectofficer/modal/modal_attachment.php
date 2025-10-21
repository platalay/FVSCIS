<!-- 📸 Modal: รูปภาพแนบ -->
<div class="modal fade" id="modalPhotoAttachments" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">
            <i class="fas fa-images text-primary me-2"></i>
            รูปภาพแนบคำขอ:
            <span id="photoModalReqId" class="text-dark"></span>
            <small id="modalShipCode" class="text-secondary"></small>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

      <div class="modal-body">
        <!-- ภาพใหญ่ -->
        <div id="photoPreviewWrap" class="mb-3 d-none text-center">
          <img id="photoPreviewImg" src="" alt="preview" 
               class="img-fluid rounded border shadow-sm"
               style="max-height:500px; object-fit:contain;">
        </div>

        <!-- กริดรูปภาพ -->
        <div id="photoGrid" class="d-flex flex-wrap gap-2 justify-content-start"></div>

        <!-- กรณีไม่มีรูป -->
        <div id="photoEmpty" class="text-muted">ยังไม่มีรูปภาพแนบ</div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>
    </div>
  </div>
</div>
