<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body>

    <div class="wrapper">

        <div class="main">

            @include('partials.siswa.navbar')

            <main class="content p-4">
                @yield('content')
            </main>

            @include('partials.footer')

        </div>

    </div>

    @include('partials.scripts')

</body>

</html>