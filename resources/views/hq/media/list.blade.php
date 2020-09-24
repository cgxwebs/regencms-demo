@extends('layouts.dashboard_2col')

@section('header_title', 'Media Storage')



@section('content')
    @parent
    @include(
        'blocks.form-messages',
        ['success_notice_message' => 'Tag has been saved.']
    )

    <div class="m-1">
        <form action="{{ $action }}" enctype="multipart/form-data" method="post">
            @csrf

            @error('media.*')
            <div class="block text-sm text-red-500 pb-4">
                @foreach($errors->get('media.*') as $message)
                    {{ $message[0] }}<br/>
                @endforeach
            </div>
            @enderror

            <x-admin-form-element
                type="filemulti"
                name="media"
                nolabel="true"
                display_name=""
            />

            <x-admin-form-element
                type="submit"
                name="submit"
                nolabel="true"
                nowrapper="true"
                form_style="small"
                display_name="UPLOAD"
            />

            <div class="mt-6 form-list-notice-box">
                * Maximum of {{ config('regencms.media_batch') }} file uploads at a time<br/>
                * Maximum file size of {{ config('regencms.media_filesize') }} MB<br/>
                * Accepted file types are {{ implode(", ", config('regencms.media_mimetypes')) }}
            </div>

        </form>

    </div>

    <div class="flex flex-wrap">
    @foreach($media_index as $m)
        <div class="flex w-1/3 p-1">
            <div class="flex flex-col w-full border-gray-400 rounded-md border">
                <a class="flex-grow block px-6 py-6 text-center leading-loose " href="{{ url($m->filepath) }}">
                    @if($m->isImage())
                        <svg class="inline-block text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" height="32" width="32">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @else
                        <svg class="inline-block text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" height="32" width="32">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                        </svg>
                    @endif
                    <span class="block text-teal-600">{{ $m->description, 0, 120 }}</span>
                    <span class="block text-gray-600 text-sm">
                        {{ $m->filepath }}
                    </span>
                    <span class="block text-gray-600 text-sm" title="{{ $m->created['pretty']  }}">
                       {{ $m->created['human'] }} &middot; {{ $m->filesize }} MB &middot; by {{ $m->user->username }}
                    </span>
                </a>
                <div class="text-gray-600 text-sm flex flex-wrap text-center">
                    <a class="p-2 w-1/2 block bg-gray-300 hover:bg-teal-300" href="{{ route('hq.media.edit', $m) }}">
                        Edit
                    </a>
                    <a class="p-2 w-1/2 block bg-gray-200" href="{{ route('hq.media.delete', $m) }}">
                        Remove
                    </a>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <div class="pt-6">
        @if($media_index->previousPageUrl())
            <a href="{{ $media_index->previousPageUrl() }}"
               class="px-6 py-2 bg-teal-100 text-teal-600 rounded hover:bg-teal-200 @if($media_index->nextPageUrl()) mr-2 @endif"
            >Prev</a>
        @endif
        @if($media_index->nextPageUrl())
            <a href="{{ $media_index->nextPageUrl() }}" class="px-6 py-2 bg-teal-100 text-teal-600 rounded hover:bg-teal-200">Next</a>
        @endif
    </div>

@endsection
