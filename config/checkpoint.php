<?php

return [

    'default_storage' => env('CHECKPOINT_STORAGE', 'database'),

    'ignored_fields' => [
        '*' => ['created_at', 'updated_at'],
        \App\Models\User::class => ['password'],
    ],

];
