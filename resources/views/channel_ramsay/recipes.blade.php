@extends('channel_ramsay.layout')

@if($viewing)
    @section('side')
        @foreach($categories as $c)
            <div>
                <span class="block font-bold uppercase py-2">{{ $c->title }}</span>
                @foreach($recipes[$c->name] as $r)
                    <a class="block py-2" href="{{ route(($is_staging ? 'staging_' : '').'recipes', ['id' => $r->id]) }}">{{ $r->title }}</a>
                @endforeach
            </div>
        @endforeach
    @endsection

    @section('content')
        <section class="prose max-w-full">
            <header>
                <h1>{{ $viewing->title }}</h1>
            </header>
            <article>
                <h2>Ingredients</h2>
                {!! $viewing->readable->get('ingredients')->withFilter()->convertRelativeUrls()->get() !!}

                <h2>Cooking Instructions</h2>
                {!! $viewing->readable->get('default')->withFilter()->convertRelativeUrls()->get() !!}
            </article>
        </section>
    @endsection


    @if($viewing->readable->get('banner'))
        @section('banner', $viewing->readable->get('banner')->getContent())
    @endif

@else

    @section('content')
        <div class="text-center text-lg leading-loose">
            @foreach($categories as $c)
                <div>
                    <span class="block font-bold uppercase py-2">{{ $c->title }}</span>
                    @foreach($recipes[$c->name] as $r)
                        <a class="block py-2" href="{{ route(($is_staging ? 'staging_' : '').'recipes', ['id' => $r->id]) }}">{{ $r->title }}</a>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endsection

@endif
