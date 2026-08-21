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

$(document).ready(function () {
    // ✅ แจ้งเตือนแบบ Ajax
    loadNotificationCount();
    setInterval(loadNotificationCount, 60000); // โหลดทุก 1 นาที
       //Bs tootip
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
        });
    
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
    })


    $('#alertsDropdown').on('show.bs.dropdown', function () {
        $.ajax({
            url: 'ajax/load_notifications.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#alert-count').text(response.unread_count);

                if (response.notifications.length === 0) {
                    $('#alert-list').html('<div class="dropdown-item text-gray-500 small">ไม่มีการแจ้งเตือน</div>');
                    return;
                }

                let html = '';
                response.notifications.forEach(n => {
                    html += `
                    <a class="dropdown-item d-flex align-items-center" href="${n.link ?? '#'}">
                        <div class="me-3">
                            <div class="icon-circle bg-${n.type === 'action_required' ? 'warning' : 'primary'}">
                                <i class="fas fa-${n.type === 'action_required' ? 'exclamation-triangle' : 'info-circle'} text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">${n.time}</div>
                            <span class="font-weight-bold">${n.message}</span>
                        </div>
                    </a>`;
                });

                $('#alert-list').html(html);
            },
            error: function() {
                $('#alert-list').html('<div class="dropdown-item text-danger small">โหลดแจ้งเตือนไม่สำเร็จ</div>');
            }
        });
    });

    

    // ✅ DataTable เริ่มต้นพร้อม topSearch โดยตรวจสอบก่อนว่ามี element นี้หรือไม่
            if ($('#dataTable').length > 0) {

            var table = $('#dataTable').DataTable({
                language: {
                    lengthMenu: "แสดง _MENU_ รายการ",
                    zeroRecords: "ไม่พบข้อมูล",
                    info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    infoEmpty: "ไม่มีข้อมูล",
                    infoFiltered: "(ค้นหาจากทั้งหมด _MAX_ รายการ)",
                    search: "ค้นหา:",
                    paginate: {
                        first: "หน้าแรก",
                        last: "หน้าสุดท้าย",
                        next: "ถัดไป",
                        previous: "ก่อนหน้า"
                    }
                },
                order: [[0, 'desc']]
            });

            // ให้มั่นใจว่าเจอ element topSearch
            $('#topSearch').off('keyup').on('keyup', function () {
                let value = $(this).val();
                table.search(value).draw();
            });
        }


});