<!-- Modal: Edit Inspection Request -->
<div class="modal fade" id="editInspectionModal" tabindex="-1" aria-labelledby="editInspectionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="editInspectionForm" method="post" action="ajax/update_inspection.php">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="editInspectionModalLabel">แก้ไขคำขอตรวจเรือ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <!-- hidden id -->
          <input type="hidden" name="request[id]" id="edit_request_id">

          <!-- รายละเอียดเรือ -->
          <div class="mb-3">
            <strong>เลขทะเบียนเรือ:</strong> <span id="edit-modal-ship-code"></span><br>
            <strong>ชื่อเรือ:</strong> <span id="edit-modal-vessel-name"></span><br>
            <strong>ขนาดตันกรอส:</strong> <span id="edit-modal-vessel-ton"></span> ตัน<br>
            <strong>พื้นที่ทำการประมง:</strong> <span id="edit-modal-fishing-area"></span>
          </div>

          <!-- Hidden ship_code -->
          <input type="hidden" name="request[ship_code]" id="edit_hidden_ship_code">

          <!-- Contact -->
          <div class="mb-3">
            <label class="form-label">หมายเลขโทรศัพท์ที่ติดต่อได้</label>
            <input type="text"
                   name="request[contact_phone]"
                   id="edit_contact_phone"
                   class="form-control"
                   maxlength="10"
                   inputmode="numeric"
                   autocomplete="tel">
          </div>

          <!-- จังหวัด-อำเภอ-ตำบล-ท่าเรือ -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">จังหวัด</label>
              <select name="request[port_province_id]" id="edit_port_province_id" class="form-select">
                <option value="">-- เลือกจังหวัด --</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">อำเภอ</label>
              <select name="request[port_amphur_id]" id="edit_port_amphur_id" class="form-select">
                <option value="">-- เลือกอำเภอ --</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">ตำบล</label>
              <select name="request[port_tambon_id]" id="edit_port_tambon_id" class="form-select">
                <option value="">-- เลือกตำบล --</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">ท่าเรือ</label>
              <select name="request[port_license_no]" id="edit_port_license_no" class="form-select">
                <option value="">-- เลือกท่าเรือ --</option>
              </select>
            </div>
          </div>

          <!-- Department -->
          <div class="mb-3">
            <label class="form-label">หน่วยงานที่ยื่นคำขอ</label>
            <select name="request[department_id]" id="edit_department_id" class="form-select">
              <option value="">-- เลือกหน่วยงาน --</option>
            </select>
          </div>

          <!-- วันที่ต้องการตรวจ -->
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">วันที่เริ่มต้องการตรวจ</label>
              <input type="date" name="request[inspect_date_start]" id="edit_inspect_date_start" class="form-control">
            </div>
            <div class="col">
              <label class="form-label">ถึงวันที่</label>
              <input type="date" name="request[inspect_date_end]" id="edit_inspect_date_end" class="form-control">
            </div>
          </div>

          <!-- EU Export -->
          <div class="mb-3 p-3 border rounded">
            <div class="form-check">
              <input type="hidden" name="request[inspection_form_type]" id="edit_inspection_form_type" value="1" >
              <input class="form-check-input" type="checkbox" id="edit_eu_cert_checkbox"  disabled>
              <label class="form-check-label fw-semibold">
                ขอหนังสือรับรองสุขอนามัยเรือประมงเพื่อส่งออกสหภาพยุโรป
              </label>
            </div>
            <small class="text-muted">ไม่เลือก = แบบ 1 | เลือก = แบบ 2</small>
          </div>

          <!-- Cold Room -->
          <div class="mb-3 p-3 border rounded">
            <div class="form-check">
              <input type="hidden" name="request[cold_room_flag]" id="edit_cold_room_flag" value="0">
              <input class="form-check-input" type="checkbox" id="edit_cold_room_checkbox"  disabled>
              <label class="form-check-label fw-semibold">
                เรือห้องเย็น / มีระบบทำความเย็น
              </label>
            </div>
          </div>

          <!-- แนบไฟล์ใหม่เพิ่ม (ถ้าจะให้แก้ไฟล์เก่าทีหลังค่อยทำ endpoint ลบ) -->
            <!-- input file old new -->
                                <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                        <div class="fw-semibold">ไฟล์แนบใบรับรอง</div>
                                        <div class="text-muted small">รองรับ .jpg .jpeg .png .gif .webp .pdf (สูงสุด ~10MB/ไฟล์)</div>
                                </div>
                                <div style="min-width:260px;">
                                        <!-- ใช้ name="attachments[]" ให้เข้ากับสคริปต์บันทึกเดิมของคุณ -->
                                        <input type="file" id="manualAttachmentsEdit" name="attachments[]" multiple
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                                        class="form-control form-control-sm">
                                </div>
                                </div>

                                <!-- พรีวิวไฟล์ที่ *จะอัปโหลดใหม่* -->
                                <div class="row g-3 mt-2" id="manualSelectedFilesEdit"></div>

                                <!-- รายการไฟล์ที่มีอยู่เดิมในระบบ -->
                                <div class="mt-3">
                                <div class="fw-semibold mb-2">ไฟล์ที่แนบไว้แล้ว</div>
                                <div class="row g-3" id="manualExistingFiles"></div>
                                </div>
                                </div>
                                </div>

                                <!-- /input file old new --> 
          <!-- Vessel Name -->
          <input type="hidden" name="request[vessel_name]" id="edit_hidden_vessel_name">

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        </div>

      </div>
    </form>
  </div>
</div>
<!-- /Modal -->
