<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body class="@yield('body-class')">

    <div class="wrapper">

        @include('partials.admin.sidebar')

        <div class="main">

            @include('partials.admin.navbar')

            <main class="content p-4">
                @yield('content')
            </main>

            @include('partials.footer')

        </div>

    </div>

    @include('partials.scripts')

</body>

</html>