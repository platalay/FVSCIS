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
  <!-- ข้อ 1.1 -->
  <div class="accordion-item">
    <h2 class="accordion-header" id="heading1_1">
      <button class="accordion-button collapsed bg-primary text-white" type="button"
              data-bs-toggle="collapse" data-bs-target="#collapse1_1"
              aria-expanded="false" aria-controls="collapse1_1">
        1.1 ห้องเก็บรักษาสัตว์น้ำอยู่ในสภาพดี สะอาด มีขนาดเหมาะสมเพียงพอ
      </button>
    </h2>
    <div id="collapse1_1" class="accordion-collapse collapse" aria-labelledby="heading1_1" data-bs-parent="#inspectionAccordion">
      <div class="accordion-body">
        <form id="form-1-1">
          <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

          <!-- ผ่าน / ไม่ผ่าน -->
          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input form-status-radio" type="radio"
                     name="status_1_1" id="status_1_1_pass" value="pass"
                     data-code="1_1_status" data-item-code="1_1">
              <label class="form-check-label" for="status_1_1_pass">ผ่าน</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input form-status-radio" type="radio"
                     name="status_1_1" id="status_1_1_fail" value="fail"
                     data-code="1_1_status" data-item-code="1_1">
              <label class="form-check-label" for="status_1_1_fail">ไม่ผ่าน</label>
            </div>
          </div>

          <!-- Checklist (แสดงเฉพาะเมื่อไม่ผ่าน) -->
          <div id="fail_group_1_1" class="border p-3 mb-3 bg-light" style="display: none;">
            <div class="form-check mb-2">
              <input class="form-check-input checklist-item" type="checkbox"
                     id="chk_1_1_fail_1"
                     data-code="fail_1"
                     data-item-code="1_1"
                     data-text="ไม่ผ่าน - ผนังห้องผุกร่อน ชำรุด มีสนิม ผนังห้องด้านบนสภาพปรก">
              <label class="form-check-label" for="chk_1_1_fail_1">
                ผนังห้องผุกร่อน ชำรุด มีสนิม ผนังห้องด้านบนสภาพปรก
              </label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input checklist-item" type="checkbox"
                     id="chk_1_1_fail_2"
                     data-code="fail_2"
                     data-item-code="1_1"
                     data-text="ไม่ผ่าน - ห้องมีขนาดไม่เพียงพอ เช่น มีสัตว์น้ำกองอยู่นอกห้อง">
              <label class="form-check-label" for="chk_1_1_fail_2">
                ห้องมีขนาดไม่เพียงพอ เช่น มีสัตว์น้ำกองอยู่นอกห้อง
              </label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input checklist-item" type="checkbox"
                     id="chk_1_1_fail_3"
                     data-code="fail_3"
                     data-item-code="1_1"
                     data-text="ไม่ผ่าน - พบเศษซากสัตว์น้ำที่ค้างในห้อง">
              <label class="form-check-label" for="chk_1_1_fail_3">
                พบเศษซากสัตว์น้ำที่ค้างในห้อง
              </label>
            </div>
          </div>

          <!-- หมายเหตุ -->
          <div class="mb-3">
            <label for="remark_1_1" class="form-label">หมายเหตุ (ถ้ามี):</label>
            <textarea class="form-control checklist-remark"
                      id="remark_1_1"
                      data-code="1_1_remark"
                      data-item-code="1_1"
                      placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
          </div>
        </form>
      </div>
    </div>
  </div>

<!-- จบข้อ 1 -->


<!-- ข้อ 2 -->
 <div class="accordion-item">
    <h2 class="accordion-header" id="heading1_2">
      <button class="accordion-button collapsed bg-primary text-white" type="button"
              data-bs-toggle="collapse" data-bs-target="#collapse1_2"
              aria-expanded="false" aria-controls="collapse1_2">
        1.2 มีโครงสร้างอย่างเหมาะสมโดยมีซอกมุมน้อยที่สุด เพื่อให้ง่ายต่อการรักษาความสะอาด
      </button>
    </h2>
    <div id="collapse1_2" class="accordion-collapse collapse" aria-labelledby="heading1_2" data-bs-parent="#inspectionAccordion">
      <div class="accordion-body">
        <form id="form-1-2">
          <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

          <!-- ผ่าน / ไม่ผ่าน -->
          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input checklist-radio" type="radio"
                     name="status_1_2" id="status_1_2_pass"
                     data-code="1_2_status"
                     data-item-code="1_2"
                     value="pass">
              <label class="form-check-label" for="status_1_2_pass">
                ผ่าน - ไม่มีมุมอับ ไม่มีสิ่งของขวาง
              </label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input checklist-radio" type="radio"
                     name="status_1_2" id="status_1_2_fail"
                     data-code="1_2_status"
                     data-item-code="1_2"
                     value="fail">
              <label class="form-check-label" for="status_1_2_fail">
                ไม่ผ่าน - มีมุมอับ และพื้นที่สกปรก
              </label>
            </div>
          </div>

          <!-- หมายเหตุ -->
          <div class="mb-3">
            <label for="remark_1_2" class="form-label">หมายเหตุ (ถ้ามี):</label>
            <textarea class="form-control checklist-remark"
                      id="remark_1_2"
                      data-code="1_2_remark"
                      data-item-code="1_2"
                      placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
          </div>
        </form>
      </div>
    </div>
  </div>


