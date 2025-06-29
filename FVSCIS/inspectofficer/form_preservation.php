<?php
require_once('../../private/initialize.php');
$session->require_role(['inspectofficer']);
include("../../private/shared/headerofficer.php");
include("../../private/shared/sidebarofficer.php");
include("../../private/shared/topbarofficer.php");
$request = InspectionRequest::find_by_id($_GET["request"]);
?>

<!-- Begin Page Content -->
<div class="container-fluid">
  <h1 class="h3 mb-4 text-gray-800">ด้านโครงสร้างของเรือประมง (structer)
    <a href="form_inspect.php?id=<?= htmlspecialchars($request->id) ?>" class="btn btn-secondary">
  ← กลับไปหน้าฟอร์มตรวจสอบ
  </a>
  </h1>
  <!-- form start here -->
  
<div class="accordion" id="inspectionAccordion">
  
  
<!-- ข้อ 1 -->
  <div class="accordion-item">
  <h2 class="accordion-header" id="heading5_1">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_1"
            aria-expanded="false" aria-controls="collapse5_1">
      5.1 น้ำยาทำความสะอาด น้ำยาฆ่าเชื้อ และยาฆ่าแมลง ต้องเก็บแยกในสถานที่ที่เป็นสัดส่วน ถูกสุขลักษณะ 
          และควบคุมไม่ให้มีโอกาสปนเปื้อนในสัตว์น้ำได้
    </button>
  </h2>
  <div id="collapse5_1" class="accordion-collapse collapse" aria-labelledby="heading5_1" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-1">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_1" id="status_5_1_pass" value="pass">
            <label class="form-check-label" for="status_5_1_pass">
              ผ่าน - มีพื้นที่จัดเก็บสารเคมี เช่น ผงซักฟอก น้ำยาล้างจาน ยาฆ่าแมลง สี ที่เป็นสัดส่วน ไม่มีโอกาสปนเปื้อนกับสัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_1" id="status_5_1_fail" value="fail">
            <label class="form-check-label" for="status_5_1_fail">
              ไม่ผ่าน - ไม่มีพื้นที่จัดเก็บสารเคมี เช่น ผงซักฟอก น้ำยาล้างจาน ยาฆ่าแมลง สี ที่เป็นสัดส่วน 
              มีโอกาสปนเปื้อนกับสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control"
                    id="remark_5_1"
                    name="remark_5_1"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_2">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_2"
            aria-expanded="false" aria-controls="collapse5_2">
      5.2 เก็บบรรจุสัตว์น้ำในภาชนะบรรจุที่แข็งแรง สะอาด และไม่ซ้อนทับจนทำให้สัตว์น้ำเสียหาย
    </button>
  </h2>
  <div id="collapse5_2" class="accordion-collapse collapse" aria-labelledby="heading5_2" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-2">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_2" id="status_5_2_pass" value="pass">
            <label class="form-check-label" for="status_5_2_pass">
              ผ่าน - ภาชนะบรรจุสัตว์น้ำแต่ละอันมีขอบรองรับไม่ให้สัตว์น้ำกดทับกัน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_2" id="status_5_2_fail" value="fail">
            <label class="form-check-label" for="status_5_2_fail">
              ไม่ผ่าน - ภาชนะบรรจุสัตว์น้ำไม่แข็งแรง ในขณะขนถ่ายพบว่าใช้งานซะที่แตกหัก อยู่ในสภาพที่ไม่เหมาะกับการใช้งาน 
              ทำให้สัตว์น้ำเป็นรอย มีตำหนิ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control"
                    id="remark_5_2"
                    name="remark_5_2"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_3"
            aria-expanded="false" aria-controls="collapse5_3">
      5.3 เก็บรักษาสัตว์น้ำหลังจากการจับด้วยวิธีการที่เหมาะสมโดยเร็วที่สุด...
    </button>
  </h2>
  <div id="collapse5_3" class="accordion-collapse collapse" aria-labelledby="heading5_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-5_3" type="radio"
                   name="status_5_3" id="status_5_3_pass" value="pass"
                   data-item-code="5_3">
            <label class="form-check-label" for="status_5_3_pass">
              ผ่าน - มีน้ำแข็งเพียงพอในห้องเก็บสัตว์น้ำ ภาชนะ ถัง หรือกระบะที่มีสัตว์น้ำ / สัตว์น้ำมีความสด
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-5_3" type="radio"
                   name="status_5_3" id="status_5_3_fail" value="fail"
                   data-item-code="5_3">
            <label class="form-check-label" for="status_5_3_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_5_3" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_5_3_fail_1"
                   data-code="fail_1"
                   data-item-code="5_3"
                   data-text="ไม่ผ่าน - มีน้ำแข็งไม่เพียงพอในห้องเก็บสัตว์น้ำ ภาชนะ ถัง หรือกระบะที่มีสัตว์น้ำ">
            <label class="form-check-label" for="chk_5_3_fail_1">
              มีน้ำแข็งไม่เพียงพอในห้องเก็บสัตว์น้ำ ภาชนะ ถัง หรือกระบะที่มีสัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_5_3_fail_2"
                   data-code="fail_2"
                   data-item-code="5_3"
                   data-text="ไม่ผ่าน - สัตว์น้ำไม่สด">
            <label class="form-check-label" for="chk_5_3_fail_2">
              สัตว์น้ำไม่สด
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_5_3"
                    data-code="5_3_remark"
                    data-item-code="5_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_4"
            aria-expanded="false" aria-controls="collapse5_4">
      5.4 เก็บรักษาสัตว์น้ำอย่างถูกสุขลักษณะ และรักษาอุณหภูมิของสัตว์น้ำให้ใกล้เคียง 0 องศาเซลเซียส...
    </button>
  </h2>
  <div id="collapse5_4" class="accordion-collapse collapse" aria-labelledby="heading5_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-5_4" type="radio"
                   name="status_5_4" id="status_5_4_pass" value="pass"
                   data-item-code="5_4">
            <label class="form-check-label" for="status_5_4_pass">
              ผ่าน - มีน้ำแข็งเพียงพอต่อการเก็บรักษาสัตว์น้ำ / ในเรือแช่เย็นแข็ง อุณหภูมิของสัตว์น้ำใกล้เคียง 0–4 องศาเซลเซียส
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-5_4" type="radio"
                   name="status_5_4" id="status_5_4_fail" value="fail"
                   data-item-code="5_4">
            <label class="form-check-label" for="status_5_4_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_5_4" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_5_4_fail_1"
                   data-code="fail_1"
                   data-item-code="5_4"
                   data-text="ไม่ผ่าน - มีน้ำแข็งไม่เพียงพอต่อการเก็บรักษาสัตว์น้ำ">
            <label class="form-check-label" for="chk_5_4_fail_1">
              มีน้ำแข็งไม่เพียงพอต่อการเก็บรักษาสัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_5_4_fail_2"
                   data-code="fail_2"
                   data-item-code="5_4"
                   data-text="ไม่ผ่าน - ในเรือแช่เย็นแข็ง อุณหภูมิของสัตว์น้ำสูงกว่า 0–4 องศาเซลเซียส">
            <label class="form-check-label" for="chk_5_4_fail_2">
              ในเรือแช่เย็นแข็ง อุณหภูมิของสัตว์น้ำสูงกว่า 0–4 องศาเซลเซียส
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_5_4"
                    data-code="5_4_remark"
                    data-item-code="5_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->

