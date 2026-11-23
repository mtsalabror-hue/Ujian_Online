<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-3 siswa-navbar">

    <div class="container-fluid px-2">

        <span class="navbar-brand fw-semibold mb-0 d-flex align-items-center">
            <img src="{{ !empty($setting->logo) ? asset('storage/' . $setting->logo) : asset('images/logoo.png') }}"
                alt="Logo MTs Al-Abror"
                class="me-2"
                style="height: 32px; width: auto;">
            Dashboard Siswa
        </span>

        <ul class="navbar-nav ms-auto align-items-center">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 px-2"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                    <i class="bi bi-person-circle fs-4 text-success"></i>

                    <div class="text-start d-none d-md-block lh-sm">
                        <div class="fw-semibold text-dark">
                            {{ auth()->user()->siswa->nama_siswa ?? auth()->user()->name }}
                        </div>
                        <small class="text-muted">Siswa</small>
                    </div>

                </a>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <span class="dropdown-item-text">
                            <strong>{{ ucfirst(auth()->user()->role) }}</strong>
                        </span>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="btn btn-danger btn-sm">
                                <i class="bi bi-box-arrow-right"></i>
                                Logout
                            </button>
                        </form>
                    </li>

                </ul>

            </li>

        </ul>

    </div>

</nav>