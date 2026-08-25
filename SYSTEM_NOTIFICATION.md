# ระบบ Notification / กระดิ่ง (Bell) ใน FVSCIS

เอกสารนี้เขียนจากการวิเคราะห์โค้ดจริงในปัจจุบันของระบบ FVSCIS โดยไม่ได้ปรับปรุง source code ใด ๆ และไม่สรุปจากการเดา

## 1. ภาพรวมระบบ Notification ปัจจุบัน

ระบบ notification ใน FVSCIS เป็นระบบแบบ database-backed, per-user, per-role: notification ถูกบันทึกลงตาราง `notifications` และ query ตาม `user_id` + `user_role` + `is_read` เพื่อแสดงจำนวน unread และรายการล่าสุดใน dropdown กระดิ่ง

การปรับปรุงล่าสุดได้ยึดแนวทางนี้ให้สอดคล้องกับ implementation จริง: ทุก role ใช้ `Notification::build_destination($notification, $user_role)` เพื่อคำนวณลิงก์จริงจาก `inspection_request_id` + `InspectionRequest::ship_code` แทนการพึ่ง `reference_type/reference_id` ที่ไม่มีอยู่จริง; และมี `Notification::mark_single_as_read()` สำหรับ mark item เดี่ยวแบบปลอดภัย โดยจำกัดด้วย `id + user_id + user_role`

จุดสำคัญ:

- Notification model หลักอยู่ที่ [private/classes/Notification.class.php](private/classes/Notification.class.php)
- Badge / dropdown ตัวบาร์กระดิ่งอยู่ที่ [private/shared/topbaruser.php](private/shared/topbaruser.php), [private/shared/topbarofficer.php](private/shared/topbarofficer.php), [private/shared/topbarsigner.php](private/shared/topbarsigner.php), [private/shared/topbaradmin.php](private/shared/topbaradmin.php), [private/shared/topbarheadquarter.php](private/shared/topbarheadquarter.php)
- JavaScript ที่นับ unread และโหลดรายการอยู่ที่ [public/js/fvscis.js](public/js/fvscis.js)
- AJAX endpoint สำหรับโหลดข้อมูลกระดิ่งอยู่ในแต่ละ role เช่น [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php), [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php), [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php), [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php), [public/headquarter/ajax/load_notifications.php](public/headquarter/ajax/load_notifications.php)
- หน้าแสดงข้อมูลทั้งหมดอยู่ที่ [public/fisherman/notifications.php](public/fisherman/notifications.php), [public/inspectofficer/notifications.php](public/inspectofficer/notifications.php), [public/signer/notifications.php](public/signer/notifications.php), [public/admin/notifications.php](public/admin/notifications.php), [public/headquarter/notifications.php](public/headquarter/notifications.php)

จากโค้ดจริง ระบบนี้ไม่ได้ใช้ notification service ภายนอกหรือ queue; มันสร้าง record ทันทีใน database เมื่อมี event ปรากฏขึ้น

## 2. โครงสร้างฐานข้อมูล/ตาราง/field ที่เกี่ยวข้อง

### ตารางที่ใช้งาน

จาก [private/classes/Notification.class.php](private/classes/Notification.class.php) ได้ระบุ:

- table name: `notifications`
- columns:

```php
protected static $db_columns = [
    'id',
    'user_id',
    'user_role',
    'inspection_request_id',
    'action_id',
    'message',
    'notification_type',
    'is_read',
    'action_taken',
    'created_at', 'updated_at',
    'created_by', 'updated_by',
    'created_ip', 'updated_ip'
];
```

### ความหมาย field หลัก

| field | ประเภท/ค่าในโค้ด | ความหมาย |
| --- | --- | --- |
| `id` | PK | id ของ notification |
| `user_id` | integer | ผู้รับ notification |
| `user_role` | string เช่น `fisherman`, `inspectofficer`, `signer`, `admin`, `headquarter` | role ของผู้รับ |
| `inspection_request_id` | integer | id ของคำขอที่ notification เกี่ยวข้อง; ถ้าไม่มีใช้ `0` |
| `action_id` | integer | action code / event code ที่เกิดขึ้น |
| `message` | string | ข้อความที่แสดงใน bell |
| `notification_type` | string เช่น `info`, `warning`, `success`, `danger` | style สีของ notification |
| `is_read` | 0/1 | unread/read status |
| `action_taken` | 0/1 | ระบุว่าผู้รับได้ทำ action ที่เกี่ยวข้องไปแล้วหรือยัง |
| `created_at` | datetime | เวลาที่สร้าง notification |
| `updated_at` | datetime | เวลาแก้ไขล่าสุด |
| `created_by` / `updated_by` | integer/string | metadata บันทึกผู้สร้าง/ปรับปรุง |
| `created_ip` / `updated_ip` | string | metadata IP |

### หมายเหตุสำคัญ

