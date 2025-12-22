<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'make:platform-request',
    description: 'Generate Abstract + per-platform Request classes and register them in PlatformServiceProvider'
)]
class MakePlatformRequest extends Command
{
    protected $signature = 'make:platform-request
        {name : Request name, e.g. SignIn}
        {--actor=System : Actor folder (e.g., User|Admin|Merchant|...)}
        {--domain=Auth : Domain folder under Actor}
        {--api-version=1 : API version number}
        {--platform=web : Comma-separated: web,mobile (default: web)}
    ';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name       = Str::studly($this->argument('name'));
        $actor      = Str::studly($this->option('actor'));
        $domain     = Str::studly($this->option('domain'));
        $version    = (int) $this->option('api-version');

        $platformOpt = $this->option('platform') ?: 'web';
        $platforms = collect(explode(',', (string) $platformOpt))
            ->map(fn($p) => strtolower(trim($p)))
            ->filter()
            ->unique()
            ->values();

        if ($platforms->isEmpty()) {
            $this->error('Please provide at least one platform (web or mobile).');
            return self::FAILURE;
        }

        // === 1) Abstract request ===
        $abstractRequestNs  = "App\\Http\\Requests\\V{$version}\\Abstracts\\{$actor}\\{$domain}";
        $abstractRequestDir = app_path("Http/Requests/V{$version}/Abstracts/{$actor}/{$domain}");
        $abstractRequestCls = "{$name}AbstractRequest";
        $abstractRequestPath = "{$abstractRequestDir}/{$abstractRequestCls}.php";

        $this->makeDirectory($abstractRequestDir);
        $this->writeFile($abstractRequestPath, $this->renderAbstractRequest($abstractRequestNs, $abstractRequestCls));
        $this->info("Created: {$abstractRequestPath}");

        // === 2) Per-platform requests ===
        $concreteRequests = [];
        foreach ($platforms as $platform) {
            $nsPlatform       = $platform === 'mobile' ? 'Mobile' : 'Web';
            $platformEnumCase = strtoupper($platform) === 'MOBILE' ? 'MOBILE' : 'WEB';

            $requestNs  = "App\\Http\\Requests\\V{$version}\\{$nsPlatform}\\{$actor}\\{$domain}";
            $requestDir = app_path("Http/Requests/V{$version}/{$nsPlatform}/{$actor}/{$domain}");
            $requestCls = "{$name}Request";
            $requestPath = "{$requestDir}/{$requestCls}.php";

            $this->makeDirectory($requestDir);
            $this->writeFile($requestPath, $this->renderPlatformRequest(
                $requestNs,
                $requestCls,
                $abstractRequestNs,
                $abstractRequestCls,
                $platformEnumCase
            ));
            $this->info("Created: {$requestPath}");
            $concreteRequests[] = "\\{$requestNs}\\{$requestCls}::class";
        }

        // === 3) Register in PlatformServiceProvider ===
        $abstractRequestFqn = "\\{$abstractRequestNs}\\{$abstractRequestCls}::class";
        $this->registerRequestInPlatformServiceProvider($abstractRequestFqn, $concreteRequests);

        $this->info('All set ✅');
        return self::SUCCESS;
    }

    // ===== Renderers =====

    private function renderAbstractRequest(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Requests\PlatformRequest;

abstract class {$class} extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}

PHP;
    }

    private function renderPlatformRequest(
        string $namespace,
        string $class,
        string $abstractNamespace,
        string $abstractClass,
        string $platformEnumCase
    ): string {
        return <<<PHP
<?php

namespace {$namespace};

use App\Enums\Platform;
use {$abstractNamespace}\\{$abstractClass};

class {$class} extends {$abstractClass}
{
    public static function platform(): Platform
    {
        return Platform::{$platformEnumCase};
    }

    public function rules(): array
    {
        return parent::rules();
    }
}

PHP;
    }

    // ===== File helpers =====

    private function makeDirectory(string $dir): void
    {
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0777, true);
        }
    }

    private function writeFile(string $path, string $contents): void
    {
        $this->files->put($path, $contents);
    }

    // ===== Provider registration =====

    private function registerRequestInPlatformServiceProvider(string $abstractFqn, array $concretes): void
    {
        $providerPath = app_path('Providers/PlatformServiceProvider.php');
        if (!$this->files->exists($providerPath)) {
            $this->error('PlatformServiceProvider.php not found. Skipping request registration.');
            return;
        }

        $content = $this->files->get($providerPath);

        // Extract version from abstractFqn (e.g., \App\Http\Requests\V1\...)
        preg_match('/\\\\V(\d+)\\\\/', $abstractFqn, $matches);
        $version = $matches[1] ?? '1';

        // Build the implementation array with proper formatting (each on new line)
        $uniqueConcretes = collect($concretes)->unique()->values()->all();
        $implLines = array_map(fn($impl) => "                    {$impl},", $uniqueConcretes);
        $implArray = "[\n" . implode("\n", $implLines) . "\n                ]";

        $newEntry = "{$abstractFqn} => {$implArray},";

        // Pattern to find the version block in getRequestImplementations
        $versionPattern = '/(private function getRequestImplementations\(int \$version\): array\s*\{\s*return match \(\$version\) \{.*?' . $version . ' => \[)(.*?)(            \],)/s';

        if (preg_match($versionPattern, $content, $matches)) {
            // Version block exists
            $existingBindings = $matches[2];

            // Check if abstract already exists
            if (preg_match('/'.preg_quote($abstractFqn, '/').' => \[.*?\],/s', $existingBindings)) {
                // Update existing entry
                $content = preg_replace(
                    '/(private function getRequestImplementations.*?' . $version . ' => \[.*?)'.preg_quote($abstractFqn, '/').' => \[.*?\],/s',
                    "$1{$newEntry}",
                    $content,
                    1
                );
            } else {
                // Add new entry before the closing bracket
                $replacement = "$1$2                {$newEntry}\n$3";
                $content = preg_replace($versionPattern, $replacement, $content, 1);
            }
        } else {
            // Version block doesn't exist - create it
            $newVersionBlock = "            {$version} => [\n                {$newEntry}\n            ],\n";

            // Insert before the default block (most reliable anchor point)
            $insertPattern = '/(private function getRequestImplementations\(int \$version\): array\s*\{\s*return match \(\$version\) \{.*?)(            default => \[\],)/s';

            if (preg_match($insertPattern, $content)) {
                $content = preg_replace(
                    $insertPattern,
                    "$1{$newVersionBlock}$2",
                    $content,
                    1
                );
                $this->info("Created new version {$version} block in getRequestImplementations.");
            } else {
                $this->warn("Could not automatically add version {$version} block. Please add it manually to getRequestImplementations().");
            }
        }

        $this->files->put($providerPath, $content);
        $this->info('PlatformServiceProvider updated (requests).');
    }
}
