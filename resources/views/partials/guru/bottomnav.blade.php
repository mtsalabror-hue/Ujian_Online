{{-- Navigasi bawah untuk HP (menggantikan sidebar kiri di layar kecil) --}}
<nav class="bottom-nav d-md-none">
    <a href="{{ route('guru.dashboard') }}"
        class="bottom-nav-item {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
        <i class="bi bi-speedometer2"></i>
        <span>Beranda</span>
    </a>
    <a href="{{ route('guru.bank-soal.index') }}"
        class="bottom-nav-item {{ request()->routeIs('guru.bank-soal.*') ? 'active' : '' }}">
        <i class="bi bi-journal-text"></i>
        <span>Bank Soal</span>
    </a>
    <a href="{{ route('guru.ujian.index') }}"
        class="bottom-nav-item {{ request()->routeIs('guru.ujian.*') ? 'active' : '' }}">
        <i class="bi bi-pencil-square"></i>
        <span>Ujian</span>
    </a>
    <a href="{{ route('guru.hasil.index') }}"
        class="bottom-nav-item {{ request()->routeIs('guru.hasil.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line"></i>
        <span>Hasil</span>
    </a>
    <button type="button" class="bottom-nav-item" data-sidebar-toggle>
        <i class="bi bi-list"></i>
        <span>Menu</span>
    </button>
</nav>