- โค้ดระบุ comment: `// 0 = system / admin notification ที่ไม่ผูกกับคำขอ` สำหรับ `inspection_request_id` ใน [private/classes/Notification.class.php](private/classes/Notification.class.php)
- ไม่พบไฟล์ DDL/SQL สำหรับสร้างตาราง `notifications` ใน repo นี้; โครงสร้าง field จึงสืบจาก model เท่านั้น
- `Notification::create_notification()` จะตั้งค่าเริ่มต้นเป็น `is_read = 0` และ `action_taken = 0` เสมอ

## 3. จุดที่สร้าง notification

มีการเรียก `Notification::create_notification(...)` หลายจุดตาม workflow จริง โดยมีลักษณะสำคัญดังนี้

### 3.1 สมัครสมาชิก / admin alert

- [public/ajax/save_fisherman.php](public/ajax/save_fisherman.php)
- [public/ajax/save_officer.php](public/ajax/save_officer.php)

เมื่อมีผู้สมัครชาวประมงหรือเจ้าหน้าที่ใหม่ จะค้นหา admin และส่ง notification ไปยัง `admin`:

```php
Notification::create_notification(
    $admin->id,
    'admin',
    0,
    1,
    $msg,
    'warning'
);
```

ในกรณีนี้ `inspection_request_id = 0` และ `action_id = 1` เป็น notification แบบไม่มีคำขอที่เกี่ยวข้อง

### 3.2 ชาวประมงยื่นคำขอ / เจ้าหน้าที่ได้รับแจ้ง

- [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php)

หลังบันทึกคำขอสำเร็จ ระบบจะดึง `Officer::find_by_department_id($department_id)` และสร้าง notification ให้เจ้าหน้าที่ในแผนกที่ถูกเลือก:

```php
foreach ($officers as $officer) {
    Notification::create_notification(
        $officer->id,
        'inspectofficer',
        $request->id,
        2,
        $notif_title,
        'warning'
    );
}
```

### 3.3 เจ้าหน้าที่กำหนด/เปลี่ยนวันตรวจ

- [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php)

เมื่อเจ้าหน้าที่กำหนดหรือเสนอวันตรวจใหม่ จะสร้าง notification ให้ชาวประมง:

```php
Notification::create_notification(
    $request->created_by,
    'fisherman',
    $request->id,
    7,
    "เจ้าหน้าที่กำหนดวันตรวจเรือ ...",
    'warning'
);
```

และภายหลังจะเรียก:

```php
Notification::mark_action_taken($officer->id, 'inspectofficer', $request->id, [2,3]);
```

เพื่อปิด action ของ officer ที่เกี่ยวข้อง

### 3.4 ชาวประมงยืนยันวันตรวจ

- [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php)

เมื่อชาวประมงยืนยันวันตรวจแล้ว จะสร้าง notification ให้เจ้าหน้าที่ของแผนก:

```php
foreach ($officers as $officer) {
    Notification::create_notification(
        $officer->id,
        'inspectofficer',
        $req->id,
        8,
        $message,
        'warning'
    );
}
```

และยังเรียก `Notification::mark_action_taken($session->user_id(), 'fisherman', $req->id, 7)` เพื่อทำเครื่องหมายให้ action ของชาวประมงเป็นดำเนินการแล้ว

### 3.5 การลบคำขอ

- [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php)

เมื่อชาวประมงลบคำขอ ระบบสร้าง notification 2 ช่อง:

- notification ให้ชาวประมงเอง
- notification ให้เจ้าหน้าที่แผนกของคำขอ

```php
Notification::create_notification(
    $currentUserId,
    'fisherman',
    $id,
    4,
    "เรือ {$request->vessel_name} : ลบคำขอตรวจสุขอนามัยเรียบร้อย",
    'warning'
);
```

และต่อด้วย loop ให้ officer ใน department

### 3.6 ผลการตรวจ / ดาวน์โหลด PDF / ส่งต่อผู้มีอำนาจ

- [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php)
- [public/signer/generate_pdf.php](public/signer/generate_pdf.php)
- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)
- [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php)
- [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php)

ตัวอย่างที่ชัดเจนคือ เมื่อเจ้าหน้าที่ส่งผลการตรวจแล้ว (pass / failed / conditional) ให้สร้าง notification ถึง fisherman และ signer:

```php
Notification::create_notification(
    $approver_id,
    'signer',
    $request->id,
    16,
    "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจ ...",
    'info'
);
```

และ/หรือ

```php
Notification::create_notification(
    $request->created_by,
    'fisherman',
    $request->id,
    16,
    "ผลการตรวจเรือ {$request->vessel_name} ผ่านการตรวจ ...",
    'info'
);
```

สำหรับกรณี `failed` จะใช้ `action_id = 17` และ `notification_type = 'warning'`

### 3.7 Approval ของ signer

- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)

