@includeWhen($errors->any() || session('save_failed'), 'blocks.form-notice', [
            'notice_color' => 'red',
            'notice_header' => session('save_failed_title') ?? 'Errors Encountered',
            'notice_message' => session('save_failed_message') ?? ($error_notice_message ?? 'See specific messages below.')
        ])

@includeWhen(session('save_success'), 'blocks.form-notice', [
    'notice_color' => 'green',
    'notice_header' => session('save_success_title') ?? 'Saved Successfully',
    'notice_message' => session('save_success_message') ?? ($success_notice_message ?? '')
])

@includeWhen(session('delete_success_message'), 'blocks.form-notice', [
    'notice_color' => 'green',
    'notice_header' => 'Deleted Successfully',
    'notice_message' => session('delete_success_message') ?? ''
])

@includeWhen(session('delete_error_message'), 'blocks.form-notice', [
    'notice_color' => 'red',
    'notice_header' => 'Errors Encountered',
    'notice_message' => session('delete_error_message') ?? ''
])
