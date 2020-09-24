@unless($nowrapper)<div class="mb-6" v-pre>@endunless

@unless($nolabel)
    <label class="block text-gray-600 text-sm mb-2" for="{{ $name }}">
        <span class="uppercase">{{ $displayName }}</span>@if($isRequired)<span class="text-gray-500" title="Required field">*</span>@endif
        @error($name)
            <span class="inline pl-2 text-red-500 leading-normal">{{ $message }}</span>
        @enderror
    </label>
@endunless

@if($nolabel && $errors->get($name))
    <label class="block text-gray-600 text-sm mb-2" for="{{ $name }}">
        @error($name)
        <span class="inline pl-2 text-red-500 leading-normal">{{ $message }}</span>
        @enderror
    </label>
@endif

@if ($type === 'input')
    <input name="{{ $name }}"
           class="appearance-none border-0 rounded w-full bg-gray-200 text-gray-700 leading-tight
                @if ($formStyle !== 'small') text-xl p-4
                @else p-3 @endif
            "
           type="{{ $sub_type }}"
           placeholder="{{ $placeholder }}"
           value="{{ $value() }}"
           />
@endif

@if ($type === 'filemulti')
    <input name="{{ $name }}[]"
           class=""
           type="file"
           multiple
           />
@endif

@if ($type === 'textarea')
    <textarea name="{{ $name }}"
              class="appearance-none border rounded w-full p-4 bg-gray-200 text-gray-700 leading-relaxed"
              placeholder="{{ $placeholder }}"
              rows="{{ $rows }}">{{ $value() }}</textarea>
@endif

@if ($type === 'radio')
    <div class="block">
        @foreach($options as $opt)
        <label class="inline-flex items-center mr-1 cursor-pointer bg-gray-200 px-3 py-2 rounded-full">
            <input type="radio" name="{{ $name }}" value="{{ $opt->value }}" class="form-radio" {{ $checked($opt->value) }}>
            <span class="ml-2 text-gray-600 ">{{ $opt->description }}</span>
        </label>
        @endforeach
    </div>
@endif

@if ($type === 'select')
    <div class="block">
        <select name="{{ $name }}" class="w-full form-select bg-gray-200 text-gray-700 my-1 appearance-none border-0">
            @foreach($options as $opt)
                <option value="{{ $opt->value }}" {{ $checked($opt->value) }}>{{ $opt->description }}</option>
            @endforeach
        </select>
    </div>
@endif

@if ($type === 'checkbox')
    <label class="block py-1 uppercase text-gray-600 text-sm mb-2">
        <input type="checkbox" name="{{ $name }}" value="{{ $value() }}" class="form-checkbox">
        <span class="pl-2">{{ $displayName }}</span>
    </label>
@endif

@if ($type === 'list')
    <div>
        @error($name . '.*')
            <span class="block py-2 text-sm text-red-500">{{ $message }}</span>
        @enderror

        @foreach($options as $opt)
            <label class="flex items-center py-1 text-gray-700">
                <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $opt['id'] }}"
                    class="form-checkbox" {{ $checked_list($opt['id']) }}
                />
                <div class="ml-2 leading-normal text-sm cursor-pointer">
                    {{ $opt['name'] }}
                    @if($opt['title'])
                        <span class="text-gray-600 block text-xs">
                            {{ $opt['title'] }}
                        </span>
                    @endif
                </div>
            </label>
        @endforeach
    </div>

    <div class="mt-4">
        @error($name . '_create_input')
        <span class="block py-2 text-sm text-red-500">{{ $message }}</span>
        @enderror

        @error($name . '_create_input.*')
            <span class="block py-2 text-sm text-red-500">{{ $message }}</span>
        @enderror

        <input name="{{ $name }}_create"
               class="appearance-none border rounded w-full p-2 text-gray-600 leading-tight"
               type="{{ $sub_type }}"
               placeholder="{{ $placeholder }}"
               value="{{ $value($name . '_create', '') }}"
        />
    </div>
@endif

@if ($type === 'list_only')
        <div class="@if($formStyle === 'wide') flex flex-wrap @endif w-full">
            @error($name . '.*')
                <span class="py-2 block text-sm text-red-500">{{ $message }}</span>
            @enderror

            @foreach($options as $opt)
                <label class="flex items-center text-gray-700 cursor-pointer @if($formStyle === 'wide') py-2 w-1/2 @else py-1 @endif">
                    <input
                        type="checkbox"
                        name="{{ $name }}[]"
                        value="{{ $opt['id'] }}"
                        class="form-checkbox" {{ $checked_list($opt['id']) }}
                    />
                    <div class="@if($formStyle === 'wide') ml-4 @else ml-2 text-sm @endif leading-normal">
                        {{ $opt['name'] }}
                        @if($opt['title'])
                            <span class="text-gray-600 block text-xs">
                            {{ $opt['title'] }}
                        </span>
                        @endif
                    </div>
                </label>
            @endforeach
        </div>
@endif

@if ($type === 'submit')
    @if ($formStyle === 'small')
        <button type="submit" name="{{ $name }}" value="1"
                class="inline-block bg-teal-500 hover:bg-teal-700 text-white rounded py-3 px-6 shadow-lg" type="button">
            {{ $displayName }}
        </button>
    @else
        <button type="submit" name="{{ $name }}" value="1"
                class="inline-block bg-teal-500 hover:bg-teal-700 text-xl text-white rounded py-3 px-6 shadow-lg" type="button">
                {{ $displayName }}
        </button>
    @endif
@endif

@if ($type === 'red_button')
    <a href="{{ $name }}" class="inline-block bg-red-400 hover:bg-red-500 text-xl text-gray-200 rounded py-3 px-6 mx-4 shadow-md">
        {{ $displayName }}
    </a>
@endif

@unless($nowrapper)</div>@endunless