<!-- จบข้อ 2 -->


<!-- ข้อ 3 -->

<div class="accordion-item">
  <h2 class="accordion-header" id="heading1_3">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_3"
            aria-expanded="false" aria-controls="collapse1_3">
      1.3 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำออกแบบอย่างเหมาะสม ถูกสุขลักษณะ...
    </button>
  </h2>
  <div id="collapse1_3" class="accordion-collapse collapse" aria-labelledby="heading1_3" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-3">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_3" id="status_1_3_pass" value="pass"
                   data-code="1_3_status" data-item-code="1_3">
            <label class="form-check-label" for="status_1_3_pass">
              ผ่าน - โครงสร้างเรือ การออกแบบมีความเหมาะสม แบ่งสัดส่วนชัดเจน
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio" type="radio"
                   name="status_1_3" id="status_1_3_fail" value="fail"
                   data-code="1_3_status" data-item-code="1_3">
            <label class="form-check-label" for="status_1_3_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist (แสดงเมื่อไม่ผ่าน) -->
        <div id="fail_group_1_3" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_3_fail_1"
                   data-code="fail_1"
                   data-item-code="1_3"
                   data-text="ไม่ผ่าน - มีคราบน้ำมันปนเปื้อนจากเครื่องยนต์ หรือเครื่องจักรช่วยทำการประมง">
            <label class="form-check-label" for="chk_1_3_fail_1">
              มีคราบน้ำมันปนเปื้อนจากเครื่องยนต์ หรือเครื่องจักรช่วยทำการประมง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_3_fail_2"
                   data-code="fail_2"
                   data-item-code="1_3"
                   data-text="ไม่ผ่าน - วางสิ่งของ เช่น เครื่องจากวาน รอก ฯลฯ ปะปน ไม่เหมาะสม">
            <label class="form-check-label" for="chk_1_3_fail_2">
              วางสิ่งของ เช่น เครื่องจากวาน รอก ฯลฯ ปะปน ไม่เหมาะสม
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_3" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_1_3"
                    data-code="1_3_remark"
                    data-item-code="1_3"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 3 -->

<!-- ข้อ 4 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_4">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_4"
            aria-expanded="false" aria-controls="collapse1_4">
      1.4 มีพื้นที่เพียงพอและเหมาะสมสำหรับรับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำ...
    </button>
  </h2>
  <div id="collapse1_4" class="accordion-collapse collapse" aria-labelledby="heading1_4" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-4">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio-1_4" type="radio"
                   name="status_1_4" id="status_1_4_pass" value="pass"
                   data-item-code="1_4">
            <label class="form-check-label" for="status_1_4_pass">
              ผ่าน - มีพื้นที่รับวัตถุดิบ การคัดเลือก การขนถ่าย และเก็บรักษาสัตว์น้ำเพียงพอและเหมาะสม
            </label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input form-status-radio-1_4" type="radio"
                   name="status_1_4" id="status_1_4_fail" value="fail"
                   data-item-code="1_4">
            <label class="form-check-label" for="status_1_4_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist ไม่ผ่าน -->
        <div id="fail_group_1_4" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_4_fail_1"
                   data-code="fail_1"
                   data-item-code="1_4"
                   data-text="ไม่ผ่าน - สัตว์น้ำและเครื่องมือวางปะปนกัน หรือวางทับซ้อนกัน ไม่เป็นระเบียบ">
            <label class="form-check-label" for="chk_1_4_fail_1">
              สัตว์น้ำและเครื่องมือวางปะปนกัน หรือวางทับซ้อนกัน ไม่เป็นระเบียบ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_4_fail_2"
                   data-code="fail_2"
                   data-item-code="1_4"
                   data-text="ไม่ผ่าน - การขนถ่ายที่ไม่ถูกสุขลักษณะ เช่น การเหยียบบนสัตว์น้ำ">
            <label class="form-check-label" for="chk_1_4_fail_2">
              การขนถ่ายที่ไม่ถูกสุขลักษณะ เช่น การเหยียบบนสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_4" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_1_4"
                    data-code="1_4_remark"
                    data-item-code="1_4"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 4 -->

