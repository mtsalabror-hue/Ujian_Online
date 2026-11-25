{{-- Navigasi bawah untuk HP (menggantikan sidebar kiri di layar kecil) --}}
<nav class="bottom-nav d-md-none">
    <a href="{{ route('admin.dashboard') }}"
        class="bottom-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Beranda</span>
    </a>
    <a href="{{ route('admin.guru.index') }}"
        class="bottom-nav-item {{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
        <i class="bi bi-person-video3"></i>
        <span>Guru</span>
    </a>
    <a href="{{ route('admin.siswa.index') }}"
        class="bottom-nav-item {{ request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
        <i class="bi bi-mortarboard"></i>
        <span>Siswa</span>
    </a>
    <a href="{{ route('admin.hasil-siswa.index') }}"
        class="bottom-nav-item {{ request()->routeIs('admin.hasil-siswa.*') ? 'active' : '' }}">
        <i class="bi bi-trophy"></i>
        <span>Hasil</span>
    </a>
    <button type="button" class="bottom-nav-item" data-sidebar-toggle>
        <i class="bi bi-list"></i>
        <span>Menu</span>
    </button>
</nav>
