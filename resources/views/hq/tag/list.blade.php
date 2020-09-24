@extends('layouts.dashboard_2col')

@section('header_title', 'Tags')

@unless(is_null($edit))
    @section('header_button', 'Create New Tag')
    @section('header_button_route', route('hq.tags.index'))
@endunless

@section('top_content')
    @include(
        'blocks.form-messages',
        ['success_notice_message' => 'Tag has been saved.']
    )
@endsection

@section('left_content')
    @if (count($tags) === 0)
        <p>Create tags now and see them listed here.</p>
    @endif

    @foreach($tags as $t)
        <div class="pb-10">
            <a href="{{ $t->route }}" class="inline-block text-lg text-teal-700 leading-normal">
                {{ $t->name }}
            </a>
            <span class="text-gray-500 text-sm block leading-normal">{{ $t->title }}</span>
        </div>
    @endforeach
@endsection

@section('right_content')
    <form action="{{ $action }}" enctype="multipart/form-data" method="post">
        @csrf
        @unless( is_null($edit) ) @method('PUT') @endunless

        <x-admin-form-element
            type="input.text"
            name="name"
            display_name="name" :
            is_required="true"
            :default="optional($edit)->name ?? ''"
            placeholder="Alphanumeric and underscores only"
        />

        <div class="form-list-notice-box">
            * Tags may have a parent by prefixing a name followed by a dot: (parent.child)<br/>
            * A child tag's visibility is also dependent on its parent<br/>
            * Unlisted will not be displayed on lists but can still be retrieved individually
        </div>

        <x-admin-form-element
            type="radio"
            name="visibility"
            display_name="Visibility"
            :options="$visibility_enum"
            nolabel="true"
            :default="optional($edit)->visibility ?? 'visible'"
        />

        <x-admin-form-element
            type="input.text"
            name="title"
            display_name="Label"
            form_style="small"
            :default="optional($edit)->title ?? ''"
            placeholder=""
        />

        @include('blocks.form-actions', [
            'submit_button_label' => 'Save Tag',
            'red_button_label' => 'Delete Tag',
            'red_button_show' => (!is_null($edit) && Auth::user()->isSuper()),
            'red_button_route' => $delete ?? ''
        ])

    </form>
@endsection
