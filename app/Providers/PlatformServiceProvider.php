<?php

namespace App\Providers;

use App\Enums\Platform;
use App\Http\Services\V1\Web\Admin\Auth\AuthService;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    private const VERSIONS = [1];
    private const FALLBACK_VERSION = 1;
    private const FALLBACK_PLATFORM = Platform::WEB;

    private ?int $version;
    private ?Platform $platform;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->detectPlatformAndVersion();
    }

    private function detectPlatformAndVersion(): void
    {
        foreach (self::VERSIONS as $version) {
            foreach (Platform::cases() as $platformCase) {
                $pattern = "api/v$version/{$platformCase->value}/*";

                if (request()->is($pattern)) {
                    $this->version = $version;
                    $this->platform = $platformCase;
                    return;
                }
            }
        }

        $this->version = self::FALLBACK_VERSION;
        $this->platform = self::FALLBACK_PLATFORM;
    }

    public function register(): void
    {
        $this->bindServices();
    }

    private function bindServices(): void
    {
        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService::class,
            $this->resolve(\App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService::class)
        );

        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class,
            $this->resolve(\App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class)
        );

        $this->app->bind(
            \App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService::class,
            $this->resolve(\App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService::class)
        );
    }

    private function resolve(string $abstract): string
    {
        $implementations = $this->getConcreteImplementations($abstract);

        foreach ($implementations as $implementation) {
            if ($implementation::platform() === $this->platform) {
                return $implementation;
            }
        }

        throw new \RuntimeException("No implementation found for {$abstract}");
    }

    private function getConcreteImplementations(string $abstract): array
    {
        return match ($abstract) {
            \App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class => [
                \App\Http\Services\V1\Web\Admin\Auth\AuthService::class,
            ],

            \App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService::class => [
                \App\Http\Services\V1\Web\Merchant\Auth\AuthService::class,
            ],
            \App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService::class => [
                \App\Http\Services\V1\Web\Merchant\Role\RoleService::class,
            ],

            default => [],
        };
    }
}
