<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">

    <div class="container-fluid">

        <button class="btn btn-outline-secondary me-3" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>

        <span class="navbar-brand fw-semibold">
            Dashboard Guru
        </span>

        <ul class="navbar-nav ms-auto">

            <li class="nav-item dropdown">

                <a class="nav-link dropdown-toggle d-flex align-items-center"
                    href="#"
                    role="button"
                    data-bs-toggle="dropdown">

                    <i class="bi bi-person-circle fs-4"></i>

                    <div class="text-start d-none d-sm-block">

                        <div class="fw-semibold ms-2">
                            {{ auth()->user()->guru->nama_guru ?? auth()->user()->username }}
                        </div>

                        <small class="text-muted ms-2">
                            Guru
                        </small>

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