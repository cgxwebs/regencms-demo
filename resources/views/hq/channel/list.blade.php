@extends('layouts.dashboard_2col')

@section('header_title', 'Channels')
@unless(is_null($edit))
@can('is-superuser')
    @section('header_button', 'Create New Channel')
    @section('header_button_route', route('hq.channels.index'))
@endcan
@endunless

@section('top_content')
    @include(
        'blocks.form-messages',
        ['success_notice_message' => 'Channel has been saved.']
    )
@endsection

@section('left_content')
    @if (count($channels) === 0)
        <p>Create channels now and see them listed here.</p>
    @endif

    @foreach($channels as $c)
        <div class="pb-10">
            <a href="{{ $c->route }}" class="inline-block text-lg text-teal-700 leading-normal">
                {{ $c->name }}
            </a>
            <span class="text-gray-500 text-sm block leading-normal">{{ $c->title }}</span>
        </div>
    @endforeach
@endsection

@section('right_content')
    <form action="{{ $action }}" enctype="multipart/form-data" method="post">
        @csrf
        @unless( is_null($edit) ) @method('PUT') @endunless

        @if(Auth::user()->can('is-superuser') || (Auth::user()->isEditor() && !is_null($edit)))
            <x-admin-form-element
                type="input.text"
                name="name"
                display_name="name"
                is_required="true"
                :default="optional($edit)->name ?? ''"
                placeholder="Alphanumeric, underscore and dots only"
            />

            <x-admin-form-element
                type="input.text"
                name="title"
                display_name="Title"
                form_style="small"
                :default="optional($edit)->title ?? ''"
                placeholder="Display title"
            />

            <x-admin-form-element
                type="input.text"
                name="url"
                display_name="Hostname"
                form_style="small"
                is_required="true"
                :default="optional($edit)->url ?? ''"
                placeholder="Valid hostname or domain name of your frontend"
            />

            <x-admin-form-element
                type="list_only"
                name="tags"
                form_style="wide"
                is_required="true"
                display_name="Tag Access"
                :options="$tags_list"
                :default="$tags_default"
                placeholder="Add new unique tags separated by commas"
            />

            @include('blocks.form-actions', [
                'submit_button_label' => 'Save Channel',
                'red_button_label' => 'Delete Channel',
                'red_button_show' => !is_null($edit),
                'red_button_route' => $delete ?? ''
            ])
        @else
            <div class="bg-gray-200 py-20 px-4 text-center">
                Please select a channel on the left, to modify.
            </div>
        @endif

    </form>
@endsection
