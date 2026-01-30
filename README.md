# Laravel LangGen 🌍

[![Latest Version on Packagist](https://img.shields.io/packagist/v/azizdevfull/lang-gen.svg?style=flat-square)](https://packagist.org/packages/azizdevfull/lang-gen)
[![Total Downloads](https://img.shields.io/packagist/dt/azizdevfull/lang-gen.svg?style=flat-square)](https://packagist.org/packages/azizdevfull/lang-gen)
[![License](https://img.shields.io/packagist/l/azizdevfull/lang-gen.svg?style=flat-square)](https://packagist.org/packages/azizdevfull/lang-gen)

**Automatically generate missing translation keys in your Laravel application.**

LangGen scans your application code (`app` and `resources/views`) for translation keys like `__('messages.welcome')` or `@lang('auth.failed')` and automatically creates the corresponding PHP language files.

Unlike other tools, LangGen supports **nested array keys** (dot-notation) and creates clean, native PHP arrays without needing a database.

## 🚀 Features

-   **Auto-Discovery:** Scans PHP and Blade files for `__(), @lang(), trans()`.
-   **JSON Support:** Handles keys with spaces or single strings (e.g., `__('Log in')`) by generating `lang/{code}.json`.
-   **Nested Arrays:** Converts `auth.password.min` into `['password' => ['min' => '...']]`.
-   **Smart Conflict Handling:** Configurable policy to `preserve` or `overwrite` existing keys.
-   **Custom Paths:** Configurable directories to scan (e.g., `Modules`, `routes`).
-   **Native PHP Files:** Works with standard Laravel `lang/*.php` files.
-   **No Database Required:** Lightweight and zero-setup.

## 📦 Installation

You can install the package via composer:

```bash
composer require azizdevfull/lang-gen --dev
```

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --tag="lang-gen-config"
```

## 🛠 Usage

### Basic Usage

Run the command to scan your codebase and generate missing keys for the default language (default: `en`):

```bash
php artisan lang:gen
```

### Specify Language

To generate translations for a specific language (e.g., Uzbek):

```bash
php artisan lang:gen uz
```

This will:
1.  Scan your configured directories (default: `app`, `resources/views`, `routes`).
2.  Find all translation keys (nested arrays and JSON strings).
3.  Create or update `lang/uz/messages.php` and `lang/uz.json`.
4.  Populate missing keys with a readable default value (e.g., "Messages Welcome").

## ⚙️ Configuration

The configuration file `config/lang-gen.php` allows you to customize the behavior:

```php
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
```

## 💡 Example

**In your code (`resources/views/welcome.blade.php`):**

```blade
<h1>{{ __('home.hero.title') }}</h1>
<p>@lang('home.hero.subtitle')</p>
```

**Run command:**

```bash
php artisan lang:gen en
```

**Result (`lang/en/home.php`):**

```php
<?php

return [
    'hero' => [
        'title' => 'Home Hero Title',
        'subtitle' => 'Home Hero Subtitle',
    ],
];
```

### JSON Key Example

**In your code:**

```blade
<button>{{ __('Create Account') }}</button>
```

**Result (`lang/en.json`):**

```json
{
    "Create Account": "Create Account"
}
```

## 🧪 Testing

```bash
composer test
```

## 🔒 Security

If you discover any security related issues, please email aziz16110904@gmail.com instead of using the issue tracker.

## 👥 Credits

-   [Azizbek](https://github.com/azizdevfull)

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.