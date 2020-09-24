<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frontend Demo</title>
    <link href="{{ mix('css/base.css') }}" rel="stylesheet">
    <link href="{{ mix('css/ramsay.css') }}" rel="stylesheet">

</head>
<body class="antialiased @if(!$is_staging) bg-gray-200 @else bg-blue-800 @endif">

<div class="container mx-auto bg-white flex flex-col">

    <nav class="flex flex-wrap justify-evenly border-b border-black">
        <a href="/" class="p-4 text-2xl">Gordon Ramsay</a>
        <a href="/about" class="p-4 text-2xl">About Gordon</a>
        <a href="/recipes" class="p-4 text-2xl">Recipes</a>
        @if($is_staging)
            <a href="/locations" class="p-4 text-2xl">Restaurants</a>
        @endif
    </nav>

    @sectionMissing('no_banner')
    <div id="banner" style="background-image: url(@yield('banner', '/media/um/qsur-6pgl-qnft.jpeg'));">
    </div>
    @endif

    <div class="flex py-8 px-6">
        @hasSection('side')
            <div class="lg:w-1/4 lg:pr-4">
                @yield('side')
            </div>
            <div class="lg:w-3/4 lg:pl-4">
                @yield('content')
            </div>
        @else
            <div class="w-full">
                @yield('content')
            </div>
        @endif
    </div>

    <div class="border-t border-black px-4 py-6 text-center text-sm text-gray-700">
        Disclaimer: This website is for demo purposes only.<br/>
        All contents were borrowed from GordonRamsay.com.
    </div>

</div>

@stack('scripts')
</body>

</html>
