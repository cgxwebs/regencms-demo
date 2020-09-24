@extends('layouts.dashboard_page')

@section('header_title', 'Delete ' . $item_type)

@section('content')
    @parent

    <form action="{{ $action }}" method="post">
        @csrf
        @method('DELETE')

        <div class="text-center text-2xl leading-loose">
            Are you sure you want to permanently delete<br/>
            <strong>{{ $item_name }}</strong>?
        </div>

        <div class="bg-gray-700 shadow-xl rounded-lg p-10 w-1/2 my-10 mx-auto">
            <label class="block text-gray-100 text-lg mb-6">
                Please enter your account password to continue:
                @error('password')
                <span class="block leading-loose text-red-500">{{ $message }}</span>
                @enderror
            </label>
            <input
                name="password"
                autocomplete="off"
                class="appearance-none border-0 rounded w-full bg-gray-200 text-gray-700 leading-tight text-xl p-4"
                type="password"
            />
        </div>

        @include('blocks.form-actions', [
            'submit_button_label' => 'Yes, continue.',
            'red_button_label' => 'No, go back.',
            'red_button_show' => true,
            'red_button_route' => $back_route
        ])

    </form>
@endsection