เมื่อ signer อนุมัติหรือไม่อนุมัติคำขอ จะสร้าง notification ให้ fisherman หรือผู้ที่เกี่ยวข้อง โดยอิง `request->created_by` และ `action_id` ในลำดับ workflow:

```php
Notification::create_notification(
    $request->created_by,
    'fisherman',
    $request->id,
    $log->action_id,
    $log->note,
    'warning'
);
```

## 4. จุดที่อ่าน notification

มี 2 ประเภทหลัก:

### 4.1 ดึง unread notification สำหรับ dropdown bell

ทุก role มี AJAX endpoint ที่เรียก `Notification::recent_unread_notifications(...)` และ `Notification::unread_count(...)`

ตัวอย่าง:

- [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php)
- [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php)
- [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php)
- [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php)
- [public/headquarter/ajax/load_notifications.php](public/headquarter/ajax/load_notifications.php)

โค้ดแบบทั่วไป:

```php
$notifications = Notification::recent_unread_notifications($user_id, $user_role);
$unread = Notification::unread_count($user_id, $user_role);
```

จากนั้นจะส่ง JSON:

```php
json_encode(['unread_count' => $unread, 'notifications' => $data]);
```

### 4.2 ดึงรายการทั้งหมดสำหรับหน้า notifications.php

หน้าแสดงรายการทั้งหมดมีการเรียก:

```php
$notifications = Notification::recent_notifications($user_id, $user_role, 50);
```

ในไฟล์:

- [public/fisherman/notifications.php](public/fisherman/notifications.php)
- [public/inspectofficer/notifications.php](public/inspectofficer/notifications.php)
- [public/signer/notifications.php](public/signer/notifications.php)
- [public/admin/notifications.php](public/admin/notifications.php)
- [public/headquarter/notifications.php](public/headquarter/notifications.php)

## 5. วิธีนับ unread notification สำหรับ badge กระดิ่ง

ฟังก์ชันหลักอยู่ที่ [private/classes/Notification.class.php](private/classes/Notification.class.php):

```php
public static function unread_count($user_id, $user_role) {
    $sql = "SELECT COUNT(*) FROM " . static::$table_name .
           " WHERE user_id = '{$user_id}'
             AND user_role = '{$user_role}'
             AND is_read = 0";
    return static::count_by_sql($sql);
}
```

สรุปได้ว่า badge count เท่ากับจำนวน record ที่มีเงื่อนไข:

- `user_id = session user`
- `user_role = session role`
- `is_read = 0`

### JavaScript ที่อัปเดต badge

[public/js/fvscis.js](public/js/fvscis.js) มีฟังก์ชัน:

```js
function loadNotificationCount() {
    $.ajax({
        url: 'ajax/load_notifications.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#alert-count').text(response.unread_count);
            if (response.unread_count > 0) {
                $('#alert-count').show();
            } else {
                $('#alert-count').hide();
            }
        }
    });
}
```

ต่อด้วย:

```js
loadNotificationCount();
setInterval(loadNotificationCount, 60000);
```

ดังนั้น badge จะ refresh ทุก 60 วินาทีและยังเรียกตอนเริ่มหน้า

## 6. วิธี mark as read / unread ถ้ามี

### 6.1 mark all as read

มี function หลัก:

```php
public static function mark_all_as_read($user_id, $user_role) {
    $sql = "UPDATE " . static::$table_name .
           " SET is_read = 1
             WHERE user_id = '{$user_id}'
               AND user_role = '{$user_role}'";
    return self::$database->query($sql);
}
```

มีการเรียกผ่านไฟล์:

- [public/fisherman/notifications_mark_all.php](public/fisherman/notifications_mark_all.php)
- [public/inspectofficer/notifications_mark_all.php](public/inspectofficer/notifications_mark_all.php)
- [public/signer/notifications_mark_all.php](public/signer/notifications_mark_all.php)
- [public/admin/notifications_mark_all.php](public/admin/notifications_mark_all.php)
- [public/headquarter/notifications_mark_all.php](public/headquarter/notifications_mark_all.php)

ตัวอย่าง:

```php
Notification::mark_all_as_read($user_id, $user_role);
redirect_to('notifications.php');
```

### 6.2 mark specific action as done (not generic read)

มีฟังก์ชัน:

```php
public static function mark_action_taken($user_id, $user_role, $inspection_request_id, $action_ids = null) {
    $sql = "UPDATE " . static::$table_name . "
            SET action_taken = 1,
                is_read = 1
            WHERE user_id = '{$user_id}'
            AND user_role = '{$user_role}'
            AND inspection_request_id = {$inspection_request_id}";
```

และเลือกกรองต่อด้วย `action_id IN (...)` ถ้ามี `action_ids`

ความหมาย:

- `action_taken = 1` หมายถึง ผู้รับได้ดำเนินการตาม action แล้ว
- `is_read = 1` ถูก set ไปพร้อมกันในกรณีนี้
- เป็นการ mark แบบมีเงื่อนไขตามคำขอและ action_id ไม่ใช่ per-notification id อย่างชัดเจน

