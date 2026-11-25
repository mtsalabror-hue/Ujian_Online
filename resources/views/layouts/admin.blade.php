<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body class="@yield('body-class')">

    <div class="wrapper">

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        @include('partials.admin.sidebar')

        <div class="main">

            @include('partials.admin.navbar')

            <main class="content p-4">
                @yield('content')
            </main>

            @include('partials.footer')

        </div>

    </div>

    @include('partials.admin.bottomnav')

    @include('partials.scripts')

</body>

</html>