<div class="top-nav">
    <a href="#" onclick="konfirmasiKeluar()" class="btn-logout">Keluar Akun</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function konfirmasiKeluar() {
    Swal.fire({
        title: 'Yakin mau keluar?',
        text: "Anda harus login kembali untuk mengakses data.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout_aksi.php';
        }
    })
}
</script>