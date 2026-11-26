<?php

return [
    'validation' => [
        'max' => [
            'first_name' => 50,
            'middle_name' => 50,
            'last_name' => 50,
            'email' => 50,
            'address' => 100,
            'rows' => 500,
            'file_size' => 10240,
            'ai_message' => 2000,
            'conversation_id' => 36,
            'message_body' => 5000,
            'message_attachment' => 255,
        ],
        'min' => [
            'password' => 8,
        ],
        'student_file_mimes' => 'pdf,docx,txt',
    ],
    'load_items_limit' => 3,
    'load_items_offset' => 0,
    'ai' => [
        'announcements' => [
            'default_limit' => 5,
            'min_limit' => 1,
            'max_limit' => 10,
        ],
        'conversations' => [
            'default_per_page' => 20,
            'min_per_page' => 1,
            'max_per_page' => 50,
            'default_page' => 1,
        ],
        'rate_limit_per_minute' => 10,
    ],
    'stripe' => [
        'amount_decimal' => '0,2',
        'zero_decimal' => '0',
        'money_scale' => 2,
        'minor_unit_multiplier' => '100',
        'checkout_lock_seconds' => 120,
        'billing_timezone' => env('BILLING_TIMEZONE', 'Asia/Manila'),
        'overdue_grace_days' => (int) env('BILLING_OVERDUE_GRACE_DAYS', 7),
        'reminder_days_before_due' => (int) env('BILLING_REMINDER_DAYS_BEFORE_DUE', 3),
        'command_chunk_size' => (int) env('BILLING_COMMAND_CHUNK_SIZE', 100),
        'schedule' => [
            'generate_at' => env('BILLING_GENERATE_AT', '00:05'),
            'remind_at' => env('BILLING_REMIND_AT', '08:00'),
        ],
        'due_end' => [
            'hour' => 23,
            'minute' => 59,
            'second' => 59,
        ],
        'invoices' => [
            'default_per_page' => 20,
            'min_per_page' => 1,
            'max_per_page' => 50,
        ],
    ],
];
