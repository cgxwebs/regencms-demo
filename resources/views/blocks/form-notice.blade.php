<div class="block -mt-10 mb-12 text-center" role="alert">
    <p class="rounded-t-lg py-3 px-4 font-bold text-lg block bg-{{ $notice_color }}-600 text-white">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" height="20" width="20" class="inline-block mr-2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ $notice_header }}
    </p>
    @if(!empty($notice_message))
        <p class="block py-8 px-4 bg-{{ $notice_color }}-100 text-{{ $notice_color }}-700">{!! $notice_message !!}</p>
    @endif
</div>
