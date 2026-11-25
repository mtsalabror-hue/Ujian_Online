<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body>

    <div class="wrapper">

        @include('partials.guru.sidebar')

        <div class="main">

            {{-- Banner saat admin sedang masuk sebagai guru --}}
            @if(session('impersonate_admin_id'))
            <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0 rounded-0 px-4 py-2">
                <span>
                    <i class="bi bi-eye me-1"></i>
                    Anda sedang masuk sebagai
                    <strong>{{ auth()->user()->guru->nama_guru ?? auth()->user()->username }}</strong>
                    (mode pantau admin).
                </span>
                <form action="{{ route('impersonate.kembali') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-dark">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Kembali ke Akun Admin
                    </button>
                </form>
            </div>
            @endif

            @include('partials.guru.navbar')

            <main class="content p-4">
                @yield('content')
            </main>

            @include('partials.footer')

        </div>

    </div>

    @include('partials.scripts')

</body>

</html>