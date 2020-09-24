@extends('layouts.dashboard_page')

@section('header_title', 'Edit Media')
@section('header_button', 'Upload New Files')
@section('header_button_route', route('hq.media.index'))


@section('content')
@parent

    <form action="{{ $action }}" enctype="multipart/form-data" method="post">
        @csrf
        @unless( is_null($edit) ) @method('PUT') @endunless
        @include(
            'blocks.form-messages',
            ['success_notice_message' => 'Media saved successfully.']
        )

        <x-admin-form-element
            type="input.text"
            name="filename"
            display_name="filename"
            :default="optional($edit)->getFilename() ?? ''"
            is_required="true"
            placeholder="Alphanumeric, underscore and dots only"
        />

        <x-admin-form-element
            type="input.text"
            name="description"
            display_name="description"
            :default="optional($edit)->description ?? ''"
        />

        @include('blocks.form-actions', [
            'submit_button_label' => 'Save Media',
            'red_button_label' => 'Delete Media',
            'red_button_show' => !is_null($edit),
            'red_button_route' => $delete ?? ''
        ])
    </form>
@endsection
