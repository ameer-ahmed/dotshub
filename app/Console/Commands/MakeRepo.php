<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(
    name: 'make:repo',
    description: 'Generate Repository interface + Eloquent implementation and register binding in RepositoryServiceProvider (supports tenant repos/models)'
)]
class MakeRepo extends Command
{
    protected $signature = 'make:repo
        {name : Base name, e.g. Role}
        {--model= : Eloquent model FQCN or short name (defaults to App\Models\<Name> or App\Models\Tenant\<Name> with --tenant)}
        {--tenant : Generate under Contracts/Tenant and Eloquent/Tenant; default model under Models\Tenant}
        {--no-model : Skip model creation even if it does not exist}
        {--force : Overwrite files if they exist}
    ';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name      = Str::studly($this->argument('name'));    // e.g. Role
        $modelOpt  = $this->option('model');                   // e.g. App\Models\Something or Something
        $isTenant  = (bool) $this->option('tenant');
        $force     = (bool) $this->option('force');
        $noModel   = (bool) $this->option('no-model');

        // ==== Resolve model FQCN (short names resolved by tenancy) ====
        if ($modelOpt) {
            $modelFqn = str_contains($modelOpt, '\\')
                ? ltrim($modelOpt, '\\')
                : ($isTenant ? 'App\\Models\\Tenant\\' : 'App\\Models\\') . Str::studly($modelOpt);
        } else {
            $modelFqn = ($isTenant ? 'App\\Models\\Tenant\\' : 'App\\Models\\') . $name;
        }

        // ==== Create model if it doesn't exist (unless --no-model is set) ====
        if (!$noModel) {
            $this->ensureModelExists($modelFqn, $isTenant, $name);
        }

        // ==== Namespaces & paths (Contracts & Eloquent with Tenant variants) ====
        if ($isTenant) {
            $repoInterfaceNs  = 'App\\Repository\\Contracts\\Tenant';
            $repoInterfaceDir = app_path('Repository/Contracts/Tenant');
            $repoEloquentNs   = 'App\\Repository\\Eloquent\\Tenant';
            $repoEloquentDir  = app_path('Repository/Eloquent/Tenant');
        } else {
            $repoInterfaceNs  = 'App\\Repository\\Contracts';
            $repoInterfaceDir = app_path('Repository/Contracts');
            $repoEloquentNs   = 'App\\Repository\\Eloquent';
            $repoEloquentDir  = app_path('Repository/Eloquent');
        }

        $repoInterfaceCls  = "{$name}RepositoryInterface";
        $repoInterfacePath = "{$repoInterfaceDir}/{$repoInterfaceCls}.php";

        $repoEloquentCls   = "{$name}Repository";
        $repoEloquentPath  = "{$repoEloquentDir}/{$repoEloquentCls}.php";

        // ==== Create interface ====
        $this->makeDirectory($repoInterfaceDir);
        if ($force || !$this->files->exists($repoInterfacePath)) {
            $this->writeFile($repoInterfacePath, $this->renderRepoInterface($repoInterfaceNs, $repoInterfaceCls));
            $this->info("Created: {$repoInterfacePath}");
        } else {
            $this->line("Exists:  {$repoInterfacePath}");
        }

        // ==== Create eloquent implementation ====
        $this->makeDirectory($repoEloquentDir);
        if ($force || !$this->files->exists($repoEloquentPath)) {
            $this->writeFile(
                $repoEloquentPath,
                $this->renderRepoConcrete(
                    $repoEloquentNs,
                    $repoEloquentCls,
                    $modelFqn,
                    $repoInterfaceNs,
                    $repoInterfaceCls
                )
            );
            $this->info("Created: {$repoEloquentPath}");
        } else {
            $this->line("Exists:  {$repoEloquentPath}");
        }

        // ==== Register binding in RepositoryServiceProvider with clean imports ====
        $this->registerInRepositoryServiceProvider(
            "{$repoInterfaceNs}\\{$repoInterfaceCls}",
            "{$repoEloquentNs}\\{$repoEloquentCls}"
        );

        $this->info('Repository scaffolding complete ✅');
        return self::SUCCESS;
    }

    // ===== Renderers =====

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
        string $modelFqn,        // e.g. App\Models\Role or App\Models\Tenant\Role
        string $interfaceNs,     // e.g. App\Repository\Contracts or App\Repository\Contracts\Tenant
        string $interfaceCls     // e.g. RoleRepositoryInterface
    ): string {
        $modelFqn   = ltrim($modelFqn, '\\');
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

    // ===== Helpers =====

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

        // Ensure clean `use` imports (no leading backslash, no ::class in use)
        foreach ([$interfaceFqn, $eloquentFqn] as $fqcn) {
            $useStmt = "use {$fqcn};";
            if (!str_contains($content, $useStmt)) {
                $content = preg_replace(
                    '/^(namespace\s+[^\n]+;\s*\R)/m',
                    "$0{$useStmt}\n",
                    $content,
                    1
                );
            }
        }

        // Binding line using short names; no extra spaces
        $bindLine = "        \$this->app->singleton({$interfaceShort}::class, {$eloquentShort}::class);";

        // Skip if already present
        if (str_contains($content, $bindLine)) {
            $this->files->put($providerPath, $content);
            $this->line('RepositoryServiceProvider already has this binding.');
            return;
        }

        // Insert inside register()
        if (preg_match('/public function register\(\)\s*\{/', $content, $m, PREG_OFFSET_CAPTURE)) {
            $pos = $m[0][1] + strlen($m[0][0]);
            $content = substr($content, 0, $pos) . "\n" . $bindLine . substr($content, $pos);
        } else {
            // Fallback: append to end
            $content .= "\n{$bindLine}\n";
        }

        $this->files->put($providerPath, $content);
        $this->info('RepositoryServiceProvider updated.');
    }

    // ===== Model creation =====

    private function ensureModelExists(string $modelFqn, bool $isTenant, string $name): void
    {
        // Convert FQCN to file path
        $modelPath = base_path(str_replace('\\', '/', str_replace('App\\', 'app/', $modelFqn)) . '.php');

        if ($this->files->exists($modelPath)) {
            $this->line("Model exists: {$modelPath}");
            return;
        }

        // Create the model
        $this->info("Creating model: {$modelFqn}");

        if ($isTenant) {
            // For tenant models, create in Models/Tenant directory
            $this->createTenantModel($name, $modelPath);
        } else {
            // For regular models, use artisan command
            $this->call('make:model', ['name' => $name]);
        }
    }

    private function createTenantModel(string $name, string $modelPath): void
    {
        $namespace = 'App\\Models\\Tenant';
        $modelContent = <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    use HasFactory;

    protected \$fillable = [];
}

PHP;

        $this->makeDirectory(dirname($modelPath));
        $this->writeFile($modelPath, $modelContent);
        $this->info("Created: {$modelPath}");
    }
}