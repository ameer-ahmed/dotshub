<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'make:platform-service',
    description: 'Generate Model, Repository (interface + eloquent), Abstract + per-platform Services, Controllers, Request, Resource, and register providers'
)]
class MakePlatformService extends Command
{
    protected $signature = 'make:platform-service
        {name : Base name, e.g. Role}
        {--actor=System : Actor folder (e.g., User|Admin|Merchant|...)}
        {--domain=Role : Domain folder under Actor}
        {--api-version=1 : API version number}
        {--platform=web : Comma-separated: web,mobile (default: web)}
        {--tenant : Generate model and repository for tenant (Models/Tenant, Repository/Contracts/Tenant, Repository/Eloquent/Tenant)}
        {--no-controller : Skip generating controllers}
        {--no-request : Skip generating request}
        {--no-resource : Skip generating resource}
        {--no-model : Skip generating model}
        {--no-repo : Skip generating repository (interface + eloquent + provider bind)}
    ';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name       = Str::studly($this->argument('name')); // e.g. Role
        $actor      = Str::studly($this->option('actor'));  // e.g. User
        $domain     = Str::studly($this->option('domain')); // e.g. Role
        $version    = (int) $this->option('api-version');
        $isTenant   = (bool) $this->option('tenant');
        $makeCtrl   = !$this->option('no-controller');
        $makeReq    = !$this->option('no-request');
        $makeRes    = !$this->option('no-resource');
        $makeModel  = !$this->option('no-model');
        $makeRepo   = !$this->option('no-repo');

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

        // === 0) Model ===
        if ($makeModel) {
            $modelNs  = $isTenant ? 'App\\Models\\Tenant' : 'App\\Models';
            $modelDir = $isTenant ? app_path('Models/Tenant') : app_path('Models');
            $modelCls = $name;
            $modelPath = "{$modelDir}/{$modelCls}.php";

            $this->makeDirectory($modelDir);
            if (!$this->files->exists($modelPath)) {
                $this->writeFile($modelPath, $this->renderModel($modelNs, $modelCls));
                $this->info("Created: {$modelPath}");
            } else {
                $this->line("Model exists: {$modelPath}");
            }
        }

        // === 1) Abstract service ===
        $abstractNs   = "App\\Http\\Services\\V{$version}\\Abstracts\\{$actor}\\{$domain}";
        $abstractDir  = app_path("Http/Services/V{$version}/Abstracts/{$actor}/{$domain}");
        $abstractCls  = "{$name}AbstractService";
        $abstractPath = "{$abstractDir}/{$abstractCls}.php";

        $this->makeDirectory($abstractDir);
        $this->writeFile($abstractPath, $this->renderAbstract($abstractNs, $abstractCls));
        $this->info("Created: {$abstractPath}");

        // === 2) Abstract request ===
        if ($makeReq) {
            $abstractRequestNs  = "App\\Http\\Requests\\V{$version}\\Abstracts\\{$actor}\\{$domain}";
            $abstractRequestDir = app_path("Http/Requests/V{$version}/Abstracts/{$actor}/{$domain}");
            $abstractRequestCls = "{$name}AbstractRequest";
            $abstractRequestPath = "{$abstractRequestDir}/{$abstractRequestCls}.php";

            $this->makeDirectory($abstractRequestDir);
            $this->writeFile($abstractRequestPath, $this->renderAbstractRequest($abstractRequestNs, $abstractRequestCls));
            $this->info("Created: {$abstractRequestPath}");
        }

        // === 3) Single Controller (not platform-specific) ===
        if ($makeCtrl) {
            $controllerNs   = "App\\Http\\Controllers\\V{$version}\\{$actor}\\{$domain}";
            $controllerDir  = app_path("Http/Controllers/V{$version}/{$actor}/{$domain}");
            $controllerCls  = "{$name}Controller";
            $controllerPath = "{$controllerDir}/{$controllerCls}.php";

            $abstractServiceVar = lcfirst($abstractCls);

            $this->makeDirectory($controllerDir);
            $this->writeFile($controllerPath, $this->renderController(
                $controllerNs,
                $controllerCls,
                $abstractNs,
                $abstractCls,
                $abstractServiceVar
            ));
            $this->info("Created: {$controllerPath}");
        }