<!-- ข้อ 5 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading5_5">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_5"
            aria-expanded="false" aria-controls="collapse5_5">
      5.5 วางหรือเก็บรักษาสัตว์น้ำในที่เหมาะสม หากเป็นการแช่เย็นหรือแช่แข็งต้องหลีกเลี่ยงการสัมผัสความร้อนจากแสงแดด หรือความร้อนอื่น ๆ
    </button>
  </h2>
  <div id="collapse5_5" class="accordion-collapse collapse" aria-labelledby="heading5_5" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-5">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_5" id="status_5_5_pass" value="pass">
            <label class="form-check-label" for="status_5_5_pass">
              ผ่าน - เก็บสัตว์น้ำในภาชนะที่รองรับอย่างเหมาะสม เช่น กระบะ ถัง ห้องเก็บสัตว์น้ำในเรือ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_5" id="status_5_5_fail" value="fail">
            <label class="form-check-label" for="status_5_5_fail">
              ไม่ผ่าน - เก็บสัตว์น้ำในที่ไม่เหมาะสม วางภาชนะบรรจุสัตว์น้ำบนดาดฟ้าเรือ มีน้ำแข็งน้อย ความเย็นไม่เพียงพอ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_5" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_5_5"
                    name="remark_5_5"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- จบข้อ 5 -->

