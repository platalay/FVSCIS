<!-- Log Modal -->
<div class="modal fade" id="logModal" tabindex="-1" role="dialog" aria-labelledby="logModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title" id="logModalLabel">ประวัติการดำเนินการคำขอตรวจเรือ <span id="modalVesselName"></span></h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead class="thead-light">
              <tr>
                <th style="width: 150px;">วันเวลา</th>
                <th style="width: 180px;">การดำเนินการ</th>
                <th style="width: 160px;">ผู้ดำเนินการ</th>
                <th>หมายเหตุ</th>
              </tr>
            </thead>
            <tbody id="logModalBody">
              <!-- เติมด้วย JS -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
      </div>

    </div>
  </div>
</div>
