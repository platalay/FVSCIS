<!-- modal/modal_manual_case.php -->
<div class="modal fade" id="modalManualCase" tabindex="-1" aria-labelledby="modalManualCaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formManualCase" enctype="multipart/form-data">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalManualCaseLabel">
            <i class="fas fa-plus-circle me-2"></i> สร้างคำขอสุขอนามัย (กรณีพิเศษโดยเจ้าหน้าที่)
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
             <!-- ทะเบียนเรือ + ปุ่มค้นหา -->
            <div class="col-md-3">
              <label class="form-label">ทะเบียนเรือ</label>
              <div class="input-group">
                <input type="text"
                       class="form-control"
                       id="manual-ship-code"
                       name="request[ship_code]"
                       required>
                <button class="btn btn-outline-secondary"
                        type="button"
                        id="btnLookupShipManual">
                  <span id="btnManualText">ค้นหา</span>
                  <span id="btnManualSpin" class="d-none">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  </span>
                </button>
              </div>
              <div class="form-text">
                กรณีมีข้อมูลใน eLicense สามารถกดค้นหาเพื่อตั้งค่าข้อมูลอัตโนมัติ
              </div>
            </div>
            
            <!-- ชื่อเรือ -->
            <div class="col-md-3">
              <label class="form-label">ชื่อเรือ</label>
              <input type="text"
                     class="form-control"
                     id="manual-vessel-name"
                     name="request[vessel_name]"
                     required>
            </div>

            <!-- ชื่อเจ้าของเรือ -->
            <div class="col-md-6">
              <label class="form-label">ชื่อเจ้าของเรือ</label>
              <input type="text"
                     class="form-control"
                     id="manual-owner-name"
                     name="request[owner_name]"
                     required>
            </div>

            <!-- 🔽 แถวใหม่: สัญลักษณ์เรือ / เลขที่ใบอนุญาต / ชนิดเครื่องมือ -->
            <div class="col-md-4 elicense-only d-none">
              <label class="form-label">หมายเลขสัญลักษณ์เรือ</label>
              <input type="text"
                     class="form-control"
                     id="manual-vessel-mark"
                     name="request[vessel_mark]"
                     placeholder="" readonly>
            </div>

            <div class="col-md-4 elicense-only d-none">
              <label class="form-label">เลขที่ใบอนุญาตทำการประมง (ถ้ามี)</label>
              <input type="text"
                     class="form-control"
                     id="manual-license-number"
                     name="request[license_number]"
                     placeholder="" readonly>
            </div>

            <div class="col-md-4 elicense-only d-none">
              <label class="form-label">ชนิดเครื่องมือทำการประมง (ถ้ามี)</label>
              <input type="text"
                     class="form-control"
                     id="manual-gear-type"
                     name="request[gear_type]"
                     placeholder="" readonly>
            </div>
            <!-- 🔼 จบชุดฟิลด์ใหม่ -->

            <!-- หน่วยงาน -->
            <div class="col-md-6">
              <label class="form-label">หน่วยงาน</label>
              <input type="text" class="form-control" name="department_name"
                     value="<?= h($Department->name ?? ''); ?>" readonly>
            </div>

            <div class="col-md-3">
              <label for="port_tambon_id" class="form-label">ตำบล</label>
              <select name="request[port_tambon_id]" id="port_tambon_id" class="form-select" required>
                <option value="">-- เลือกตำบล --</option>
                <?php
                $subdistricts = Tambon::find_by_amphur_id($Department->district);
                if(!empty($subdistricts)) {
                  foreach ($subdistricts as $subdistrict) {
                    echo '<option value="' . h($subdistrict->id) . '">' . h($subdistrict->name) . '</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="col-md-3">
              <label for="port_license_no" class="form-label">ท่าเรือ</label>
              <select name="request[port_license_no]" id="port_license_no" class="form-select" required>
                <option value="">-- เลือกท่าเรือ --</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="confirmed_inspect_date" class="form-label">วันที่ต้องการตรวจ</label>
              <input type="date" name="request[confirmed_inspect_date]" id="confirmed_inspect_date" class="form-control" required>
            </div>

            <!-- ข้อมูลผู้ยื่น -->
            <div class="col-md-9">
              <label for="contact_phone" class="form-label">หมายเลขโทรศัพท์ที่ติดต่อได้</label>
              <input type="text"
                     name="request[contact_phone]"
                     id="contact_phone"
                     class="form-control"
                     required
                     maxlength="10"
                     inputmode="numeric"
                     autocomplete="tel"
                     placeholder="เช่น 0891234567">
            </div>

            <!-- ✅ รูปแบบการตรวจ -->
            <div class="col-12">
              <div class="mb-3 p-3 border rounded">
                <div class="form-check">
                  <input type="hidden" name="request[inspection_form_type]" id="inspection_form_type" value="1">
                  <input class="form-check-input" type="checkbox" id="eu_cert_checkbox">
                  <label class="form-check-label fw-semibold" for="eu_cert_checkbox">
                    ขอหนังสือรับรองมาตรฐานด้านสุขอนามัยในเรือประมงเพื่อการส่งออกสินค้าสัตว์น้ำไปสหภาพยุโรป
                  </label>
                </div>
                <small class="text-muted d-block mt-2">
                  ไม่เลือก = ตรวจทั่วไป (แบบที่ 1) | เลือก = ตรวจเพื่อ EU Export (แบบที่ 2)
                </small>
              </div>
            </div>

            <div class="col-12">
              <div class="mb-3 p-3 border rounded">
                <div class="form-check">
                  <input type="hidden" name="request[cold_room_flag]" id="cold_room_flag" value="0">
                  <input class="form-check-input" type="checkbox" id="cold_room_checkbox">
                  <label class="form-check-label fw-semibold" for="cold_room_checkbox">
                    เรือห้องเย็น / มีระบบทำความเย็น
                  </label>
                </div>
                <small class="text-muted d-block mt-2">
                  ถ้าเลือก ระบบจะเพิ่มหัวข้อ 5.8–5.9 ในรายการตรวจหมวดการเก็บรักษาสัตว์น้ำ
                </small>
              </div>
            </div>

            <!-- แนบไฟล์ -->
            <div class="mb-12">
              <label class="form-label">แนบเอกสารหลักฐาน (jpeg/jpg/png, ≤10MB/ไฟล์)</label>
              <input type="file" class="form-control" id="attachments" name="attachments[]" multiple
                     accept=".jpg,.jpeg,.png">
              <div id="filePreview" class="mt-2"></div>
              <div class="form-text">คุณสามารถลบไฟล์ออกก่อนบันทึกได้</div>
            </div>

            <!-- flag -->
            <input type="hidden" name="request[is_manual_case]" value="1">
            <input type="hidden" name="request[confirm_agreement]" value="1">
            <input type="hidden" name="request[department_id]" value="<?= h($Department->id ?? ''); ?>">
            <input type="hidden" name="request[port_province_id]" value="<?= h($Department->province ?? ''); ?>">
            <input type="hidden" name="request[port_amphur_id]" value="<?= h($Department->district ?? ''); ?>">
            <input type="hidden" name="request[license_status]" id="manual-license-status" value="none">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> บันทึกคำขอ
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
