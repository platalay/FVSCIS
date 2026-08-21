<!-- modalConfirmInspection date-->
                    <div class="modal fade" id="modalConfirmInspection" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <form id="confirmInspectionForm">
                            <div class="modal-header">
                            <h5 class="modal-title">ยืนยันวันตรวจเรือ</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                            <p><strong>วันที่นัดตรวจ:</strong> <span id="confirmedDateDisplay" class="text-primary"></span></p>
                            <input type="hidden" name="request_id" id="confirm_request_id">
                            <input type="hidden" name="original_confirmed_date" id="original_confirmed_date">
                            </div>
                            <div class="modal-footer">
                            <button id="btnSubmitConfirm" type="submit" class="btn btn-success">
                                ยืนยันเข้ารับการตรวจ
                            </button>
                            </div>
                        </form>
                        </div>
                    </div>
                    </div><!-- modalConfirmInspection date-->    