        // === 4) Per-platform services (+ request / resource) ===
        $concretes = [];
        $concreteRequests = [];
        foreach ($platforms as $platform) {
            $nsPlatform       = $platform === 'mobile' ? 'Mobile' : 'Web';
            $platformEnumCase = strtoupper($platform) === 'MOBILE' ? 'MOBILE' : 'WEB';

            // Service
            $serviceNs   = "App\\Http\\Services\\V{$version}\\{$nsPlatform}\\{$actor}\\{$domain}";
            $serviceDir  = app_path("Http/Services/V{$version}/{$nsPlatform}/{$actor}/{$domain}");
            $serviceCls  = "{$name}Service";
            $servicePath = "{$serviceDir}/{$serviceCls}.php";

            $this->makeDirectory($serviceDir);
            $this->writeFile($servicePath, $this->renderConcrete(
                $serviceNs,
                $serviceCls,
                $abstractNs,
                $abstractCls,
                $platformEnumCase
            ));
            $this->info("Created: {$servicePath}");
            $concretes[] = "\\{$serviceNs}\\{$serviceCls}::class";

            // Request (platform-specific)
            if ($makeReq) {
                $requestNs  = "App\\Http\\Requests\\V{$version}\\{$nsPlatform}\\{$actor}\\{$domain}";
                $requestDir = app_path("Http/Requests/V{$version}/{$nsPlatform}/{$actor}/{$domain}");
                $requestCls = "{$name}Request";
                $requestPath = "{$requestDir}/{$requestCls}.php";

                $abstractRequestNs = "App\\Http\\Requests\\V{$version}\\Abstracts\\{$actor}\\{$domain}";
                $abstractRequestCls = "{$name}AbstractRequest";

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

            // Resource
            if ($makeRes) {
                $resourceNs  = "App\\Http\\Resources\\V{$version}\\{$nsPlatform}\\{$actor}\\{$domain}";
                $resourceDir = app_path("Http/Resources/V{$version}/{$nsPlatform}/{$actor}/{$domain}");
                $resourceCls = "{$name}Resource";
                $resourcePath = "{$resourceDir}/{$resourceCls}.php";

                $this->makeDirectory($resourceDir);
                $this->writeFile($resourcePath, $this->renderResource($resourceNs, $resourceCls));
                $this->info("Created: {$resourcePath}");
            }
        }

        // === 3) Repository ===
        if ($makeRepo) {
            // Interface
            $repoInterfaceNs  = $isTenant ? 'App\\Repository\\Contracts\\Tenant' : 'App\\Repository\\Contracts';
            $repoInterfaceDir = $isTenant ? app_path('Repository/Contracts/Tenant') : app_path('Repository/Contracts');
            $repoInterfaceCls = "{$name}RepositoryInterface";
            $repoInterfacePath = "{$repoInterfaceDir}/{$repoInterfaceCls}.php";

            $this->makeDirectory($repoInterfaceDir);
            if (!$this->files->exists($repoInterfacePath)) {
                $this->writeFile($repoInterfacePath, $this->renderRepoInterface($repoInterfaceNs, $repoInterfaceCls));
                $this->info("Created: {$repoInterfacePath}");
            }

            // Eloquent implementation
            $repoEloquentNs   = $isTenant ? 'App\\Repository\\Eloquent\\Tenant' : 'App\\Repository\\Eloquent';
            $repoEloquentDir  = $isTenant ? app_path('Repository/Eloquent/Tenant') : app_path('Repository/Eloquent');
            $repoEloquentCls  = "{$name}Repository";
            $repoEloquentPath = "{$repoEloquentDir}/{$repoEloquentCls}.php";

            // Determine model FQN based on tenant flag
            $modelFqn = $isTenant ? "App\\Models\\Tenant\\{$name}" : "App\\Models\\{$name}";

            $this->makeDirectory($repoEloquentDir);
            $this->writeFile($repoEloquentPath, $this->renderRepoConcrete(
                $repoEloquentNs,
                $repoEloquentCls,
                $modelFqn,
                $repoInterfaceNs,
                $repoInterfaceCls
            ));
            $this->info("Created: {$repoEloquentPath}");

            // Bind in RepositoryServiceProvider (with clean imports)
            $this->registerInRepositoryServiceProvider(
                "{$repoInterfaceNs}\\{$repoInterfaceCls}",
                "{$repoEloquentNs}\\{$repoEloquentCls}"
            );
        }

        // === 4) Register in PlatformServiceProvider ===
        $abstractFqn = "\\{$abstractNs}\\{$abstractCls}::class";
        $this->registerInPlatformServiceProvider($abstractFqn, $concretes);

        if ($makeReq && !empty($concreteRequests)) {
            $abstractRequestFqn = "\\{$abstractRequestNs}\\{$abstractRequestCls}::class";
            $this->registerRequestInPlatformServiceProvider($abstractRequestFqn, $concreteRequests);
        }

        $this->info('All set ✅');
        return self::SUCCESS;
    }

