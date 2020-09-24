@extends('layouts.dashboard_page')

@section('content')
    @parent
    @yield('top_content')
    <div class="flex">
        <div class="flex-1 pr-8">
            @yield('left_content')
        </div>
        <div class="w-9/12">
            @yield('right_content')
        </div>
    </div>
    @yield('bottom_content')
@endsection
