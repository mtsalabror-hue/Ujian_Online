<!DOCTYPE html>
<html lang="id">

@include('partials.head')

<body>

    <div class="wrapper">

        <div class="main">

            <main class="content">
                @yield('content')
            </main>

        </div>

    </div>

    @include('partials.scripts')

</body>

</html>
