<!-- modalRequestDetail -->   
                    <div class="modal fade" id="modalRequestDetail" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <form id="confirmInspectionForm">
                            <div class="modal-header">
                            <h5 class="modal-title">รายละเอียดคำขอตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="modalRequestBody">
                            <!-- 📌 JS จะโหลดข้อมูลมาใส่ตรงนี้ -->

                            </div>
                            <div class="modal-footer">
                            <input type="hidden" name="request_id" id="confirm_request_id">
                            <input type="hidden" name="original_confirmed_date" id="original_confirmed_date">
                            <div class="row g-2 align-items-center mb-3">
                            <div class="col-auto">
                                <label for="confirmed_date" class="col-form-label text-secondary fw-semibold">
                                กรุณาเลือกวันนัดตรวจ
                                </label>
                            </div>
                            <div class="col">
                                <input type="date" id="confirmed_date" name="confirmed_date" class="form-control" required>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary" id="btnConfirmDate">
                                ยืนยัน
                                </button>
                            </div>
                            </div>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div>
                    <!-- modalRequestDetail -->    
