<!-- modal/modal_edit_manual_case.php -->
<div class="modal fade" id="modalEditManualCase" tabindex="-1"
     aria-labelledby="modalEditManualCaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formEditManualCase" enctype="multipart/form-data">
        <div class="modal-header text-dark">
          <h5 class="modal-title" id="modalEditManualCaseLabel">
            <i class="fas fa-edit me-2"></i> แก้ไขคำขอสุขอนามัย (สร้างโดยเจ้าหน้าที่)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"
                  aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <!-- เก็บ id ของคำขอ -->
            <input type="hidden" name="request[id]" id="edit-request-id">

            <!-- ทะเบียนเรือ + ปุ่มค้นหา -->
            <div class="col-md-3">
              <label class="form-label">ทะเบียนเรือ</label>
              <div class="input-group">
                <input type="text"
                       class="form-control"
                       id="edit-ship-code"
                       name="request[ship_code]"
                       required>
                <button class="btn btn-outline-secondary"
                        type="button"
                        id="btnLookupShipEdit">
                  <span id="btnEditText">ค้นหา</span>
                  <span id="btnEditSpin" class="d-none">
                    <span class="spinner-border spinner-border-sm"
                          role="status" aria-hidden="true"></span>
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
                     id="edit-vessel-name"
                     name="request[vessel_name]"
                     required>
            </div>

            <!-- ชื่อเจ้าของเรือ -->
            <div class="col-md-6">
              <label class="form-label">ชื่อเจ้าของเรือ</label>
              <input type="text"
                     class="form-control"
                     id="edit-owner-name"
                     name="request[owner_name]"
                     required>
            </div>

            <!-- สัญลักษณ์เรือ / เลขที่ใบอนุญาต / เครื่องมือ -->
            <div class="col-md-4">
              <label class="form-label">หมายเลขสัญลักษณ์เรือ</label>
              <input type="text"
                     class="form-control"
                     id="edit-vessel-mark"
                     name="request[vessel_mark]">
            </div>

            <div class="col-md-4">
              <label class="form-label">เลขที่ใบอนุญาตทำการประมง (ถ้ามี)</label>
              <input type="text"
                     class="form-control"
                     id="edit-license-number"
                     name="request[license_number]"
                     placeholder="ยังไม่มีใบอนุญาตไม่ต้องกรอก">
            </div>

            <div class="col-md-4">
              <label class="form-label">ชนิดเครื่องมือทำการประมง (ถ้ามี)</label>
              <input type="text"
                     class="form-control"
                     id="edit-gear-type"
                     name="request[gear_type]"
                     placeholder="ยังไม่มีใบอนุญาตไม่ต้องกรอก">
            </div>

            <!-- หน่วยงาน (ดูอย่างเดียว) -->
            <div class="col-md-6">
              <label class="form-label">หน่วยงาน</label>
              <input type="text" class="form-control"
                     value="<?= h($Department->name ?? ''); ?>" readonly>
            </div>

            <!-- ตำบล / ท่าเรือ -->
            <div class="col-md-3">
              <label for="edit-port_tambon_id" class="form-label">ตำบล</label>
              <select name="request[port_tambon_id]" id="edit-port_tambon_id"
                      class="form-select" required>
                <option value="">-- เลือกตำบล --</option>
                <?php
                $subdistricts = Tambon::find_by_amphur_id($Department->district);
                if (!empty($subdistricts)) {
                  foreach ($subdistricts as $subdistrict) {
                    echo '<option value="' . h($subdistrict->id) . '">'
                       . h($subdistrict->name) . '</option>';
                  }
                }
                ?>
              </select>
            </div>

            <div class="col-md-3">
              <label for="edit-port_license_no" class="form-label">ท่าเรือ</label>
              <select name="request[port_license_no]" id="edit-port_license_no"
                      class="form-select" required>
                <option value="">-- เลือกท่าเรือ --</option>
              </select>
            </div>

            <!-- วันที่ต้องการตรวจ -->
            <div class="col-md-3">
              <label for="edit-confirmed_inspect_date"
                     class="form-label">วันที่ต้องการตรวจ</label>
              <input type="date"
                     name="request[confirmed_inspect_date]"
                     id="edit-confirmed_inspect_date"
                     class="form-control" required>
            </div>

            <!-- เบอร์โทร -->
            <div class="col-md-9">
              <label for="edit-contact-phone"
                     class="form-label">หมายเลขโทรศัพท์ที่ติดต่อได้</label>
              <input type="text"
                     name="request[contact_phone]"
                     id="edit-contact-phone"
                     class="form-control"
                     required
                     maxlength="10"
                     inputmode="numeric"
                     autocomplete="tel"
                     placeholder="เช่น 0891234567">
            </div>

            <!-- รูปแบบการตรวจ -->
            <div class="col-12">
              <div class="mb-3 p-3 border rounded">
                <div class="form-check">
                  <input type="hidden"
                         name="request[inspection_form_type]"
                         id="edit-inspection-form-type" value="1">
                  <input class="form-check-input" type="checkbox"
                         id="edit-eu-cert-checkbox">
                  <label class="form-check-label fw-semibold"
                         for="edit-eu-cert-checkbox">
                    ขอหนังสือรับรองมาตรฐานด้านสุขอนามัยในเรือประมงเพื่อการส่งออกสินค้าสัตว์น้ำไปสหภาพยุโรป
                  </label>
                </div>
                <small class="text-muted d-block mt-2">
                  ไม่เลือก = ตรวจทั่วไป (แบบที่ 1) | เลือก = ตรวจเพื่อ EU Export (แบบที่ 2)
                </small>
              </div>
            </div>

            <!-- ห้องเย็น -->
            <div class="col-12">
              <div class="mb-3 p-3 border rounded">
                <div class="form-check">
                  <input type="hidden"
                         name="request[cold_room_flag]"
                         id="edit-cold-room-flag" value="0">
                  <input class="form-check-input" type="checkbox"
                         id="edit-cold-room-checkbox">
                  <label class="form-check-label fw-semibold"
                         for="edit-cold-room-checkbox">
                    เรือห้องเย็น / มีระบบทำความเย็น
                  </label>
                </div>
                <small class="text-muted d-block mt-2">
                  ถ้าเลือก ระบบจะเพิ่มหัวข้อ 5.8–5.9 ในรายการตรวจหมวดการเก็บรักษาสัตว์น้ำ
                </small>
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

            <!-- flag เดิม -->
            <input type="hidden" name="request[is_manual_case]" value="1">
            <input type="hidden" name="request[confirm_agreement]" value="1">
            <input type="hidden" name="request[department_id]"
                   value="<?= h($Department->id ?? ''); ?>">
            <input type="hidden" name="request[port_province_id]"
                   value="<?= h($Department->province ?? ''); ?>">
            <input type="hidden" name="request[port_amphur_id]"
                   value="<?= h($Department->district ?? ''); ?>">

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary"
                  data-bs-dismiss="modal">ยกเลิก</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
