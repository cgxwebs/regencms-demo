@extends('channel_ramsay.layout')

@section('banner', 'media/zl/y9or-b01o-ns2r.jpeg')

@section('content')
    @if ($is_staging)
    <p class="bg-yellow-200 text-yellow-700 p-20 text-xl text-center leading-loose mb-10">
        This demonstrates how channels can be used to setup different environments like staging.
    </p>
    @endif

    <p class="bg-gray-300 text-gray-700 p-20 text-xl text-center leading-loose">
        This demo website re-creates some of the contents from the original GordonRamsay.com,
        to showcase RegenCMS and its PHP-based frontend.
        <br/><br/>
        No copyright infringement intended.
    </p>
@endsection
