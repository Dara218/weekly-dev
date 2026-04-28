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
        ],
        'min' => [
            'password' => 8,
        ],
        'student_file_mimes' => 'pdf,docx,txt',
    ],
    'load_items_limit' => 3,
    'load_items_offset' => 0,
];