### 6.3 ไม่มีการค้นหาตาม notification id เพื่อ mark single item

จากการค้นหาโค้ดจริง ไม่พบ endpoint สำหรับ `mark_single_notification_as_read` หรือ `toggle_read` ต่อ notification id แบบเฉพาะเจาะจง; การ mark as read ส่วนใหญ่เป็น `mark_all_as_read` และ `mark_action_taken`

## 7. การเชื่อมโยง notification ไปยังหน้าเป้าหมาย

มีความแตกต่างระหว่าง role และ load endpoint:

### 7.1 role-specific link generation

- [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php)

```php
if (!empty($n->inspection_request_id)) {
    $req = InspectionRequest::find_by_id($n->inspection_request_id);
    if ($req && !empty($req->ship_code)) {
        $shipcode = $req->ship_code;
    }
}

if (!empty($shipcode)) {
    $link = 'mystatus.php?shipcode=' . urlencode($shipcode);
} else {
    $link = '#';
}
```

- [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php)

```php
$link = $shipcode
    ? 'incoming_requests.php?shipcode=' . urlencode($shipcode)
    : '#';
```

- [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php) และ [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php) เบื้องต้นมี `link => null` หรือไม่มี logic link

### 7.2 ตรงกับ UI bell dropdown

[public/js/fvscis.js](public/js/fvscis.js) สร้าง DOM สำหรับแต่ละ notification:

```js
<a class="dropdown-item d-flex align-items-center" href="${n.link ?? '#'}">
```

ซึ่งหมายความว่า `link` ถ้าเป็น `null` หรือ `#` จะไม่พาไปหน้าเป้าหมาย

### 7.3 ข้อสังเกตเรื่อง link

- link ในโค้ดส่วนใหญ่ไม่ได้ใช้ `reference_type`/`reference_id` ตาม pattern ใน `notifications.php` helper
- helper `notification_link()` ในแต่ละ `notifications.php` เรียกใช้ `reference_type`/`reference_id` แต่ field จริงของ model ไม่ประกอบด้วย `reference_type` และ `reference_id` ดังนั้น helper นี้ไม่มีประสิทธิภาพกับข้อมูลจริงในตาราง `notifications`
- ในทางปฏิบัติ actual link ที่ทำงานคือ link ที่สร้างจาก `InspectionRequest::find_by_id(...)->ship_code` ใน AJAX loader แต่ไม่ใช่ link จาก `reference_type`

## 8. role หรือ user ใดเป็นผู้รับ notification ในแต่ละกรณี

จากโค้ดจริง มี role ชัดเจนดังนี้

### 8.1 `fisherman`

ผู้รับ notification:

- เจ้าของคำขอเอง (`request->created_by`)
- ผู้ที่ยืนยันวันตรวจ (`session->user_id()`) เมื่อ action ต้องดำเนินการ

ตัวอย่างเหตุการณ์:

- เจ้าหน้าที่กำหนดวันตรวจ → notify fisherman
- ผลการตรวจ pass/failed/conditional → notify fisherman
- ลบคำขอ → notify fisherman
- signer อนุมัติหรือไม่อนุมัติ → notify fisherman

### 8.2 `inspectofficer`

ผู้รับ notification:

- officer ที่อยู่ใน department เดียวกันกับคำขอ (`Officer::find_by_department_id($department_id)`)
- เจ้าหน้าที่ที่เคยรับผิดชอบคำขอและต้องกด action ต่อไป

ตัวอย่างเหตุการณ์:

- ชาวประมงยื่นคำขอใหม่ → notify officers in selected department
- ชาวประมงยืนยันวันตรวจ → notify officers in department
- ลบคำขอ → notify officers in department
- วันนัดเปลี่ยน → officer group action_taken ถูก mark

### 8.3 `signer`

ผู้รับ notification:

- approver id จาก `DepartmentGroup::find_by_id($department->parent_department)->officer_id ?? null`
- คือ signer หรือ person ในการอนุมัติผลตรวจกลาง

ตัวอย่างเหตุการณ์:

- ผลการตรวจ passed/failed/conditional → notify signer
- signer approve request → notification ถึง fisherman

### 8.4 `admin`

ผู้รับ notification:

- ผู้ดูแลระบบ (`Officer::find_admins()`; usertype_id = 1)

ตัวอย่างเหตุการณ์:

- สมัครชาวประมงใหม่
- สมัครเจ้าหน้าที่ใหม่

### 8.5 `headquarter`

มีโครงสร้าง route / view สำหรับ headquarter แต่จากการค้นหา notification creation จริง ไม่พบ flow ที่สร้าง notification ให้ headquarter อย่างตรงไปตรงมาใน workflow ที่เกี่ยวกับ FV Certificate/Inspector/Signer นี้; headquarter มี topbar และ notifications page แต่ logic create notification ของจริงไม่ได้เห็นในโค้ดที่เกี่ยวข้องกับเรื่องนี้

