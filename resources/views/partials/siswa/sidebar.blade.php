<aside class="sidebar bg-success text-white">

    <div class="sidebar-header text-center py-4">
        <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
            alt="Logo MTs Al-Abror"
            width="65"
            class="mb-2">
        <h5 class="mb-0 fw-bold">{{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}</h5>
        <small class="text-white-50">Dashboard Siswa</small>
    </div>


</aside>