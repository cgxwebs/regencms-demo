@extends('layouts.dashboard_2col_rev')

@section('header_title', 'Preview: ' . $story->title ?? '(untitled)')
@can('write', $story)
    @section('header_button', 'Edit Story')
    @section('header_button_route', route('hq.stories.edit', ['story' => $story]))
@endcan

@section('right_content')
    <article class="leading-normal">
        @foreach($story->getReadableAttribute() as $content)
        <section class="text-gray-900 pb-6 prose w-full max-w-full">
            <div>
                <span class="uppercase text-xs text-white bg-teal-400 px-4 py-1 rounded-full leading-none">
                    {{ $content->getName() }}
                </span>
            </div>
            @if ($content->getFormat() === 'plaintext' || $content->getFormat() === 'json')
                <div class="whitespace-pre-wrap font-mono">{!!
                    $content->withFilter($story)->escapePlaintext()->convertLinebreaks()->get()
                !!}</div>
            @else
                <div>{!!
                    $content->withFilter($story)->convertRelativeUrls()->get()
                !!}</div>
            @endif
        </section>
        @endforeach
    </article>
@endsection

@section('left_content')
    <div id="story-metadata" class="bg-teal-100 p-6">
        <div class="pb-6">
            <div class="text-gray-600 text-sm uppercase">Author</div>
            <div class="text-gray-700 leading-loose">{{ $story->user->username }}</div>
        </div>
        <div class="pb-6">
            <div class="text-gray-600 text-sm uppercase">Last Update</div>
            <div class="text-gray-700 leading-loose">
                <span title="{{ $story->updated['pretty'] }}">{{ $story->updated['human'] }}</span>
            </div>
        </div>
        <div>
            <div class="text-gray-600 text-sm uppercase">Created</div>
            <div class="text-gray-700 leading-loose">
                <span title="{{ $story->created['human'] }}">{{ $story->created['pretty'] }}</span>
            </div>
        </div>
        @if(count($story->tags) > 0)
        <div class="pt-6">
            <div class="text-gray-600 text-sm uppercase">Tags and Channels</div>
            <div class="text-gray-700 leading-loose mt-1">
                @foreach($story->tags as $tag)
                    <div class="leading-none py-2">
                        <span class="text-gray-700 text-sm pb-1 block">{{ $tag->name }}</span>
                        <div>
                            @foreach($tag->channels as $channel)
                                <span class="text-xs text-teal-600 pr-1">{{ $channel->name }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
@endsection
