@extends('layouts.dashboard_2col')

@section('header_title', 'Users')

@unless(is_null($edit))
    @section('header_button', 'Create New User')
@section('header_button_route', route('hq.users.index'))
@endunless

@section('top_content')
    @include(
        'blocks.form-messages',
        ['success_notice_message' => 'User has been saved.']
    )
@endsection

@section('left_content')
    @if (count($users) === 0)
        <p>Create users now and see them listed here.</p>
    @endif

    @foreach($users as $u)
        <div class="pb-10">
            <a href="{{ route('hq.users.edit', ['user' => $u])  }}"
               class="inline-block text-lg leading-normal
               @if($u->username === config('regencms.root_username')) text-red-600
               @elseif($u->role === 'disabled') text-gray-500
               @else text-teal-700 @endif"
           >{{ $u->username }}</a>
            <span class="text-gray-500 text-sm block leading-normal">{{ $u->role }}</span>
        </div>
    @endforeach
@endsection

@section('right_content')
    <form action="{{ $action }}" enctype="multipart/form-data" method="post">
        @csrf
        @unless( is_null($edit) ) @method('PUT') @endunless

        <x-admin-form-element
            type="input.text"
            name="username"
            display_name="Username"
            is_required="true"
            :default="optional($edit)->username ?? ''"
            placeholder="Alphanumeric and underscores only"
        />

        <x-admin-form-element
            type="input.email"
            name="email"
            is_required="true"
            display_name="Email"
            :default="optional($edit)->email ?? ''"
        />

        @if ($edit)
            <x-admin-form-element
                type="checkbox"
                name="change_password"
                display_name="Change Password"
                default="true"
                nolabel="true"
            />
        @endif

        <div class="flex flex-wrap">
            <div class="w-1/2 pr-2">
                <x-admin-form-element
                    type="input.password"
                    name="password"
                    display_name="Password"
                />
            </div>

            <div class="w-1/2 pl-2">
                <x-admin-form-element
                    type="input.password"
                    name="password_confirmation"
                    display_name="Confirm Password"
                />
            </div>
        </div>

        <x-admin-form-element
            type="radio"
            name="role"
            display_name="Role"
            :options="$role_enum"
            :default="optional($edit)->role ?? 'contributor'"
        />

        <div class="form-list-notice-box">
            * Access is restricted to user's permitted channels<br/>
            * Editor: Stories, Media (except superuser), Channels (edit only), Tags (except delete)<br/>
            * Contributor: Stories (modify owned), Media (owned)<br/>
            * Readonly: Stories (read-only)<br/>
            * Disabled retains user account but cannot login
        </div>

        <x-admin-form-element
            type="list_only"
            name="channels"
            form_style="wide"
            display_name="Channel Access"
            :options="$channels_list"
            :default="$channels_default"
        />

        @include('blocks.form-actions', [
            'submit_button_label' => 'Save User',
            'red_button_label' => 'Delete User',
            'red_button_show' => (!is_null($edit) && $edit->username !== config('regencms.root_username')),
            'red_button_route' => $delete ?? ''
        ])

    </form>
@endsection
