<!-- modalviewOldModal -->
                    <!-- Modal: รายละเอียดใบรับรองสุขอนามัยเรือ (ข้อมูลเก่า) -->
                    <div class="modal fade" id="oldCertificationModal" tabindex="-1" aria-labelledby="oldCertLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="oldCertLabel">รายละเอียดผลการประเมินมาตรฐานสุขอนามัยเรือ</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="ปิด"></button>
                        </div>

                        <div class="modal-body">
                            <!-- Loading -->
                            <div id="oldCertLoading" class="text-center my-4" style="display:none;">
                            <div class="spinner-border" role="status"></div>
                            <div class="mt-2">กำลังโหลดข้อมูล...</div>
                            </div>

                            <!-- Error -->
                            <div id="oldCertError" class="alert alert-danger" style="display:none;"></div>

                            <!-- เนื้อหา -->
                            <div id="oldCertContent" style="display:none;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                <label class="form-label text-muted">ชื่อเรือ</label>
                                <div id="oc_vessel_name" class="fw-semibold"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">ทะเบียนเรือ</label>
                                <div id="oc_ship_code" class="fw-semibold"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หมายเลขเครื่องหมายเรือ</label>
                                <div id="oc_vessel_mark"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">เลขที่ใบอนุญาตทำการประมง</label>
                                <div id="oc_license_number"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">ชนิดเครื่องมือทำการประมง</label>
                                <div id="oc_gear_type"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">ชื่อเจ้าของเรือ</label>
                                <div id="oc_owner_name"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">เลขที่ใบรับรอง</label>
                                <div id="oc_certificate_number"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">สถานะเรือ</label>
                                <div id="oc_vessel_status"></div>
                                </div>

                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่ยื่นคำขอ</label>
                                <div id="oc_request_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่ลงนาม</label>
                                <div id="oc_signature_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันที่มีผล</label>
                                <div id="oc_effective_date"></div>
                                </div>
                                <div class="col-md-3">
                                <label class="form-label text-muted">วันหมดอายุ</label>
                                <div id="oc_expiration_date"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยประเมิน</label>
                                <div id="oc_evaluation_agency"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยลงนาม</label>
                                <div id="oc_signing_unit"></div>
                                </div>

                                <div class="col-md-6">
                                <label class="form-label text-muted">หน่วยรับผิดชอบ</label>
                                <div id="oc_responsible_unit"></div>
                                </div>
                                <div class="col-md-6">
                                <label class="form-label text-muted">สถานะใบรับรอง</label>
                                <div id="oc_certificate_status"></div>
                                </div>

                                <div class="col-12">
                                <label class="form-label text-muted">เหตุผลออกใบรับรองชั่วคราว</label>
                                <div id="oc_temporary_reason"></div>
                                </div>
                                <div class="col-12">
                                <label class="form-label text-muted">หมายเหตุ</label>
                                <div id="oc_remark"></div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        </div>
                        </div>
                    </div>
                    </div>

                    <!-- /modalviewOldModal -->