<!-- Modal: Edit FvSanitationCertificationOld -->
                    <div class="modal fade" id="modalFvscisOldEdit" tabindex="-1"
                        aria-labelledby="modalFvscisOldEditLabel" aria-hidden="true">
                    <!-- เลื่อนเฉพาะ body + fullscreen เมื่อ lg-down -->
                    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
                        <div class="modal-content">
                        <form id="form-fvscisold-edit" autocomplete="off">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalFvscisOldEditLabel">แก้ไขข้อมูลใบรับรอง (เก่า)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>

                            <div class="modal-body">
                            <input type="hidden" name="FvSanitationCertificationOld[id]" id="edit-id">

                            <div class="row g-3">
                                <div class="col-md-3">
                                <label class="form-label">รหัสเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[ship_code]" id="edit-ship-code" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_name]" id="edit-vessel-name" required>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">หมายเลข/สัญลักษณ์เรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[vessel_mark]" id="edit-vessel-mark">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบอนุญาต</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[license_number]" id="edit-license-number">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชนิดเครื่องมือทำการประมง</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[gear_type]" id="edit-gear-type">
                                </div>

                                <div class="col-md-3">
                                <label class="form-label">ชื่อเจ้าของเรือ</label>
                                <input type="text" class="form-control"
                                        name="FvSanitationCertificationOld[owner_name]" id="edit-owner-name">
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
                                    <option value="ไม่ผ่าน">ไม่ผ่าน</option>
                                </select>
                                </div>

                                <!-- Hidden (หน่วยงาน จาก record เดิม) -->
                                <input type="hidden" name="FvSanitationCertificationOld[evaluation_agency]" id="edit-evaluation-agency">
                                <input type="hidden" name="FvSanitationCertificationOld[signing_unit]" id="edit-signing-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[responsible_unit]" id="edit-responsible-unit">
                                <input type="hidden" name="FvSanitationCertificationOld[type]" id="edit-type" value="0">

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