<!-- ข้อ 5 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_5">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_5"
            aria-expanded="false" aria-controls="collapse1_5">
      1.5 พื้นที่ของเรือที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำทำจากวัสดุคงทน ผิวเรียบ ทำความสะอาดง่าย
    </button>
  </h2>
  <div id="collapse1_5" class="accordion-collapse collapse" aria-labelledby="heading1_5" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-5">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- สถานะผ่าน / ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_5" type="radio"
                   name="status_1_5" id="status_1_5_pass" value="pass"
                   data-item-code="1_5">
            <label class="form-check-label" for="status_1_5_pass">
              ผ่าน - พื้นที่ผิวเรียบ เกลี้ยง ไม่มีเสียหาย และกรณีทาสี สีไม่หลุดล่อน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_5" type="radio"
                   name="status_1_5" id="status_1_5_fail" value="fail"
                   data-item-code="1_5">
            <label class="form-check-label" for="status_1_5_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- Checklist ไม่ผ่าน -->
        <div id="fail_group_1_5" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_5_fail_1"
                   data-code="fail_1"
                   data-item-code="1_5"
                   data-text="ไม่ผ่าน - พื้นผิวไม่เรียบ เป็นร่อง ยาแนวหลุดล่อน เป็นฝุ่นขุย">
            <label class="form-check-label" for="chk_1_5_fail_1">
              พื้นผิวไม่เรียบ เป็นร่อง ยาแนวหลุดล่อน เป็นฝุ่นขุย
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_5_fail_2"
                   data-code="fail_2"
                   data-item-code="1_5"
                   data-text="ไม่ผ่าน - สีหลุดล่อน มีสนิม ปูนร่วง ทาสีไม่เหมาะกับบรรจุสัตว์น้ำ">
            <label class="form-check-label" for="chk_1_5_fail_2">
              สีหลุดล่อน มีสนิม ปูนร่วง ทาสีไม่เหมาะกับบรรจุสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_5" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_1_5"
                    data-code="1_5_remark"
                    data-item-code="1_5"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 5 -->

<!-- ข้อ 6 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_6">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_6"
            aria-expanded="false" aria-controls="collapse1_6">
      1.6 พื้นที่ปฏิบัติงานและห้องเก็บรักษาสัตว์น้ำต้องทำความสะอาดทุกครั้งหลังการใช้งานด้วยน้ำสะอาด...
    </button>
  </h2>
  <div id="collapse1_6" class="accordion-collapse collapse" aria-labelledby="heading1_6" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-6">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_6" type="radio"
                   name="status_1_6" id="status_1_6_pass" value="pass"
                   data-item-code="1_6">
            <label class="form-check-label" for="status_1_6_pass">
              ผ่าน - พื้นที่สะอาด ทำความสะอาดด้วยน้ำสะอาด และไม่มีแมลงหรือสัตว์อื่น ๆ บนเรือ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_6" type="radio"
                   name="status_1_6" id="status_1_6_fail" value="fail"
                   data-item-code="1_6">
            <label class="form-check-label" for="status_1_6_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_1_6" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_6_fail_1"
                   data-code="fail_1"
                   data-item-code="1_6"
                   data-text="ไม่ผ่าน - พื้นที่สกปรก ไม่ได้ทำความสะอาดหลังใช้งาน">
            <label class="form-check-label" for="chk_1_6_fail_1">
              พื้นที่สกปรก ไม่ได้ทำความสะอาดหลังใช้งาน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_6_fail_2"
                   data-code="fail_2"
                   data-item-code="1_6"
                   data-text="ไม่ผ่าน - พบสุนัข แมว หนู จิ้งจก แมลง หรือสัตว์อื่น ๆ ในเรือหรือห้องเก็บสัตว์น้ำ">
            <label class="form-check-label" for="chk_1_6_fail_2">
              พบสุนัข แมว หนู จิ้งจก แมลง หรือสัตว์อื่น ๆ ในเรือหรือห้องเก็บสัตว์น้ำ
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_6" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_1_6"
                    data-code="1_6_remark"
                    data-item-code="1_6"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 6 -->

