{{-- JQuery --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

{{-- Bootstrap --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

{{-- SweetAlert --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- DataTables --}}
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Flash Message --}}
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: "{{ session('success') }}",
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: "{{ session('error') }}"
    });
</script>
@endif

<script>
    $(function() {

        $('.datatable').DataTable({
            responsive: true,
            autoWidth: false,
        });

        $('.select2').select2({
            width: '100%'
        });

        /* ---- Sidebar: drawer di HP, lipat di desktop ---- */
        var $sidebar = $('.sidebar');
        var $backdrop = $('#sidebarBackdrop');

        function openSidebar() {
            $sidebar.addClass('mobile-open');
            $backdrop.addClass('show');
        }

        function closeSidebar() {
            $sidebar.removeClass('mobile-open');
            $backdrop.removeClass('show');
        }

        $(document).on('click', '#toggleSidebar, [data-sidebar-toggle]', function (e) {
            e.preventDefault();

            // Layar kecil: buka/tutup drawer
            if (window.innerWidth < 992) {
                if ($sidebar.hasClass('mobile-open')) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
                return;
            }

            // Desktop: lipat / buka sidebar kiri
            $sidebar.toggleClass('hide');
        });

        // Klik backdrop menutup drawer
        $backdrop.on('click', closeSidebar);

        // Klik menu di dalam drawer menutup drawer (HP saja)
        $(document).on('click', '.sidebar .menu a:not(.dropdown-btn)', function () {
            if (window.innerWidth < 992) {
                closeSidebar();
            }
        });

        /* ---- Dropdown di sidebar (Data Master, Kelola Ujian, Hasil Ujian) ---- */
        $(document).on('click', '.sidebar .dropdown-btn', function (e) {
            e.preventDefault();
            $(this).next('.submenu').toggleClass('show');
            $(this).find('.bi-chevron-down').toggleClass('rotated');
        });

    });
</script>

@stack('scripts')