<!-- ข้อ 6 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_6">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_6"
            aria-expanded="false" aria-controls="collapse5_6">
      5.6 มีบันทึกรายละเอียดของแหล่งจับหรือแหล่งที่มาของสัตว์น้ำ พร้อมเก็บไว้เพื่อการตรวจสอบ
    </button>
  </h2>
  <div id="collapse5_6" class="accordion-collapse collapse" aria-labelledby="heading5_6" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-6">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_6" id="status_5_6_pass" value="pass">
            <label class="form-check-label" for="status_5_6_pass">
              ผ่าน - มีสมุดบันทึกการทำประมง (fishing logbook) และบันทึกข้อมูลการทำประมง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_6" id="status_5_6_fail" value="fail">
            <label class="form-check-label" for="status_5_6_fail">
              ไม่ผ่าน - ไม่มีสมุดบันทึกการทำประมง (fishing logbook) หรือไม่บันทึกข้อมูลการทำประมง
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_6" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_5_6"
                    name="remark_5_6"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 6 -->

<!-- ข้อ 7 -->
 
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_7">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_7"
            aria-expanded="false" aria-controls="collapse5_7">
      5.7 ขนถ่ายสัตว์น้ำอย่างถูกสุขลักษณะ โดยหลีกเลี่ยงการใช้วัสดุอุปกรณ์ที่จะก่อให้เกิดความเสียหายแก่สัตว์น้ำ
    </button>
  </h2>
  <div id="collapse5_7" class="accordion-collapse collapse" aria-labelledby="heading5_7" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-7">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_7" id="status_5_7_pass" value="pass">
            <label class="form-check-label" for="status_5_7_pass">
              ผ่าน - ขนถ่ายสัตว์น้ำอย่างถูกสุขลักษณะ เช่น ใช้อุปกรณ์ที่ไม่ก่อให้เกิดความเสียหายแก่สัตว์น้ำ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_7" id="status_5_7_fail" value="fail">
            <label class="form-check-label" for="status_5_7_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- เงื่อนไขข้อไม่ผ่าน -->
        <div id="fail_group_5_7" style="display:none;" class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fail_5_7_1" id="fail_5_7_1">
            <label class="form-check-label" for="fail_5_7_1">
              ใช้อุปกรณ์ที่ก่อให้เกิดความเสียหายแก่สัตว์น้ำ เช่น ใช้วัสดุแหลมคม ตะขอสับที่ตัวสัตว์น้ำ
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fail_5_7_2" id="fail_5_7_2">
            <label class="form-check-label" for="fail_5_7_2">
              ขณะขนถ่ายมีสัตว์น้ำหล่นบนพื้นเรือหรือแพปลา
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_7" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_5_7"
                    name="remark_5_7"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- จบข้อ 7 -->


