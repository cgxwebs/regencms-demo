<div class="mt-20 py-10 bg-gray-100 text-center">
    <x-admin-form-element
        type="submit"
        name="submit"
        nolabel="true"
        nowrapper="true"
        display_name="{{ $submit_button_label ?? 'Submit' }}"
    />

    @if($red_button_show)
        <x-admin-form-element
            type="red_button"
            :name="$red_button_route"
            nolabel="true"
            nowrapper="true"
            display_name="{{ $red_button_label ?? 'Cancel' }}"
        />
    @endif
</div>
