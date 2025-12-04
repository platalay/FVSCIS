<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link rel="icon" type="image/x-icon" href="../img/favicon_fvscis.ico">
    <title>FVSCIS</title>

    <!-- Custom fonts for this template-->
    <!-- ใช้ Font Awesome 6 ผ่าน CDN -->
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" integrity="sha512-yYAvNc1yt43A8WlpEfxZB+e6Q+Uw6+k4ImCwU7Uv7Q7tXfS0ZsSHe8hrO0GnTt9vUmEyN4uh5kp1uQhxJcZx1A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link
      href="../vendor/fontawesome-free/css/all.min.css"
      rel="stylesheet"
      type="text/css"
    />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet"
    />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ✅ โหลด Bootstrap Icons (จำเป็นสำหรับ <i class="bi bi-trash"></i>) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.min.css" rel="stylesheet" />
    <!-- ✨ CSS เฉพาะโมดัลนี้ (วางไว้ครั้งเดียวใต้ <head> หรือไฟล์ css ของคุณ) -->
    <style>
      /* จัด layout ภายในโมดัลให้เลื่อนแค่ .modal-body */
      #modalFvscisOldAdd .modal-content { display: flex; flex-direction: column; }
      #modalFvscisOldAdd .modal-body { overflow-y: auto; }

      /* มือถือ ≤576px: ให้เต็มจอ และกัน header/footer เหลือพื้นที่เลื่อนใน body */
      @media (max-width: 576px) {
        #modalFvscisOldAdd .modal-dialog { margin: 0; }                   /* เต็มจอจริง */
        #modalFvscisOldAdd .modal-content { height: 100dvh; border-radius: 0; } /* รองรับแถบ address bar */
        #modalFvscisOldAdd .modal-body { max-height: calc(100dvh - 11rem); }    /* เผื่อ header+footer */
      }
    </style>
    <!-- CSS เฉพาะโมดัลนี้ -->
    <style>

    table thead th {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    font-weight: 600;
    }
    /* 1. ยังไม่ได้นัดตรวจ */
tr.tr-not-scheduled > td {
    background-color: #f2f2f2 !important;
    color: #555 !important;
}

/* 2. นัดแล้ว แต่ยังไม่ยืนยัน */
tr.tr-wait-confirm > td {
    background-color: #fff3cd !important;
    color: #856404 !important;
}

/* 3. นัดแล้ว + ยืนยันแล้ว (พร้อมตรวจ) */
tr.tr-pending-confirmed > td {
    background-color: #e2f0cb !important;
    color: #33691e !important;
}

/* 4. Inspecting / ทำผลตรวจ / ส่งอนุมัติ */
tr.tr-inspecting > td {
    background-color: #d7e3fc !important;
    color: #084298 !important;
}

/* 5. Completed / Passed / Failed / Conditional */
tr.tr-completed > td {
    background-color: #d4edda !important;
    color: #155724 !important;
}

/* 6. Cancelled */
tr.tr-cancelled > td {
    background-color: #f8d7da !important;
    color: #842029 !important;
}



      /* จัด layout: ให้ .modal-body เป็นส่วนที่เลื่อน */
      #modalFvscisOldEdit .modal-content {
        display: flex;
        flex-direction: column;
      }
      #modalFvscisOldEdit .modal-body {
        flex: 1 1 auto;           /* กินพื้นที่ที่เหลือ */
        overflow-y: auto;          /* เลื่อนเฉพาะ body */
        -webkit-overflow-scrolling: touch; /* นิ่มบน iOS */
      }

      /* จอ ≤992px (lg-down): เต็มจอ, ไม่มี margin, สูงเท่า viewport จริง */
      @media (max-width: 992px) {
        #modalFvscisOldEdit .modal-dialog { margin: 0; }
        #modalFvscisOldEdit .modal-content {
          height: 100dvh;          /* dynamic viewport, กันแถบ address bar */
          border-radius: 0;
        }
      }
      .req-type-pill {
        display: inline-flex;
        gap: .4rem;
        align-items: center;
        background: #f8f9fa;
        border: 1px solid #e6e6e6;
        border-radius: 999px;
        padding: .25rem .55rem;
        font-size: 13px;
        line-height: 1;
        transition: all 0.2s ease-in-out;
      }

      .req-type-pill:hover {
        background: #eef3ff;
        border-color: #c9d7f5;
      }

      /* ปรับสีแต่ละหมวด */
      .req-type-pill i.eu        { color: #2f6bd8; }   /* EU */
      .req-type-pill i.normal    { color: #6c757d; }   /* ตรวจทั่วไป */
      .req-type-pill i.officer   { color: #0bb; }      /* เจ้าหน้าที่ */
      .req-type-pill i.user      { color: #28a745; }   /* ผู้ยื่นเอง */
      .req-type-pill i.cold      { color: #0ea5e9; }   /* ห้องเย็น */
      .req-type-pill i.warm      { color: #9b9b9b; }   /* ไม่มีห้องเย็น */
      .att-thumb {
        width: 110px; height: 110px; object-fit: cover;
        border-radius: .5rem; border: 1px solid #e6e6e6; cursor: zoom-in;
        background:#f8f9fa;
      }

      /* ใช้เฉพาะในโมดัลนี้ */
      #modalFvscisOldAdd .file-card{ position:relative; }
      #modalFvscisOldAdd .thumb-wrap{
        width: 100%;
        height: 120px;             /* 💡 ความสูง thumbnail */
        border-radius: .5rem;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
      }
      #modalFvscisOldAdd .thumb-wrap img{
        width: 100%;
        height: 100%;
        object-fit: cover;          /* ครอปให้พอดีกรอบ */
        display: block;
      }
      #modalFvscisOldAdd .icon-pdf{
        display:flex; align-items:center; justify-content:center;
        width:100%; height:120px; border-radius:.5rem;
        background:#fff1f2; border:1px dashed #fecdd3; font-weight:700;
      }
      #modalFvscisOldAdd .file-name{ font-size:.825rem; }

      .file-card{position:relative;border:1px solid #e9ecef;border-radius:.75rem;padding:.5rem}
      .file-card .btn-remove, .file-card .btn-del-existing{position:absolute;top:.35rem;right:.35rem}
      .thumb-wrap{width:100%;height:140px;background:#f8f9fa;border-radius:.5rem;display:flex;align-items:center;justify-content:center;overflow:hidden}
      .thumb-wrap img{max-width:100%;max-height:100%;object-fit:cover}
      .icon-pdf{width:100%;height:140px;border-radius:.5rem;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-weight:700}
      .file-name{font-size:.85rem;word-break:break-all}
    
      .thumb-wrap {
          position: relative;
        }

        .thumb-wrap img {
          width: 100%;
          height: 140px;
          object-fit: cover;
          display: block;
        }

        .thumb-wrap .btn-del-existing-manual,
        .thumb-wrap .btn-remove-new-manual {
          position: absolute;
          top: 6px;
          right: 6px;
          z-index: 10;
        }

    
        .file-card .btn-del-existing-x {
  width: auto !important;
  display: inline-flex !important;
  justify-content: center;
  align-items: center;
  padding: 0.15rem 0.3rem;
  border-radius: 0.25rem;
}
    
    </style>
    
  </head>

  <body id="page-top">
    

      
  
