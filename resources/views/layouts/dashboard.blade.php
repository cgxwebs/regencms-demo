<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Regen CMS &mdash; Dashboard</title>
    <link href="{{ mix('css/base.css') }}" rel="stylesheet">
    <link href="{{ mix('css/dashboard.css') }}" rel="stylesheet">
</head>
<body class="bg-gray-400 antialiased leading-none min-h-full">
    <div class="min-h-full h-screen">


        <nav class="bg-gray-700 p-6 shadow-xl text-gray-400 " style="height: 200px;">
            <div class="flex">
                <div class="flex-1 no-underline">
                    @auth
                        <a href="{{ route('hq.stories.index') }}" class="pr-3">Stories</a>
                    @endauth
                    @can('can-modify', 'media')
                        <a href="{{ route('hq.media.index') }}" class="pr-3">Media</a>
                    @endcan
                    @can('can-modify', 'channels')
                        <a href="{{ route('hq.channels.index') }}" class="pr-3">Channels</a>
                    @endcan
                    @can('can-modify', 'tags')
                        <a href="{{ route('hq.tags.index') }}" class="pr-3">Tags</a>
                    @endcan
                    @can('can-modify', 'users')
                        <a href="{{ route('hq.users.index') }}" class="pr-3">Users</a>
                    @endcan
                    <a href="{{ route('api-usage') }}" class="pr-3">API</a>
                </div>
                <div class="flex-grow text-center">
                    <img class="w-10 block mt-2" src="{{ url('img/water.svg') }}" />
                </div>
                <div class="float-right cursor-pointer">
                    @guest
                        <a href="{{ route('login') }}">Login</a>
                    @endguest
                    @auth
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="inline-block">
                        {{ csrf_field() }}
                        <button type="submit">Logout ({{ Auth::user()->username }})</button>
                    </form>
                    @endauth
                </div>
            </div>

        </nav>


        <div class="container p-10 bg-white mx-auto -mt-24 min-h-full shadow-2xl rounded-t-lg">
            @yield('content')
        </div>

    </div>

@stack('scripts')
</body>

</html>
