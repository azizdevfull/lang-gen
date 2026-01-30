<?php

namespace AzizDevFull\LangGen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LangGenCommand extends Command
{
    protected $signature = 'lang:gen {lang? : The target language code (e.g., uz, en, ru)}';

    protected $description = 'Scan application code and generate missing translation keys in PHP language files.';

    public function handle()
    {
        $langCode = $this->argument('lang') ?: config('lang-gen.default_lang', 'en');
        $conflictPolicy = config('lang-gen.conflict_policy', 'preserve'); // 'preserve' or 'overwrite'

        $langPath = lang_path($langCode);

        $this->info("Target Language: [$langCode]");
        $this->info("Conflict Policy: [$conflictPolicy]");

        if (!File::exists($langPath)) {
            File::makeDirectory($langPath, 0755, true);
            $this->comment("Directory created: $langPath");
        }

        $this->info("Scanning files...");

        $foundKeysByFile = $this->scanFiles();

        if (empty($foundKeysByFile)) {
            $this->warn("No translation keys found.");
            return;
        }

        foreach ($foundKeysByFile as $fileName => $keys) {
            $this->processFile($fileName, $keys, $langPath, $langCode, $conflictPolicy);
        }

        $this->info("------------------------------------------------");
        $this->info("✔  Synchronization completed successfully!");
    }

    protected function scanFiles(): array
    {
        $paths = [
            base_path('app'),
            base_path('resources/views'),
        ];

        $pattern = '/(?:__|@lang|trans)\([\'"]([^\'"]+)[\'"]\)/';
        $keysByFile = [];

        foreach ($paths as $path) {
            if (!File::exists($path))
                continue;

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if (preg_match_all($pattern, $file->getContents(), $matches)) {
                    foreach ($matches[1] as $key) {
                        if (!Str::contains($key, '.'))
                            continue;

                        [$fileName, $nestedKey] = explode('.', $key, 2);
                        $keysByFile[$fileName][] = $nestedKey;
                    }
                }
            }
        }

        return $keysByFile;
    }

    protected function processFile($fileName, $keys, $langPath, $langCode, $conflictPolicy)
    {
        $filePath = "$langPath/$fileName.php";
        $keys = array_unique($keys);

        $currentData = File::exists($filePath) ? include($filePath) : [];

        if (!is_array($currentData)) {
            $currentData = [];
        }

        $modified = false;

        foreach ($keys as $nestedKey) {
            if (Arr::has($currentData, $nestedKey)) {
                continue;
            }

            if ($this->hasConflict($currentData, $nestedKey)) {
                if ($conflictPolicy === 'overwrite') {
                    $this->warn("Overwriting conflict in [$fileName.php]: $nestedKey");
                } else {
                    $this->error("Skipped conflict in [$fileName.php]: $nestedKey (Existing value is a string)");
                    continue;
                }
            }

            $defaultValue = Str::headline(str_replace('.', ' ', $nestedKey));
            Arr::set($currentData, $nestedKey, $defaultValue);
            $modified = true;
        }

        if ($modified) {
            $this->saveFile($filePath, $currentData);
            $this->line("<info>Updated:</info> lang/$langCode/$fileName.php");
        }
    }

    protected function hasConflict(array $data, string $key): bool
    {
        $parts = explode('.', $key);
        $temp = $data;

        foreach ($parts as $part) {
            if (isset($temp[$part])) {
                if (!is_array($temp[$part])) {
                    return true;
                }
                $temp = $temp[$part];
            } else {
                return false;
            }
        }

        return false;
    }
    protected function saveFile(string $filePath, array $data)
    {
        ksort($data);

        $export = var_export($data, true);

        $export = str_replace(['array (', ')', '=> ' . "\n"], ['[', ']', '=> '], $export);

        $export = preg_replace("/^([ ]*)  /m", '$1    ', $export);

        $content = "<?php\n\nreturn " . $export . ";\n";

        File::put($filePath, $content);
    }
}