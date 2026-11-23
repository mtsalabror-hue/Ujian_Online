<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">

    <div class="container-fluid">

        <button class="btn btn-outline-secondary" id="toggleSidebar">
            <i class="bi bi-list"></i>
        </button>

        <h5 class="ms-3 mb-0">
            @yield('title')
        </h5>

        <div class="ms-auto dropdown">

            <a href="#"
                class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown">

                <i class="bi bi-person-circle fs-4 me-2"></i>

                <span class="d-none d-sm-inline">{{ auth()->user()->username }}</span>

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

        </div>

    </div>

</nav>