## 9. event หรือ workflow ที่ทำให้เกิด notification

สิ่งที่พบจาก code จริงมีดังนี้

1. สมัครสมาชิกใหม่
   - [public/ajax/save_fisherman.php](public/ajax/save_fisherman.php)
   - [public/ajax/save_officer.php](public/ajax/save_officer.php)
   - notify `admin`

2. ชาวประมงยื่นคำขอใหม่
   - [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php)
   - notify `inspectofficer`

3. เจ้าหน้าที่กำหนดหรือเปลี่ยนวันนัดตรวจ
   - [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php)
   - notify `fisherman`

4. ชาวประมงยืนยันวันตรวจ
   - [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php)
   - notify `inspectofficer`

5. ลบคำขอ
   - [public/fisherman/ajax/delete_request.php](public/fisherman/ajax/delete_request.php)
   - notify `fisherman` + `inspectofficer`

6. ผลการตรวจผ่าน/ไม่ผ่าน/มีเงื่อนไข
   - [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php)
   - [public/signer/generate_pdf.php](public/signer/generate_pdf.php)
   - notify `signer` และ `fisherman`

7. signer approve / reject
   - [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)
   - notify `fisherman`

8. ประกาศออกใบรับรองเก่า/สรุปใบรับรองใหม่
   - [public/inspectofficer/ajax/update_fvscisold.php](public/inspectofficer/ajax/update_fvscisold.php)
   - [public/inspectofficer/ajax/delete_fvscisold.php](public/inspectofficer/ajax/delete_fvscisold.php)
   - รูปแบบ notification ที่เกี่ยวกับใบรับรองเก่า/สรุปผลตรวจ

## 10. notification ที่เกี่ยวข้องกับ FV Certificate, Inspector และ Signer

### 10.1 FV Certificate

มี notification ที่เกี่ยวข้องกับใบรับรองโดยตรงใน workflow ที่มีผลตรวจและส่งต่อไปยัง signer:

- [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php)
- [public/signer/generate_pdf.php](public/signer/generate_pdf.php)
- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)

ข้อความสำคัญ เช่น:

- `ผลการตรวจเรือ ... ผ่านการตรวจ เมื่อวันที่ ... อยู่ระหว่างรอการอนุมัติ`
- `ผลการตรวจเรือ ... อยู่ในสถานะไม่ผ่าน ... กรุณาตรวจสอบและยืนยันผลการตรวจ`
- `เรือ ... ได้รับการอนุมัติ ... โดย ...`

### 10.2 Inspector

Inspector/Officier มี role รับ notification หลายแบบ:

- เมื่อ fisherman ส่งคำขอใหม่
- เมื่อ fisherman ยืนยันวันตรวจ
- เมื่อคำขอลบ
- เมื่อเจ้าหน้าที่กำหนดวันตรวจใหม่ไปยัง fisherman
- เมื่อมีการส่ง approval result ได้รับเรื่องมา review

### 10.3 Signer

Signer เป็น role ที่รับ notification จากการตรวจผลการตรวจและอนุมัติผล:

- ขณะที่ inspector generate PDF / mark form status passed/failed/conditional
- ใช้ `approver_id` แรกจาก structure ของ department/group
- เมื่อ signer approve request แล้ว ส่ง notification กลับผู้ยื่นคำขอ

## 11. AJAX endpoint / PHP file / JavaScript ที่เกี่ยวข้อง

### Main model

- [private/classes/Notification.class.php](private/classes/Notification.class.php)

### AJAX loaders

- [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php)
- [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php)
- [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php)
- [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php)
- [public/headquarter/ajax/load_notifications.php](public/headquarter/ajax/load_notifications.php)

### Mark all read

- [public/fisherman/notifications_mark_all.php](public/fisherman/notifications_mark_all.php)
- [public/inspectofficer/notifications_mark_all.php](public/inspectofficer/notifications_mark_all.php)
- [public/signer/notifications_mark_all.php](public/signer/notifications_mark_all.php)
- [public/admin/notifications_mark_all.php](public/admin/notifications_mark_all.php)
- [public/headquarter/notifications_mark_all.php](public/headquarter/notifications_mark_all.php)

### Bell UI and JS

- [public/js/fvscis.js](public/js/fvscis.js)
- [private/shared/topbaruser.php](private/shared/topbaruser.php)
- [private/shared/topbarofficer.php](private/shared/topbarofficer.php)
- [private/shared/topbarsigner.php](private/shared/topbarsigner.php)
- [private/shared/topbaradmin.php](private/shared/topbaradmin.php)
- [private/shared/topbarheadquarter.php](private/shared/topbarheadquarter.php)

### Pages list overall

