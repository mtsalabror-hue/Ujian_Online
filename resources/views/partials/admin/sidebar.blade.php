<div class="sidebar">

    <div class="sidebar-header  text-center py-3 border-bottom border-light-subtle">
        <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
            alt="Logo"
            width="65"
            class="mb-2">
        <h5 class="mb-0 text-white fw-bold fs-6">{{ $setting->nama_sekolah ?? 'SMK IT AL-ABROR' }}</h5>
        <small class="text-white-50">Panel Admin</small>
    </div>

    <ul class="menu">

        <li>
            <a href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </span>
            </a>
        </li>

        @php
        $isDataMaster = request()->routeIs(
        'admin.guru.*',
        'admin.siswa.*',
        'admin.kelas.*',
        'admin.mapel.*',
        'admin.mata-pelajaran.*',
        'admin.tahun-ajaran.*'
        );
        @endphp

        <li>
            <a href="#" class="dropdown-btn {{ $isDataMaster ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-folder"></i>
                    <span>Data Master</span>
                </span>
                <i class="bi bi-chevron-down arrow-icon"></i>
            </a>

            <ul class="submenu {{ $isDataMaster ? 'show' : '' }}">

                <li>
                    <a href="{{ route('admin.guru.index') }}"
                        class="{{ request()->routeIs('admin.guru.*') ? 'active' : '' }}">
                        <i class="bi bi-person-video3"></i>
                        <span>Data Guru</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.siswa.index') }}"
                        class="{{ request()->routeIs('admin.siswa.index') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard"></i>
                        <span>Data Siswa</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.kelas.index') }}"
                        class="{{ request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Data Kelas</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.mata-pelajaran.index') }}"
                        class="{{ request()->routeIs('admin.mata-pelajaran.*') ? 'active' : '' }}">
                        <i class="bi bi-book"></i>
                        <span>Mata Pelajaran</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.tahun-ajaran.index') }}"
                        class="{{ request()->routeIs('admin.tahun-ajaran.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar3"></i>
                        <span>Tahun Ajaran</span>
                    </a>
                </li>

            </ul>
        </li>

        <li>
            <a href="{{ route('admin.pengguna.index') }}"
                class="{{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-people-fill"></i>
                    <span>Kelola Pengguna</span>
                </span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.upload-guru.index') }}"
                class="{{ request()->routeIs('admin.upload-guru.*') ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-cloud-arrow-up"></i>
                    <span>Status Upload Guru</span>
                </span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.hasil-siswa.index') }}"
                class="{{ request()->routeIs('admin.hasil-siswa.*') ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-trophy"></i>
                    <span>Hasil Siswa</span>
                </span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.pengaturan.index') }}"
                class="{{ request()->routeIs('admin.pengaturan.*') ? 'active' : '' }}">
                <span class="menu-label">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan</span>
                </span>
            </a>
        </li>

    </ul>

</div>