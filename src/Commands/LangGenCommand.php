<?php

namespace AzizDevFull\LangGen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LangGenCommand extends Command
{
    protected $signature = 'lang:gen {lang? : The target language code (e.g., uz, en, ru)}';

    protected $description = 'Scan application code and generate missing translation keys (PHP arrays & JSON).';

    public function handle()
    {
        $langCode = $this->argument('lang') ?: config('lang-gen.default_lang', 'en');
        $conflictPolicy = config('lang-gen.conflict_policy', 'preserve');

        $langPath = lang_path();
        $targetDir = "$langPath/$langCode";

        $this->info("Target Language: [$langCode]");

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $this->info("Scanning files...");

        [$phpKeys, $jsonKeys] = $this->scanFiles();

        $jsonCount = count($jsonKeys);
        $phpCount = 0;
        foreach ($phpKeys as $k)
            $phpCount += count($k);

        $this->comment("Found: $phpCount PHP keys (array) and $jsonCount JSON keys (strings).");

        if (empty($phpKeys) && empty($jsonKeys)) {
            $this->warn("No translation keys found.");
            return;
        }

        foreach ($phpKeys as $fileName => $keys) {
            $this->processPhpFile($fileName, $keys, $targetDir, $langCode, $conflictPolicy);
        }

        if (!empty($jsonKeys)) {
            $this->processJsonFile($jsonKeys, $langPath, $langCode);
        }

        $this->info("------------------------------------------------");
        $this->info("✔  Synchronization completed successfully!");
    }

    protected function scanFiles(): array
    {
        $paths = config('lang-gen.paths', [
            base_path('app'),
            base_path('resources/views'),
            base_path('routes'),
        ]);

        $pattern = '/(?:__|@lang|trans)\s*\(\s*[\'"]([^\'"]+)[\'"]/';

        $phpKeysByFile = [];
        $jsonKeys = [];

        foreach ($paths as $path) {
            if (!File::exists($path))
                continue;

            $files = File::allFiles($path);

            foreach ($files as $file) {
                if (preg_match_all($pattern, $file->getContents(), $matches)) {
                    foreach ($matches[1] as $key) {
                        if (trim($key) === '')
                            continue;

                        if (Str::contains($key, ' ') || !Str::contains($key, '.')) {
                            $jsonKeys[] = $key;
                        } else {
                            [$fileName, $nestedKey] = explode('.', $key, 2);
                            $phpKeysByFile[$fileName][] = $nestedKey;
                        }
                    }
                }
            }
        }

        return [$phpKeysByFile, array_unique($jsonKeys)];
    }

    protected function processPhpFile($fileName, $keys, $langPath, $langCode, $conflictPolicy)
    {
        $filePath = "$langPath/$fileName.php";
        $keys = array_unique($keys);

        $currentData = File::exists($filePath) ? include($filePath) : [];
        if (!is_array($currentData))
            $currentData = [];

        $modified = false;

        foreach ($keys as $nestedKey) {
            if (Arr::has($currentData, $nestedKey))
                continue;

            if ($this->hasConflict($currentData, $nestedKey)) {
                if ($conflictPolicy === 'overwrite') {
                } else {
                    $this->error("Conflict skipped: $fileName.php -> $nestedKey");
                    continue;
                }
            }

            $defaultValue = Str::headline(str_replace('.', ' ', $nestedKey));
            Arr::set($currentData, $nestedKey, $defaultValue);
            $modified = true;
        }

        if ($modified) {
            $this->savePhpFile($filePath, $currentData);
            $this->line("<info>Updated PHP:</info> lang/$langCode/$fileName.php");
        }
    }

    protected function processJsonFile($keys, $langPath, $langCode)
    {
        $filePath = "$langPath/$langCode.json";

        $currentData = [];
        if (File::exists($filePath)) {
            $currentData = json_decode(File::get($filePath), true) ?? [];
        }

        $modified = false;

        foreach ($keys as $key) {
            if (isset($currentData[$key]))
                continue;

            $currentData[$key] = $key;
            $modified = true;
        }

        if ($modified) {
            ksort($currentData);
            File::put($filePath, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->line("<info>Updated JSON:</info> lang/$langCode.json");
        }
    }

    protected function hasConflict(array $data, string $key): bool
    {
        $parts = explode('.', $key);
        $temp = $data;
        foreach ($parts as $part) {
            if (isset($temp[$part])) {
                if (!is_array($temp[$part]))
                    return true;
                $temp = $temp[$part];
            } else {
                return false;
            }
        }
        return false;
    }

    protected function savePhpFile(string $filePath, array $data)
    {
        ksort($data);
        $export = var_export($data, true);
        $export = str_replace(['array (', ')', '=> ' . "\n"], ['[', ']', '=> '], $export);
        $export = preg_replace("/^([ ]*)  /m", '$1    ', $export);
        $content = "<?php\n\nreturn " . $export . ";\n";
        File::put($filePath, $content);
    }
}