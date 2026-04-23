<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/popper.min.js"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/simplebar.min.js"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/bootstrap.min.js"></script>
<script src="{{ asset('assets/BE') }}/js/fonts/custom-font.js"></script>
<script src="{{ asset('assets/BE') }}/js/pcoded.js"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/jquery.dataTables.min.js"></script>
<script src="{{ asset('assets/BE') }}/js/plugins/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

<script>
    document.querySelector('body').classList.add('preset-1');

    $(document).ready(function() {
        $('.datatable').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
                emptyTable: "Tidak ada data tersedia",
                zeroRecords: "Tidak ada data yang cocok"
            }
        });
    });
</script>

@if(session('success'))
<script>
    toastr.success("{{ session('success') }}");
</script>
@endif

@if(session('error'))
<script>
    toastr.error("{{ session('error') }}");
</script>
@endif

@if($errors->any())
<script>
    @foreach($errors->all() as $error)
        toastr.error("{{ $error }}");
    @endforeach
</script>
@endif

@stack('scripts')