<!-- ข้อ 7 -->
 <div class="accordion-item">
  <h2 class="accordion-header" id="heading1_7">
    <button class="accordion-button collapsed bg-primary text-white" type="button"
            data-bs-toggle="collapse" data-bs-target="#collapse1_7"
            aria-expanded="false" aria-controls="collapse1_7">
      1.7 จัดพื้นที่บริเวณเฉพาะสำหรับเก็บขยะ เศษอาหาร และเศษสัตว์น้ำที่เหลือให้เป็นสัดส่วนแยกออกจากบริเวณพื้นที่ปฏิบัติงาน
    </button>
  </h2>
  <div id="collapse1_7" class="accordion-collapse collapse" aria-labelledby="heading1_7" data-bs-parent="#inspectionAccordion">
    <div class="accordion-body">
      <form id="form-1-7">
        <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id) ?>">

        <!-- radio ผ่าน/ไม่ผ่าน -->
        <div class="mb-3">
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_7" type="radio"
                   name="status_1_7" id="status_1_7_pass" value="pass"
                   data-item-code="1_7">
            <label class="form-check-label" for="status_1_7_pass">
              ผ่าน - มีการจัดพื้นที่ขยะเป็นสัดส่วน มีภาชนะเก็บขยะ และไม่พบเศษอาหารในพื้นที่ปฏิบัติงาน
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input form-status-radio-1_7" type="radio"
                   name="status_1_7" id="status_1_7_fail" value="fail"
                   data-item-code="1_7">
            <label class="form-check-label" for="status_1_7_fail">
              ไม่ผ่าน
            </label>
          </div>
        </div>

        <!-- checklist ไม่ผ่าน -->
        <div id="fail_group_1_7" class="border p-3 mb-3 bg-light" style="display: none;">
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_7_fail_1"
                   data-code="fail_1"
                   data-item-code="1_7"
                   data-text="ไม่ผ่าน - ไม่มีถัง/ภาชนะใส่ขยะที่ชัดเจน เช่น ถุงพลาสติก ขวดแก้ว กระป๋องกระจัดกระจาย">
            <label class="form-check-label" for="chk_1_7_fail_1">
              ไม่มีถัง/ภาชนะใส่ขยะที่ชัดเจน เช่น ถุงพลาสติก ขวดแก้ว กระป๋อง
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_7_fail_2"
                   data-code="fail_2"
                   data-item-code="1_7"
                   data-text="ไม่ผ่าน - พบเศษอาหาร เช่น ข้าว เศษปลา ฯลฯ ไม่อยู่ในพื้นที่จัดเก็บ">
            <label class="form-check-label" for="chk_1_7_fail_2">
              พบเศษอาหาร เช่น ข้าว เศษปลา ฯลฯ ไม่อยู่ในพื้นที่จัดเก็บ
            </label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input checklist-item" type="checkbox"
                   id="chk_1_7_fail_3"
                   data-code="fail_3"
                   data-item-code="1_7"
                   data-text="ไม่ผ่าน - พบเศษสัตว์น้ำที่เหลืออยู่บนพื้นที่ปฏิบัติงาน">
            <label class="form-check-label" for="chk_1_7_fail_3">
              พบเศษสัตว์น้ำที่เหลืออยู่บนพื้นที่ปฏิบัติงาน
            </label>
          </div>
        </div>

        <!-- หมายเหตุ -->
        <div class="mb-3">
          <label for="remark_1_7" class="form-label">หมายเหตุ (ถ้ามี):</label>
          <textarea class="form-control checklist-remark"
                    id="remark_1_7"
                    data-code="1_7_remark"
                    data-item-code="1_7"
                    placeholder="พิมพ์ข้อสังเกตเพิ่มเติม..."></textarea>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- จบข้อ 7 -->

</div><!--<div class="accordion" id="inspectionAccordion">-->
</div><!--<div class="container-fluid">-->

<?php
include("../../private/shared/footerofficer.php");
?>

<script>
$(document).ready(function () {
  $('.form-status-radio').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const itemCode = $(this).data('item-code'); // เช่น 1_1, 1_2
    const targetSection = '#fail_group_' + itemCode;

    if (isFail) {
      $(targetSection).slideDown();
    } else {
      $(targetSection).slideUp();
    }
  });
});
</script>

<script>
$(document).ready(function () {
  $('.form-status-radio-1_4').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const target = '#fail_group_1_4';
    if (isFail) {
      $(target).slideDown();
    } else {
      $(target).slideUp();
    }
  });
});
</script>

<script>// js ข้อ 5
$(document).ready(function () {
  $('.form-status-radio-1_5').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const target = '#fail_group_1_5';
    if (isFail) {
      $(target).slideDown();
    } else {
      $(target).slideUp();
    }
  });
});
</script>


<script>// js ข้อ 6
$(document).ready(function () {
  $('.form-status-radio-1_6').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const target = '#fail_group_1_6';
    if (isFail) {
      $(target).slideDown();
    } else {
      $(target).slideUp();
    }
  });
});
</script>

<script>
$(document).ready(function () {
  $('.form-status-radio-1_7').on('change', function () {
    const isFail = $(this).val() === 'fail';
    const target = '#fail_group_1_7';
    if (isFail) {
      $(target).slideDown();
    } else {
      $(target).slideUp();
    }
  });
});
</script>


<?
include("../../private/shared/footerall.php");
?>