<!-- ข้อ 8 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_8">
    <button class="accordion-button collapsed bg-info text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_8"
            aria-expanded="false" aria-controls="collapse5_8">
      5.8 ห้องเย็นเก็บรักษาสัตว์น้ำต้องสามารถควบคุมอุณหภูมิไม่สูงกว่า 18 องศาเซลเซียสและติดตั้งเทอร์โมมิเตอร์หรืออุปกรณ์บันทึกอุณหภูมิ อย่างต่อเนื่องอัตโนมัติ
    </button>
  </h2>
  <div id="collapse5_8" class="accordion-collapse collapse" aria-labelledby="heading5_8" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-8">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_8" id="status_5_8_pass" value="pass">
            <label class="form-check-label" for="status_5_8_pass">
              ผ่าน - ติดตั้งเทอร์โมมิเตอร์หรืออุปกรณ์บันทึกอุณหภูมิ และมีอุณหภูมิในห้องเย็นต่ำกว่า 18°C
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_8" id="status_5_8_fail" value="fail">
            <label class="form-check-label" for="status_5_8_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- เงื่อนไขไม่ผ่าน -->
        <div id="fail_group_5_8" style="display:none;" class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fail_5_8_1" id="fail_5_8_1">
            <label class="form-check-label" for="fail_5_8_1">
              ไม่ติดตั้งเทอร์โมมิเตอร์หรืออุปกรณ์บันทึกอุณหภูมิ
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fail_5_8_2" id="fail_5_8_2">
            <label class="form-check-label" for="fail_5_8_2">
              มีอุณหภูมิในห้องเย็นสูงกว่า 18°C
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_8" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_5_8"
                    name="remark_5_8"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 8 -->

<!-- ข้อ 9 -->
<div class="accordion-item">
  <h2 class="accordion-header" id="heading5_9">
    <button class="accordion-button collapsed bg-info text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse5_9"
            aria-expanded="false" aria-controls="collapse5_9">
      5.9 กระบวนการทำความเย็นต้องมีประสิทธิภาพที่จะลดอุณหภูมิของสัตว์น้ำได้อย่างทั่วถึง และอุณหภูมิในสัตว์น้ำไม่สูงกว่า 18 องศาเซลเซียส
    </button>
  </h2>
  <div id="collapse5_9" class="accordion-collapse collapse" aria-labelledby="heading5_9" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-5-9">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_9" id="status_5_9_pass" value="pass">
            <label class="form-check-label" for="status_5_9_pass">
              ผ่าน - ใช้เทอร์โมมิเตอร์ตรวจวัดอุณหภูมิในสัตว์น้ำ พบว่าไม่สูงกว่า 18°C
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="radio"
                   name="status_5_9" id="status_5_9_fail" value="fail">
            <label class="form-check-label" for="status_5_9_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- เงื่อนไขไม่ผ่าน -->
        <div id="fail_group_5_9" style="display:none;" class="mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fail_5_9_1" id="fail_5_9_1">
            <label class="form-check-label" for="fail_5_9_1">
              อุณหภูมิในสัตว์น้ำสูงกว่า 18°C
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_5_9" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control" id="remark_5_9"
                    name="remark_5_9"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 9 -->


</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>

<script>
  $(document).ready(function () {
    $('.form-status-radio-5_3').on('change', function () {
      const isFail = $(this).val() === 'fail';
      $('#fail_group_5_3').toggle(isFail);
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('.form-status-radio-5_4').on('change', function () {
      const isFail = $(this).val() === 'fail';
      $('#fail_group_5_4').toggle(isFail);
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('input[name="status_5_7"]').on('change', function () {
      const isFail = $(this).val() === 'fail';
      $('#fail_group_5_7').toggle(isFail);
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('input[name="status_5_8"]').on('change', function () {
      $('#fail_group_5_8').toggle($(this).val() === 'fail');
    });
    $('input[name="status_5_9"]').on('change', function () {
      $('#fail_group_5_9').toggle($(this).val() === 'fail');
    });
  });
</script>

<?
include("../../private/shared/footerall.php");
?>