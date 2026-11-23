<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body>

    <div class="wrapper">

        @include('partials.guru.sidebar')

        <div class="main">

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