<div class="sidebar">

    <div class="sidebar-header text-center py-4 border-bottom border-light-subtle">
        <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
            alt="Logo"
            width="65"
            class="mb-2">
        <h5 class="mb-0 text-white fw-bold fs-6">{{ $setting->nama_sekolah ?? 'MTs Al-Abror' }}</h5>
        <small class="text-white-50">Panel Guru</small>
    </div>

    @php
        $isKelolaUjian = request()->routeIs('guru.ujian.*');
        $isHasilUjian = request()->routeIs('guru.hasil.*');
    @endphp

    <ul class="menu">

        {{-- Dashboard --}}
        <li>
            <a href="{{ route('guru.dashboard') }}"
                class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                <span>
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </span>
            </a>
        </li>

        {{-- Kelola Bank Soal --}}
        <li>
            <a href="{{ route('guru.bank-soal.index') }}"
                class="{{ request()->routeIs('guru.bank-soal.*') ? 'active' : '' }}">
                <span>
                    <i class="bi bi-journal-text me-2"></i>
                    Kelola Bank Soal
                </span>
            </a>
        </li>

        {{-- Kelola Ujian --}}
        <li>
            <a href="#" class="dropdown-btn {{ $isKelolaUjian ? 'active' : '' }}">
                <span>
                    <i class="bi bi-pencil-square me-2"></i>
                    Kelola Ujian
                </span>
                <i class="bi bi-chevron-down"></i>
            </a>

            <ul class="submenu {{ $isKelolaUjian ? 'show' : '' }}">
                <li>
                    <a href="{{ route('guru.ujian.jadwal') }}"
                        class="{{ request()->routeIs('guru.ujian.jadwal') ? 'active' : '' }}">
                        <i class="bi bi-calendar-event me-2"></i>
                        Buat Jadwal Ujian
                    </a>
                </li>
                <li>
                    <a href="{{ route('guru.ujian.peserta') }}"
                        class="{{ request()->routeIs('guru.ujian.peserta') ? 'active' : '' }}">
                        <i class="bi bi-people me-2"></i>
                        Atur Ujian Peserta
                    </a>
                </li>
                <li>
                    <a href="{{ route('guru.ujian.publish') }}"
                        class="{{ request()->routeIs('guru.ujian.publish') ? 'active' : '' }}">
                        <i class="bi bi-send me-2"></i>
                        Publish Ujian
                    </a>
                </li>
            </ul>
        </li>

        {{-- Hasil Ujian --}}
        <li>
            <a href="#" class="dropdown-btn {{ $isHasilUjian ? 'active' : '' }}">
                <span>
                    <i class="bi bi-bar-chart-line me-2"></i>
                    Hasil Ujian
                </span>
                <i class="bi bi-chevron-down"></i>
            </a>

            <ul class="submenu {{ $isHasilUjian ? 'show' : '' }}">
                <li>
                    <a href="{{ route('guru.hasil.index') }}"
                        class="{{ request()->routeIs('guru.hasil.index') ? 'active' : '' }}">
                        <i class="bi bi-eye me-2"></i>
                        Lihat Hasil Ujian
                    </a>
                </li>
                <li>
                    <a href="{{ route('guru.hasil.peserta') }}"
                        class="{{ request()->routeIs('guru.hasil.peserta') ? 'active' : '' }}">
                        <i class="bi bi-person-check me-2"></i>
                        Hasil Peserta
                    </a>
                </li>
                <li>
                    <a href="{{ route('guru.hasil.export') }}"
                        class="{{ request()->routeIs('guru.hasil.export') ? 'active' : '' }}">
                        <i class="bi bi-printer me-2"></i>
                        Cetak / Export
                    </a>
                </li>
            </ul>
        </li>

    </ul>

</div>