- [public/fisherman/notifications.php](public/fisherman/notifications.php)
- [public/inspectofficer/notifications.php](public/inspectofficer/notifications.php)
- [public/signer/notifications.php](public/signer/notifications.php)
- [public/admin/notifications.php](public/admin/notifications.php)
- [public/headquarter/notifications.php](public/headquarter/notifications.php)

## 12. ลำดับการทำงานตั้งแต่ event เกิด → สร้าง notification → แสดงบนกระดิ่ง → ผู้ใช้กดอ่าน → เปลี่ยนสถานะ

### ลำดับจริงที่สังเกตได้จากโค้ด

1. Event เกิดขึ้นใน workflow เช่น ผู้ยื่นคำขอ, เจ้าหน้าที่กำหนดวันตรวจ, signer approve
2. Workflow เรียก `Notification::create_notification($user_id, $user_role, $inspection_request_id, $action_id, $message, $notification_type)`
3. `Notification` object จะบันทึกลงตาราง `notifications` พร้อม `is_read = 0` และ `action_taken = 0`
4. หน้า UI ที่เปิดอยู่จะโหลด AJAX เองผ่าน `loadNotificationCount()` ใน [public/js/fvscis.js](public/js/fvscis.js)
5. AJAX endpoint จะ query `recent_unread_notifications()` และ `unread_count()`
6. JSON response ส่งกลับ `unread_count` และ `notifications` list
7. JS อัปเดต `#alert-count` และ render `<div id="alert-list">`
8. ผู้ใช้คลิก bell เพื่อดู dropdown และเลือกดูทั้งหมดหรือคลิก link
9. หากผู้ใช้กด “ทำเครื่องหมายว่าอ่านทั้งหมดแล้ว” จะ post ไปยัง `notifications_mark_all.php` และเรียก `Notification::mark_all_as_read(...)`
10. สำหรับ workflow ที่ต้องมี action จะเรียก `Notification::mark_action_taken(...)` สำหรับแจ้งว่าดำเนินการแล้ว และในกรณีนี้จะกำหนด `is_read = 1` ให้ด้วย

## 13. ชื่อไฟล์และ function/class สำคัญที่เกี่ยวข้อง

### class / function

- `Notification` class — [private/classes/Notification.class.php](private/classes/Notification.class.php)
  - `create_notification()`
  - `unread_count()`
    - `build_destination()`
  - `recent_notifications()`
  - `recent_unread_notifications()`
  - `mark_all_as_read()`
    - `mark_single_as_read()`
  - `mark_action_taken()`

### ตัวจัดการ session / role

- [private/classes/session.class.php](private/classes/session.class.php)
  - `login()`
  - `is_logged_in()`
  - `require_role()`
  - `map_usertype_id_to_role()`

### การค้นหาผู้รับ

- [private/classes/officer.class.php](private/classes/officer.class.php)
  - `find_by_department_id()`
  - `find_admins()`

### Request model

- [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php)
  - `STATUS_PASSED`
  - `STATUS_FAILED`
  - `STATUS_CONDITIONAL`

## 14. code เก่า, logic ซ้ำ, endpoint ซ้ำ หรือจุดที่มีความเสี่ยง

### 14.1 Code ซ้ำมากและมีหลาย copy/variant

เอกสารนี้พบว่ามีหลายไฟล์ structure ที่คล้ายกันมาก:

- [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php)
- [public/fisherman/ajax/load_notifications.php](public/fisherman/ajax/load_notifications.php)
- [public/inspectofficer/ajax/load_notifications.php](public/inspectofficer/ajax/load_notifications.php)
- [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php)
- [public/headquarter/ajax/load_notifications.php](public/headquarter/ajax/load_notifications.php)

แต่ละไฟล์มี logic ที่คล้ายกันมาก เหมือน template copy เนื่องจากมีบ้างที่ตั้ง `link => null` และบ้างที่คำนวณ `link` จาก `ship_code` และมีความแตกต่างระหว่าง role

### 14.2 Endpoint ซ้ำ/duplicate file ที่น่าสงสัย

มีไฟล์ต่อไปนี้ในอดีต:

- [public/headquarter/ajax/load_notifications copy.php](public/headquarter/ajax/load_notifications%20copy.php)

ชื่อไฟล์นี้ยืนยันแล้วว่าไม่ถูกเรียกจาก code ใด ๆ และไม่ได้ใช้จริงใน runtime; หลังจากตรวจการอ้างอิงครบแล้วจึงได้ลบไฟล์สำเนานี้ออกเพื่อให้โครงสร้างสอดคล้องกับ implementation จริง

### 14.3 Logic link ที่เชื่อมกับข้อมูลจริงเท่านั้น

