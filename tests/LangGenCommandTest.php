<?php

namespace AzizDevFull\LangGen\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Config;

class LangGenCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    protected function cleanUp()
    {
        if (File::exists(lang_path('en'))) {
            File::deleteDirectory(lang_path('en'));
        }
        if (File::exists(lang_path('en.json'))) {
            File::delete(lang_path('en.json'));
        }
        if (File::exists(resource_path('views/test.blade.php'))) {
            File::delete(resource_path('views/test.blade.php'));
        }
    }

    public function test_it_generates_nested_php_translation_files()
    {
        $content = "<h1>{{ __('messages.home.welcome_title') }}</h1>";
        File::ensureDirectoryExists(resource_path('views'));
        File::put(resource_path('views/test.blade.php'), $content);

        $this->artisan('lang:gen en')
            ->assertExitCode(0);

        $filePath = lang_path('en/messages.php');
        $this->assertFileExists($filePath);

        $translations = include $filePath;

        $this->assertIsArray($translations);
        $this->assertArrayHasKey('home', $translations);
        $this->assertArrayHasKey('welcome_title', $translations['home']);
        $this->assertEquals('Home Welcome Title', $translations['home']['welcome_title']);
    }

    public function test_it_generates_json_translation_files()
    {
        $content = "
            {{ __('Hello World') }}
            {{ __('My name is :name') }}
        ";
        File::ensureDirectoryExists(resource_path('views'));
        File::put(resource_path('views/test.blade.php'), $content);

        $this->artisan('lang:gen en')
            ->assertExitCode(0);

        $jsonPath = lang_path('en.json');
        $this->assertFileExists($jsonPath);

        $jsonContent = json_decode(File::get($jsonPath), true);

        $this->assertArrayHasKey('Hello World', $jsonContent);
        $this->assertArrayHasKey('My name is :name', $jsonContent);
        $this->assertEquals('Hello World', $jsonContent['Hello World']);
    }

    public function test_it_respects_preserve_policy_on_conflict()
    {
        File::ensureDirectoryExists(lang_path('en'));
        $existingContent = "<?php return ['error' => 'Simple Error String'];";
        File::put(lang_path('en/auth.php'), $existingContent);

        $content = "{{ __('auth.error.code') }}";
        File::put(resource_path('views/test.blade.php'), $content);

        Config::set('lang-gen.conflict_policy', 'preserve');

        $this->artisan('lang:gen en')
            ->assertExitCode(0); // Xato bermasligi kerak, lekin o'tkazib yuborishi kerak

        $translations = include lang_path('en/auth.php');
        $this->assertIsString($translations['error']); // Hali ham string bo'lib turishi kerak
        $this->assertEquals('Simple Error String', $translations['error']);
    }

    public function test_it_overwrites_conflict_when_policy_is_overwrite()
    {
        File::ensureDirectoryExists(lang_path('en'));
        $existingContent = "<?php return ['error' => 'Simple Error String'];";
        File::put(lang_path('en/auth.php'), $existingContent);

        $content = "{{ __('auth.error.code') }}";
        File::put(resource_path('views/test.blade.php'), $content);

        Config::set('lang-gen.conflict_policy', 'overwrite');

        $this->artisan('lang:gen en')
            ->assertExitCode(0);

        $translations = include lang_path('en/auth.php');

        $this->assertIsArray($translations['error']);
        $this->assertArrayHasKey('code', $translations['error']);
    }
}