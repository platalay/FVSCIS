<!-- Modal: รายละเอียดคำขอ -->
<div class="modal fade" id="requestDetailModal" tabindex="-1" role="dialog" aria-labelledby="requestDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="requestDetailModalLabel">รายละเอียดคำขอตรวจเรือ</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">

        <!-- ข้อมูลเรือ -->
        <h6 class="mb-2">ข้อมูลเรือประมง</h6>
        <table class="table table-sm table-borderless mb-3">
          <tr>
            <th class="w-25">เลขทะเบียนเรือ</th>
            <td><span id="detail-ship-code">-</span></td>
          </tr>
          <tr>
            <th>ชื่อเรือ</th>
            <td><span id="detail-vessel-name">-</span></td>
          </tr>
          <tr>
            <th>ขนาดตันกรอส</th>
            <td><span id="detail-vessel-ton">-</span></td>
          </tr>
          <tr>
            <th>พื้นที่ทำการประมง</th>
            <td><span id="detail-fishing-area">-</span></td>
          </tr>
        </table>

        <!-- ข้อมูลคำขอ -->
        <h6 class="mb-2">ข้อมูลคำขอตรวจ</h6>
        <table class="table table-sm table-borderless mb-3">
          <tr>
            <th>วันที่ยื่นคำขอ</th>
            <td><span id="detail-created-at">-</span></td>
          </tr>
          <tr>
            <th>หมายเลขโทรศัพท์ติดต่อ</th>
            <td><span id="detail-contact-phone">-</span></td>
          </tr>
          <tr>
            <th>รูปแบบการตรวจ</th>
            <td><span id="detail-inspection-type">-</span></td>
          </tr>
          <tr>
            <th>เรือห้องเย็น / มีระบบทำความเย็น</th>
            <td><span id="detail-cold-room">-</span></td>
          </tr>
        </table>

        <!-- ท่าเรือและหน่วยงาน -->
        <h6 class="mb-2">ท่าเทียบเรือและหน่วยงานที่ยื่นคำขอ</h6>
        <table class="table table-sm table-borderless mb-3">
          <tr>
            <th class="w-25">จังหวัด</th>
            <td><span id="detail-port-province">-</span></td>
          </tr>
          <tr>
            <th>อำเภอ / ตำบล</th>
            <td><span id="detail-port-amphur-tambon">-</span></td>
          </tr>
          <tr>
            <th>ท่าเทียบเรือ</th>
            <td><span id="detail-port-name">-</span></td>
          </tr>
          <tr>
            <th>หน่วยงานที่รับคำขอ</th>
            <td><span id="detail-department">-</span></td>
          </tr>
        </table>

        <!-- วันนัดตรวจและสถานะ -->
        <h6 class="mb-2">วันนัดตรวจและสถานะ</h6>
        <table class="table table-sm table-borderless">
          <tr>
            <th class="w-25">ช่วงวันที่ต้องการตรวจ</th>
            <td><span id="detail-inspect-range">-</span></td>
          </tr>
          <tr>
            <th>วันที่เจ้าหน้าที่นัดตรวจ</th>
            <td><span id="detail-confirmed-inspect-date">-</span></td>
          </tr>
          <tr>
            <th>สถานะปัจจุบัน</th>
            <td><span id="detail-status-badge">-</span></td>
          </tr>
        </table>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
      </div>

    </div>
  </div>
</div>