ใน [public/fisherman/notifications.php](public/fisherman/notifications.php), [public/inspectofficer/notifications.php](public/inspectofficer/notifications.php), [public/signer/notifications.php](public/signer/notifications.php), [public/admin/notifications.php](public/admin/notifications.php), [public/headquarter/notifications.php](public/headquarter/notifications.php) มี helper `notification_link()` ที่อ้างอิง `reference_type` และ `reference_id` แต่ model `Notification` จริงไม่มี field เหล่านี้ จึงมีความเสี่ยงว่า link ในหน้ารายการทั้งหมดจะไม่ทำงานจริงหรือไม่เคยถูก set

หลังการปรับปรุง แทนที่ด้วย `Notification::build_destination($notification, $user_role)` ซึ่งโหลด `inspection_request_id` แล้ว resolve `InspectionRequest::find_by_id(...)` เพื่ออ่าน `ship_code` แล้วคำนวณ path ของหน้าที่ถูกต้อง เช่น `mystatus.php?shipcode=...`, `incoming_requests.php?shipcode=...`, `inspection_requests.php?shipcode=...`

### 14.4 `mark_action_taken` ไม่ควรทำให้ notification เป็น read ทันที

ฟังก์ชัน `mark_action_taken()` ที่ปรับปรุงแล้วให้ตั้งเฉพาะ `action_taken = 1` และไม่แตะ `is_read` เพื่อให้ semantics ถูกต้อง: action_taken เป็นสถานะงานที่ผู้ใช้ได้ดำเนินการแล้ว ขณะที่ is_read เป็นสถานะการอ่านแยกต่างหาก

### 14.5 Admin/headquarter notification endpoints มีความไม่สอดคล้อง

- [public/admin/ajax/load_notifications.php](public/admin/ajax/load_notifications.php) และ [public/signer/ajax/load_notifications.php](public/signer/ajax/load_notifications.php) มี `link => null` ซึ่งหมายความว่า dropdownจะมีข้อความแต่ไม่มี link
- ขณะที่ fisherman / inspector กำหนด link แบบมีค่าโดยใช้ `ship_code`

### 14.6 ไม่มี pagination/soft-delete/tenant isolation ที่เห็นได้ชัด

- Notification table ไม่มี logic จัดการลบเก่า/cleanup
- ไม่เห็นการใช้ `deleted_at` หรือ `archived`
- unread count/query เรียกโดย user_id + user_role เท่านั้น และไม่ได้ filter ขอบเขตการใช้งานเพิ่มเติม

## 15. สิ่งที่ยังไม่แน่ชัดหรือควรทดสอบเพิ่มเติม

สิ่งที่ควรตรวจต่อในอนาคตเพื่อความชัดเจน:

1. ตาราง `notifications` จริงในฐานข้อมูลมี field เพิ่มเติมหรือไม่ เช่น `reference_type`, `reference_id` หรือ `read_at` ที่ไม่ปรากฏในโค้ด model
2. มีการเชื่อม notification กับ pages จริงที่ผู้ใช้คลิกแล้วไปหน้าที่ถูกต้องหรือไม่ เพราะบาง role ใช้ `link => null` หรือ `#`
3. ระบบมี mark single item read จริงหรือไม่; ปัจจุบันมี implementation ใน `Notification::mark_single_as_read()` และ role-specific endpoint `notifications_mark_read.php` สำหรับทุก role แล้ว
4. `headquarter` มี notification creation จริงหรือไม่; code ปัจจุบันเห็นว่า headquarter มี UI เท่านั้น แต่ยังไม่มี event create ที่ชัดเจนใน workflow หลัก
5. `action_id` มีความหมายเฉพาะครอบคลุมหรือไม่; ควรตรวจ mapping `action_id` กับ `LogAction`/Workflow จริง เพราะมีใช้เป็นรหัส event หลายแบบ
6. มีการเชื่อม `notification_type` กับ class CSS จริงหรือไม่; ส่วนมากใช้ `warning`, `info`, `success` แต่บางแห่งยังคงใช้ `notification_type` แบบไม่สอดคล้อง
7. มีความเป็นไปได้ของ notification duplicate ซึ่งเกิดจาก repeated loops หรือ create same message multiple times across role; ควรทดสอบการสั่งหลายครั้ง เช่น ระหว่างยืนยันวันนัด + regenerate PDF เป็นต้น
8. HTTP endpoint `ajax/load_notifications.php` ในแต่ละ role อาจมี route path ผิด/ไม่สอดคล้องกับ topbar embed ไปยัง string `'ajax/load_notifications.php'` ในทุกหน้าที่มี bell; ควรตรวจว่า path ใช้งานจริงใน context ของแต่ละ folder และมิใช่ route ข้าม folder

## 16. สรุปสั้นด้านเนื้อความที่ควรจำ

ระบบ notification ใน FVSCIS เป็นระบบที่สร้าง record ในตาราง `notifications` ทันทีเมื่อตัว workflow เกิดเหตุการณ์ต่าง ๆ และใช้ `user_id` + `user_role` + `is_read` คำนวณ badge unread และแสดงรายการใกล้สุดใน dropdown กระดิ่ง

