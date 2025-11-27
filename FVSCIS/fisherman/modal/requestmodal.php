<!-- Modal: Request Inspection -->
                    <div class="modal fade" id="requestInspectionModal" tabindex="-1" aria-labelledby="requestInspectionModalLabel" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <form id="requestInspectionForm" method="post" action="ajax/request_inspection.php">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="requestInspectionModalLabel">ยื่นคำขอตรวจเรือ</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                              <!-- รายละเอียดเรือ -->
                              <div class="mb-3">
                                <strong>เลขทะเบียนเรือ:</strong> <span id="modal-ship-code"></span><br>
                                <strong>ชื่อเรือ:</strong> <span id="modal-vessel-name"></span><br>
                                <strong>ขนาดตันกรอส:</strong> <span id="modal-vessel-ton"></span> ตัน<br>
                                <strong>พื้นที่ทำการประมง:</strong> <span id="modal-fishing-area"></span>
                              </div>

                              <!-- ข้อมูลผู้ยื่น -->
                              <div class="mb-3">
                                <input type="hidden" name="request[ship_code]" id="hidden_ship_code">
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

                              <!-- เลือกจังหวัด อำเภอ ตำบล ท่าเรือ -->
                              <div class="row mb-3">
                                <div class="col-md-3">
                                  <label for="port_province_id" class="form-label">จังหวัด</label>
                                  <select name="request[port_province_id]" id="port_province_id" class="form-select" required>
                                    <option value="">-- เลือกจังหวัด --</option>
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label for="port_amphur_id" class="form-label">อำเภอ</label>
                                  <select name="request[port_amphur_id]" id="port_amphur_id" class="form-select" required>
                                    <option value="">-- เลือกอำเภอ --</option>
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label for="port_tambon_id" class="form-label">ตำบล</label>
                                  <select name="request[port_tambon_id]" id="port_tambon_id" class="form-select" required>
                                    <option value="">-- เลือกตำบล --</option>
                                  </select>
                                </div>
                                <div class="col-md-3">
                                  <label for="port_license_no" class="form-label">ท่าเรือ</label>
                                  <select name="request[port_license_no]" id="port_license_no" class="form-select" required>
                                    <option value="">-- เลือกท่าเรือ --</option>
                                  </select>
                                </div>
                              </div>

                              <div class="mb-3">
                                <label for="department_id" class="form-label">หน่วยงานที่ยื่นคำขอ</label>
                                <select name="request[department_id]" id="department_id" class="form-select" required>
                                  <option value="">-- เลือกหน่วยงาน --</option>
                                  <?php 
                                    $Departments = Department::find_all();
                                    foreach ($Departments as $Department): ?>
                                      <option value="<?= $Department->id ?>" data-province-id="<?= $Department->province ?>">
                                        <?= $Department->name ?>
                                      </option>
                                  <?php endforeach; ?>
                                </select>
                              </div>

                              <!-- วันที่ต้องการตรวจ -->
                              <div class="row mb-3">
                                <div class="col">
                                  <label for="inspect_date_start" class="form-label">วันที่เริ่มต้องการตรวจ</label>
                                  <input type="date" name="request[inspect_date_start]" id="inspect_date_start" class="form-control" required>
                                </div>
                                <div class="col">
                                  <label for="inspect_date_end" class="form-label">ถึงวันที่</label>
                                  <input type="date" name="request[inspect_date_end]" id="inspect_date_end" class="form-control" required>
                                </div>
                              </div>

                              <!-- ✅ รูปแบบการตรวจ -->
                              <div class="mb-3 p-3 border rounded">
                                <div class="form-check">
                                  <!-- hidden เก็บค่าแบบฟอร์ม: 1=ทั่วไป, 2=EU -->
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

                              <!-- ✅ เรือห้องเย็น / ระบบทำความเย็น -->
                              <div class="mb-3 p-3 border rounded">
                                <div class="form-check">
                                  <!-- hidden เก็บค่า: 0/1 -->
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

                              <!-- Checkbox ยืนยัน -->
                              <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="request[confirm_agreement]" id="confirm_agreement" required>
                                <label class="form-check-label" for="confirm_agreement">
                                  ข้าพเจ้ายืนยันว่าข้อมูลที่กรอกถูกต้องและยินยอมให้ใช้ข้อมูลนี้ในการตรวจเรือ
                                </label>
                              </div>

                              <!-- Hidden vessel fields -->
                              <input type="hidden" name="request[vessel_name]" id="hidden_vessel_name">
                              <input type="hidden" name="request[license_status]" id="hidden_vessel_name">
                            </div>

                            <div class="modal-footer">
                              <button type="submit" class="btn btn-primary">ยื่นคำขอ</button>
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                    <!-- /Modal -->