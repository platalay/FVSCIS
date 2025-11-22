<!-- Modal: Add FvSanitationCertificationOld -->
                    <div class="modal fade" id="modalFvscisOldAdd" tabindex="-1" aria-labelledby="modalFvscisOldAddLabel" aria-hidden="true">
                    <!-- ✅ เลื่อนเฉพาะ body และเต็มจอเมื่อจอเล็ก -->
                    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
                        <div class="modal-content">
                        <form id="form-fvscisold-add" autocomplete="off" method="post" enctype="multipart/form-data">
                            <div class="modal-header">
                            <h5 class="modal-title" id="modalFvscisOldAddLabel">เพิ่มข้อมูลใบรับรอง(manual)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                            </div>

                            <!-- ✅ เผื่อ fallback ใส่ style overflow:auto ด้วย (ไม่รบกวน CSS ข้างบน) -->
                            <div class="modal-body" style="overflow-y:auto; max-height:calc(100vh - 200px);">
                            <div class="row g-3">

                                <!-- 2 : ship_code + ปุ่มค้นหา -->
                                <div class="col-md-3">
                                <label class="form-label">ทะเบียนเรือ</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="FvSanitationCertificationOld[ship_code]" id="fv-ship-code" placeholder="" required>
                                    <button class="btn btn-outline-secondary" type="button" id="btnLookupShip">
                                    <span class="d-inline" id="btnText">ค้นหา</span>
                                    <span class="spinner-border spinner-border-sm d-none" id="btnSpin" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                                </div>

                                <!-- 1 -->
                                <div class="col-md-3">
                                <label class="form-label">ชื่อเรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[vessel_name]" id="fv-vessel-name" required>
                                </div>

                                <!-- 3 -->
                                <div class="col-md-3">
                                <label class="form-label">หมายเลข/สัญลักษณ์เรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[vessel_mark]" id="fv-vessel-mark" required>
                                </div>

                                <!-- 4 -->
                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบอนุญาต</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[license_number]" id="fv-license-number" required>
                                </div>

                                <!-- 5 -->
                                <div class="col-md-3">
                                <label class="form-label">ชนิดเครื่องมือทำการประมง</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[gear_type]" id="fv-gear-type" required>
                                </div>

                                <!-- 6 -->
                                <div class="col-md-3">
                                <label class="form-label">ชื่อเจ้าของเรือ</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[owner_name]" id="fv-owner-name" required>
                                </div>

                                <!-- 7 -->
                                <div class="col-md-3">
                                <label class="form-label">เลขที่ใบรับรอง</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[certificate_number]" required>
                                </div>

                                <!-- วันที่ -->
                                <div class="col-md-3">
                                <label class="form-label">วันที่ยื่นคำขอ</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[request_date]" required>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันที่ลงนาม</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[signature_date]" required>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันที่มีผล</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[effective_date]" required>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label">วันหมดอายุ</label>
                                <input type="date" class="form-control" name="FvSanitationCertificationOld[expiration_date]" required>
                                </div>

                                <!-- ซ่อนค่าอ้างอิง -->
                                <input type="hidden" name="FvSanitationCertificationOld[evaluation_agency]" value="<?= h($Officer->departments_id ?? '') ?>">
                                <input type="hidden" name="FvSanitationCertificationOld[signing_unit]"        value="<?= h($department->parent_department ?? '') ?>">
                                <input type="hidden" name="FvSanitationCertificationOld[responsible_unit]"   value="<?= h($departmentgroup->responsible_unit ?? '') ?>">

                                <!-- สถานะ -->
                                <div class="col-md-3">
                                <label class="form-label">สถานะใบรับรอง (certificate_status)</label>
                                <select class="form-select" name="FvSanitationCertificationOld[certificate_status]" required>
                                    <option value="" selected disabled>-- เลือกสถานะ --</option>
                                    <option value="สร. 3">สร. 3</option>
                                    <option value="สร. 3 ชั่วคราว">สร. 3 ชั่วคราว</option>
                                    <option value="สร. 3 EU">สร. 3 EU</option>
                                    <option value="สร. 3 EU ชั่วคราว">สร. 3 EU ชั่วคราว</option>
                                    <option value="ไม่ผ่าน">ไม่ผ่าน</option>
                                </select>
                                </div>

                                <!-- อื่น ๆ -->
                                <div class="col-md-6">
                                <label class="form-label">เหตุผลชั่วคราว</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[temporary_reason]">
                                </div>

                                <div class="col-md-6">
                                <label class="form-label">หมายเหตุ (remark)</label>
                                <input type="text" class="form-control" name="FvSanitationCertificationOld[remark]">
                                </div>

                                <!-- แสดงผลในโมดัล -->
                                <?php
                                $eval = $evaluation_agency ?? '';
                                $sign = $signing_unit ?? '';
                                $resp = $responsible_unit ?? '';
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
                                <div class="col-12">
                                <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">แนบเอกสารประกอบ</label>

                                    <input type="file"
                                    id="certAttachments"
                                    name="attachments[]"
                                    class="form-control"
                                    multiple
                                    accept=".jpg,.jpeg,.png,.webp,.gif,.pdf">

                                    <small class="text-muted">รองรับ .jpg .png .webp .gif .pdf แนบได้หลายไฟล์</small>

                                    <!-- กล่องพรีวิวไฟล์ -->
                                    <div id="selectedFiles" class="row g-2 mt-3"></div>
                                </div>
                                </div>
                                </div>


                            </div><!-- /.row -->
                            </div><!-- /.modal-body -->

                            <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- /Modal -->   