- Notification model หลัก: [private/classes/Notification.class.php](private/classes/Notification.class.php)
- สร้างลิงก์ที่ใช้งานจริง: `Notification::build_destination()`
- mark item เดี่ยวที่ปลอดภัย: `Notification::mark_single_as_read()`
- Badge loader: [public/js/fvscis.js](public/js/fvscis.js)
- AJAX loaders: ดูในแต่ละ role folder ตาม list
- Read status: `is_read` สำหรับ unread/read, `action_taken` สำหรับ mark action done
- Mark-all-read: มีจริง, mark single item read มีแล้ว
- Link ไปหน้าถูกกำหนดจาก `inspection_request_id` + `ship_code` ด้วย role-aware destination builder; ทุก role ใช้ logic เดียวกัน
- Workflow ที่สร้าง notification มากที่สุดคือ request lifecycle, inspection result, approval, and registration alerts
- มีความซ้ำของ endpoint และ logic ระหว่าง role ที่น่าจะต้องตรวจต่อในอนาคต

## 16.1 ผลการทดสอบ Runtime ล่าสุด

ทดสอบผ่าน browser/runtime บน XAMPP วันที่ 25 สิงหาคม 2026 ด้วย session ของ `inspectofficer`:

- หน้า inspect officer เรียก loader ได้จริงที่ `inspectofficer/ajax/load_notifications.php` และตอบ HTTP 200 เป็น JSON; สาเหตุ 404 เดิมคือ unauthenticated request ถูก redirect ไป `inspectofficer/login.php` ซึ่งไม่มีอยู่ ไม่ใช่ไฟล์ loader หาย
- Badge แสดง unread `13` และ dropdown แสดงรายการจริง 10 รายการ; polling พบ request ห่างกันประมาณ 60 วินาที
- Single read ของ notification id `46` สำเร็จ, unread ลดจาก `13` เป็น `12`, DB เปลี่ยน `is_read` จาก `0` เป็น `1` และ `action_taken` ยังคง `0`
- อ่านซ้ำ, ใช้ user อื่น หรือใช้ role อื่น ตอบ failure และไม่ update record; `mark_single_as_read()` ตรวจ `affected_rows > 0` แล้ว
- Dropdown และหน้า `inspectofficer/notifications.php` ใช้ destination เดียวกัน (`incoming_requests.php` สำหรับรายการที่ทดสอบ)
- การทดสอบ mark-all ทำใน transaction ที่ rollback เพื่อไม่เปลี่ยนข้อมูลจริง; query แสดงว่าเปลี่ยนเฉพาะ `is_read` และไม่เปลี่ยน `action_taken`

ข้อจำกัดของผลทดสอบ:

- ยังไม่ได้ login และทดสอบแบบ end-to-end ครบทั้ง fisherman, signer, admin และ headquarter เพราะไม่มี credential สำหรับ role เหล่านั้นใน session ทดสอบ
- ไม่ได้ยิง workflow สร้างคำขอ/ยืนยันวันตรวจ/อนุมัติจริงเพื่อหลีกเลี่ยงการสร้างหรือเปลี่ยนข้อมูลธุรกิจจริง
- พบ notification จริงหลายรายการที่มี `inspection_request_id` แต่ไม่พบ request ที่ join ได้ในฐานข้อมูล; ระบบจึงใช้ destination fallback แบบ generic ตามที่ builder กำหนด ไม่สามารถยืนยัน ship code ของรายการเหล่านี้ได้ และควรตรวจ data integrity ของข้อมูลต้นทาง

## 17. ข้อมูลอ้างอิงหลักสำหรับการตรวจต่อ

- [private/classes/Notification.class.php](private/classes/Notification.class.php)
- [public/js/fvscis.js](public/js/fvscis.js)
- [public/fisherman/ajax/request_inspection.php](public/fisherman/ajax/request_inspection.php)
- [public/inspectofficer/ajax/confirm_inspect_date.php](public/inspectofficer/ajax/confirm_inspect_date.php)
- [public/fisherman/ajax/confirm_by_fisherman.php](public/fisherman/ajax/confirm_by_fisherman.php)
- [public/inspectofficer/generate_pdf.php](public/inspectofficer/generate_pdf.php)
- [public/signer/ajax/approve_request.php](public/signer/ajax/approve_request.php)
- [private/classes/session.class.php](private/classes/session.class.php)
- [private/classes/officer.class.php](private/classes/officer.class.php)
- [private/classes/InspectionRequest.class.php](private/classes/InspectionRequest.class.php)

เอกสารนี้เขียนเพื่อให้ใช้เป็น reference สำหรับระบบในอนาคต โดยยึดข้อมูลจริงจากโค้ดที่มีอยู่เท่านั้น และไม่ได้ตัดสินหรือวาง logic เพิ่มที่ไม่ได้พบใน source code