    // ===== Renderers =====

    private function renderModel(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$class} extends Model
{
    use HasFactory;

    protected \$guarded = [];
}

PHP;
    }

    private function renderAbstract(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Services\PlatformService;

abstract class {$class} extends PlatformService
{
    // Empty abstract service
}

PHP;
    }

    private function renderConcrete(
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
}

PHP;
    }

    private function renderController(
        string $namespace,
        string $class,
        string $abstractServiceNs,
        string $abstractServiceClass,
        string $serviceVar
    ): string {
        return <<<PHP
<?php

namespace {$namespace};

use App\Http\Controllers\Controller;
use {$abstractServiceNs}\\{$abstractServiceClass};

class {$class} extends Controller
{
    public function __construct(
        private readonly {$abstractServiceClass} \${$serviceVar},
    ) {}
}

PHP;
    }

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

    private function renderResource(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Http\Resources\Json\JsonResource;

class {$class} extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id' => \$this->id,
            // TODO: map fields
        ];
    }
}

PHP;
    }

    private function renderRepoInterface(string $namespace, string $class): string
    {
        return <<<PHP
<?php

namespace {$namespace};

interface {$class} extends \App\Repository\Contracts\RepositoryInterface
{
}

PHP;
    }

    private function renderRepoConcrete(
        string $namespace,
        string $class,
        string $modelFqn,        // e.g. App\Models\Role  (NO leading backslash)
        string $interfaceNs,     // e.g. App\Repository
        string $interfaceCls     // e.g. RoleRepositoryInterface
    ): string {
        $modelShort = class_basename($modelFqn);

        return <<<PHP
<?php

namespace {$namespace};

use {$modelFqn};
use Illuminate\Database\Eloquent\Model;
use App\Repository\Eloquent\Repository;
use {$interfaceNs}\\{$interfaceCls};

class {$class} extends Repository implements {$interfaceCls}
{
    protected Model \$model;

    public function __construct({$modelShort} \$model)
    {
        parent::__construct(\$model);
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

    // ===== Providers registration =====

    private function registerInPlatformServiceProvider(string $abstractFqn, array $concretes): void
    {
        $providerPath = app_path('Providers/PlatformServiceProvider.php');
        if (!$this->files->exists($providerPath)) {
            $this->error('PlatformServiceProvider.php not found. Skipping platform registration.');
            return;
        }

        $content = $this->files->get($providerPath);

        // Extract version from abstractFqn (e.g., \App\Http\Services\V1\...)
        preg_match('/\\\\V(\d+)\\\\/', $abstractFqn, $matches);
        $version = $matches[1] ?? '1';

        // Build the implementation array with proper formatting (each on new line)
        $uniqueConcretes = collect($concretes)->unique()->values()->all();
        $implLines = array_map(fn($impl) => "                    {$impl},", $uniqueConcretes);
        $implArray = "[\n" . implode("\n", $implLines) . "\n                ]";

        $newEntry = "{$abstractFqn} => {$implArray},";

        // Pattern to find the version block in getServiceImplementations
        // Use a more precise pattern that captures until the next version block or default case
        $versionPattern = '/(private function getServiceImplementations\(int \$version\): array\s*\{\s*return match \(\$version\) \{.*?' . $version . ' => \[)(.*?)(\n            \],\n)/s';

        if (preg_match($versionPattern, $content, $matches)) {
            // Version block exists
            $existingBindings = $matches[2];

            // Check if abstract already exists
            if (preg_match('/'.preg_quote($abstractFqn, '/').' => \[.*?\],/s', $existingBindings)) {
                // Update existing entry
                $content = preg_replace(
                    '/(private function getServiceImplementations.*?' . $version . ' => \[.*?)'.preg_quote($abstractFqn, '/').' => \[.*?\],/s',
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
            $insertPattern = '/(private function getServiceImplementations\(int \$version\): array\s*\{\s*return match \(\$version\) \{.*?)(            default => \[\],)/s';

            if (preg_match($insertPattern, $content)) {
                $content = preg_replace(
                    $insertPattern,
                    "$1{$newVersionBlock}$2",
                    $content,
                    1
                );
                $this->info("Created new version {$version} block in getServiceImplementations.");
            } else {
                $this->warn("Could not automatically add version {$version} block. Please add it manually to getServiceImplementations().");
            }
        }

        $this->files->put($providerPath, $content);
        $this->info('PlatformServiceProvider updated.');
    }

    private function registerInRepositoryServiceProvider(string $interfaceFqn, string $eloquentFqn): void
    {
        $providerPath = app_path('Providers/RepositoryServiceProvider.php');
        if (!$this->files->exists($providerPath)) {
            $this->error('RepositoryServiceProvider.php not found. Skipping repository registration.');
            return;
        }

        $content = $this->files->get($providerPath);

        // Normalize FQCNs
        $interfaceFqn = ltrim($interfaceFqn, '\\');
        $eloquentFqn  = ltrim($eloquentFqn, '\\');

        $interfaceShort = class_basename($interfaceFqn);
        $eloquentShort  = class_basename($eloquentFqn);

        // Ensure clean `use` imports (no leading backslash, no ::class)
        foreach ([$interfaceFqn, $eloquentFqn] as $fqcn) {
            $useStmt = "use {$fqcn};";
            if (!Str::contains($content, $useStmt)) {
                $content = preg_replace(
                    '/^(namespace\s+[^\n]+;\s*\R)/m',
                    "$0{$useStmt}\n",
                    $content,
                    1
                );
            }
        }

        // Binding line using short names
        $bindLine = "        \$this->app->singleton({$interfaceShort}::class, {$eloquentShort}::class);";

        // If exists, skip; else insert right after register() {
        if (!Str::contains($content, $bindLine)) {
            if (preg_match('/public function register\(\)\s*\{/', $content, $m, PREG_OFFSET_CAPTURE)) {
                $pos = $m[0][1] + strlen($m[0][0]);
                $content = substr($content, 0, $pos) . "\n" . $bindLine . substr($content, $pos);
            } else {
                $content .= "\n{$bindLine}\n";
            }
        }

        $this->files->put($providerPath, $content);
        $this->info('RepositoryServiceProvider updated.');
    }

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
        // Use a more precise pattern that captures until the next version block or default case
        $versionPattern = '/(private function getRequestImplementations\(int \$version\): array\s*\{\s*return match \(\$version\) \{.*?' . $version . ' => \[)(.*?)(\n            \],\n)/s';

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
