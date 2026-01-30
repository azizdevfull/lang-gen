<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Scanned Directories
    |--------------------------------------------------------------------------
    |
    | Here you may define the directories that LangGen should scan for
    | translation keys. By default, we scan the 'app', 'resources/views',
    | and 'routes' directories.
    |
    | You can add custom paths here, for example: base_path('Modules')
    |
    */
    'paths' => [
        base_path('app'),
        base_path('resources/views'),
        base_path('routes'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Conflict Policy
    |--------------------------------------------------------------------------
    |
    | Determines how to handle specific key conflicts.
    |
    | Scenario:
    | You have an existing key as a string: 'messages.home' => 'Home'
    | But your code now uses a nested key:  __('messages.home.title')
    |
    | Options:
    | 'preserve'  - Keeps the existing string value. The new nested key is skipped/ignored.
    | 'overwrite' - Replaces the existing string with an array to allow the new nested key.
    |
    */
    'conflict_policy' => 'overwrite', // 'preserve' or 'overwrite'

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language code to generate translations for.
    | Examples: 'en', 'uz', 'ru'.
    |
    */
    'default_lang' => 'uz',

];