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
    <link
      href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
      rel="stylesheet"
    />
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

    </style>
    
  </head>

  <body id="page-top">
    

      
  
