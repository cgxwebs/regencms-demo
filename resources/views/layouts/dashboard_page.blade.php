@extends('layouts.dashboard')

@section('content')
    <div class="pb-6 mb-12 border-b-2">
        @hasSection('header_button')
        <a class="inline-block rounded-md py-3 px-6 bg-teal-500 hover:bg-teal-700 text-white shadow-md uppercase float-right"
           href="@yield('header_button_route')">@yield('header_button')</a>
        @endif
        <h1 class="text-3xl text-teal-700">
            @yield('header_title')
        </h1>
    </div>
@endsection
