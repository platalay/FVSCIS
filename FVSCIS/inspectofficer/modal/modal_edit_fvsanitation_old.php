<!-- Modal: Edit FvSanitationCertificationOld -->
                    <div class="modal fade" id="modalFvscisOldEdit" tabindex="-1"
                        aria-labelledby="modalFvscisOldEditLabel" aria-hidden="true">
                    <!-- เลื่อนเฉพาะ body + fullscreen เมื่อ lg-down -->
                    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
                        <div class="modal-content">
                        <form id="form-fvscisold-edit" autocomplete="off" method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalFvscisOldEditLabel">แก้ไขข้อมูลใบรับรอง (เก่า)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>

                            <div class="modal-body" style="overflow-y:auto; max-height:calc(100vh - 200px);">
                            <input type="hidden" name="FvSanitationCertificationOld[id]" id="edit-id">

                            <div class="row g-3">
                               

                                <div class="col-md-3">
                                <label class="form-label">ทะเบียนเรือ</label>
                                <div class="input-group">
                                        <input type="text"
                                        class="form-control"
                                        id="edit-ship-code"
                                        name="FvSanitationCertificationOld[ship_code]"
                                        required>
                                        <button class="btn btn-outline-secondary"
                                                type="button"
                                                id="btnLookupShipEdit">
                                        <span id="btnEditText">ค้นหา</span>
                                        <span id="btnEditSpin" class="d-none">
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        </span>
                                        </button>
                                </div>
                                <div class="form-text">
                                        กรณีมีข้อมูลใน eLicense สามารถกดค้นหาเพื่อตั้งค่าข้อมูลอัตโนมัติ
                                </div>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_name]" id="edit-vessel-name" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเจ้าของเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[owner_name]" id="edit-owner-name">
                                </div>

                                <div class="col-md-3 elicense-only">
                                <label class="form-label">หมายเลข/สัญลักษณ์เรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_mark]" id="edit-vessel-mark">
                                </div>

                                <div class="col-md-3 elicense-only">
                                <label class="form-label">เลขที่ใบอนุญาต</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[license_number]" id="edit-license-number">
                                </div>

                                <div class="col-md-3 elicense-only">
                                <label class="form-label">ชนิดเครื่องมือทำการประมง</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[gear_type]" id="edit-gear-type">
                                </div>

                                

                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบรับรอง</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[certificate_number]" id="edit-certificate-number" required>
                                </div>

                                <!-- วันที่ -->
                                <div class="col-md-3">
                                <label class="form-label">วันที่ยื่นคำขอ</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[request_date]" id="edit-request-date">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันที่ลงนาม</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[signature_date]" id="edit-signature-date">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันที่มีผล</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[effective_date]" id="edit-effective-date" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">วันหมดอายุ</label>
                                <input type="date" class="form-control"
                                        name="FvSanitationCertificationOld[expiration_date]" id="edit-expiration-date" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">สถานะใบรับรอง</label>
                                <select class="form-select"
                                        name="FvSanitationCertificationOld[certificate_status]" id="edit-certificate-status" required>
                                    <option value="" disabled>-- เลือกสถานะ --</option>
                                    <option value="สร. 3">สร. 3</option>
                                    <option value="สร. 3 ชั่วคราว">สร. 3 ชั่วคราว</option>
                                    <option value="สร. 3 EU">สร. 3 EU</option>
                                    <option value="สร. 3 EU ชั่วคราว">สร. 3 EU ชั่วคราว</option>
                                    <option value="ไม่ผ่าน">ไม่ผ่าน</option>
                                </select>
                                </div>

                                <!-- Hidden (หน่วยงาน จาก record เดิม) -->
                                <input type="hidden" name="FvSanitationCertificationOld[evaluation_agency]" id="edit-evaluation-agency">
                                <input type="hidden" name="FvSanitationCertificationOld[signing_unit]" id="edit-signing-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[responsible_unit]" id="edit-responsible-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[type]" id="edit-type" value="0">
                                <input type="hidden" name="FvSanitationCertificationOld[license_status]" id="edit-license-status">
                                <!-- สรุปหน่วยงาน -->
                                <?php
                                $eval  = $evaluation_agency  ?? '';
                                $sign  = $signing_unit       ?? '';
                                $resp  = $responsible_unit   ?? '';
                                ?>
                                <div class="col-12">
                                <div class="border rounded p-3 mb-2 bg-light">
                                    <div class="row g-2 small">
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยประเมิน</div>
                                        <div><?= $eval !== '' ? h($eval) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยลงนาม</div>
                                        <div><?= $sign !== '' ? h($sign) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="fw-semibold">หน่วยงานรับผิดชอบ</div>
                                        <div><?= $resp !== '' ? h($resp) : '<span class="text-muted">ไม่ระบุ</span>' ?></div>
                                    </div>
                                    </div>
                                </div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">เหตุผลชั่วคราว</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[temporary_reason]" id="edit-temporary-reason">
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">หมายเหตุ (remark)</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[remark]" id="edit-remark">
                                </div>
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
                                        <input type="file" id="certAttachmentsEdit" name="attachments[]" multiple
                                        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf"
                                        class="form-control form-control-sm">
                                </div>
                                </div>

                                <!-- พรีวิวไฟล์ที่ *จะอัปโหลดใหม่* -->
                                <div class="row g-3 mt-2" id="selectedFilesEdit"></div>

                                <!-- รายการไฟล์ที่มีอยู่เดิมในระบบ -->
                                <div class="mt-3">
                                <div class="fw-semibold mb-2">ไฟล์ที่แนบไว้แล้ว</div>
                                <div class="row g-3" id="existingFiles"></div>
                                </div>
                                </div>
                                </div>

                                <!-- /input file old new -->

                            </div><!-- /.row -->
                            </div><!-- /.modal-body -->

                            <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /Modal -->