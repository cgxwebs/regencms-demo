@extends('layouts.dashboard_2col')

@section('header_title', 'Stories')
@if(Auth::user()->can('create', \App\Story::class))
    @section('header_button', 'Create New Story')
    @section('header_button_route', route('hq.stories.create'))
@endif

@section('top_content')
    @include('blocks.form-messages')
@endsection

@section('left_content')
    <form method="GET" action="{{ route('hq.stories.index') }}" class="flex flex-col">
        <select name="sort_channel" class="form-select border-0 text-sm text-gray-600 my-1">
            <option value="0">All Channels</option>
            @foreach ($channel_list as $ch)
                <option value="{{ $ch->id }}" @if($defaults['sort_channel'] == $ch->id) selected @endif>{{ $ch->name }}</option>
            @endforeach
        </select>
        <select name="sort_tag" class="form-select border-0 text-sm text-gray-600 my-1">
            <option value="0">All Tags</option>
            @foreach ($tag_list as $t)
                <option value="{{ $t->id }}" @if($defaults['sort_tag'] == $t->id) selected @endif>{{ $t->name }}</option>
            @endforeach
        </select>
        <select name="sort_status" class="form-select border-0 text-sm text-gray-600 my-1">
            <option value="all">All Status</option>
            @foreach ($status_list as $stat => $val)
                <option value="{{ $val }}" @if($defaults['sort_status'] == $val) selected @endif>{{ $stat }}</option>
            @endforeach
        </select>
        <select name="sort_order" class="form-select border-0 text-sm text-gray-600 my-1">
            @foreach ($order_list as $val => $key)
                <option value="{{ $val }}" @if($defaults['sort_order'] == $val) selected @endif>{{ $key }}</option>
            @endforeach
        </select>
        <button type="submit" class="border-0 bg-teal-500 hover:bg-teal-700 text-sm text-white rounded py-3 px-4 my-1">SORT</button>
    </form>
@endsection

@section('right_content')
    @if (count($stories) === 0)
        <p>Create stories now and see them listed here.</p>
    @endif

    @foreach($stories as $s)
        <div class="pb-6">

            <a href="{{ route('hq.stories.edit', ['story' => $s]) }}"
               class="inline-block pb-2 text-lg leading-normal @if ($s->status === 'published') text-teal-700 @else text-gray-500 @endif"
               title="{{ ucfirst($s->status) }}">

                @if ($s->status === 'draft')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" height="16" width="16" class="inline-block">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                @endif

                @if ($s->status === 'unlisted')
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" height="16" width="16" class="inline-block">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                @endif

                {{ $s->title ?? '(untitled)' }}
            </a>
            <div class="text-gray-500 text-sm">
                <span class="pr-1"><a href="{{ route('hq.stories.show', ['story' => $s]) }}" class="text-gray-600 hover:underline">Preview</a></span>
                <span class="pr-1 cursor-default" title="Updated {{ $s->updated['pretty'] }}">{{ $s->updated['human'] }}</span>
            </div>
        </div>
    @endforeach

    @if($prev_page || $next_page)
    <div class="pt-6">
        @if($prev_page)
            <a href="{!! $prev_page !!}"
               class="px-6 py-2 bg-teal-100 text-teal-600 rounded hover:bg-teal-200 @if($next_page) mr-2 @endif"
            >Prev</a>
        @endif
        @if($next_page)
            <a href="{!! $next_page !!}" class="px-6 py-2 bg-teal-100 text-teal-600 rounded hover:bg-teal-200">Next</a>
        @endif
    </div>
    @endif
@endsection
