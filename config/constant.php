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
];