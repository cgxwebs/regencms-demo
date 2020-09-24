@extends('layouts.dashboard_page')

@if(is_null($edit))
    @section('header_title', 'Create New Story')
@else
    @section('header_title', 'Edit Story')
    @section('header_button', 'Preview Story')
    @section('header_button_route', route('hq.stories.show', ['story' => $edit]))
@endif

@section('content')
@parent

<form action="{{ $action }}" enctype="multipart/form-data" method="post" id="story-form">
    @csrf
    @unless( is_null($edit) ) @method('PUT') @endunless
    @include(
        'blocks.form-messages',
        ['success_notice_message' => '<a href="'.session('save_view_url').'">Click here to preview story.</a>']
    )

    <div class="flex flex-row-reverse">
        <div class="flex-1 pl-8">
            <x-admin-form-element
                type="select"
                name="status"
                display_name="Status"
                :options="$status_enum"
                :default="optional($edit)->status ?? 'draft'"
            />

            <x-admin-form-element
                type="input.text"
                name="slug"
                display_name="Slug"
                form_style="small"
                :default="optional($edit)->slug ?? ''"
            />

            <x-admin-form-element
                type="{{ $author->isSuper() ? 'list' : 'list_only' }}"
                name="tags"
                display_name="Tags"
                :options="$tags_list"
                :default="$tags_default"
                placeholder="Add new, sep with commas"
            />

            @unless(is_null($edit))
            <div id="story-metadata">
                <div class="pb-4">
                    <div class="text-gray-600 text-sm uppercase">Author</div>
                    <div class="text-gray-700 leading-loose">{{ $edit->user->username }}</div>
                </div>
                <div class="pb-4">
                    <div class="text-gray-600 text-sm uppercase">Last Update</div>
                    <div class="text-gray-700 leading-loose">
                        <span title="{{ $edit->updated['pretty'] }}">{{ $edit->updated['human'] }}</span>
                    </div>
                </div>
                <div class="pb-4">
                    <div class="text-gray-600 text-sm uppercase">Created</div>
                    <div class="text-gray-700 leading-loose">
                        <span title="{{ $edit->created['human'] }}">{{ $edit->created['pretty'] }}</span>
                    </div>
                </div>
            </div>
            @endunless

        </div>

        <div class="w-9/12">
            <x-admin-form-element
                type="input.text"
                name="title"
                display_name="Title"
                :default="optional($edit)->title ?? ''"
            />

            @error('body.*')
            <div class="block py-2 text-sm text-red-500 leading-normal">
                @foreach($errors->get('body.*') as $message)
                    {{ $message[0] }}<br/>
                @endforeach
            </div>
            @enderror

            @error('body')
            <div class="block uppercase font-bold py-2 text-sm text-red-500">{{ $message }}</div>
            @enderror

            <div id="story-editor-vue">
                <story-content-list
                    :is-editing="{{ is_null($edit) ? 'false' : 'true' }}"
                    :content-list-default='@json(old('body', optional($edit)->getBodyMapAsArray() ?? ''),
                        JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)'
                ></story-content-list>
            </div>
        </div>
    </div>

    @include('blocks.form-actions', [
        'submit_button_label' => 'Save Story',
        'red_button_label' => 'Delete Story',
        'red_button_show' => !is_null($edit),
        'red_button_route' => $delete ?? ''
    ])
</form>
@endsection

@push('scripts')
    <script src="{{ mix('js/story-editor-vue.js') }}" defer></script>
@endpush
