<?php

return [

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