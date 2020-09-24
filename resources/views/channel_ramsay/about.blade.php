@extends('channel_ramsay.layout')
@section('side')
    @foreach($articles as $a)
        <a class="block pb-4" href="{{ route(($is_staging ? 'staging_' : '').'about', ['slug' => $a->slug]) }}">{{ $a->title }}</a>
    @endforeach
@endsection

@section('content')
    <section class="prose max-w-full">
        <header>
            <h1>{{ $viewing->title }}</h1>
        </header>
        <article>
            {!! $viewing->readable->get('default')->withFilter($viewing)->convertRelativeUrls()->get() !!}

            @if($slug === 'books')
                <div class="flex flex-wrap">
                @foreach($viewing->readable->get('books')->getContent() as $item)
                    <div class="lg:w-1/2 py-2 pr-2">
                        <h3>{{ $item['title'] }}</h3>
                        @if($item['image'])
                            <img src="{{ $item['image'] }}" />
                        @endif
                        <p>{{ $item['description'] }}</p>
                    </div>
                @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection

@if($viewing->readable->get('banner'))
    @section('banner', $viewing->readable->get('banner')->getContent())
@else
    @section('no_banner', true)
@endif
