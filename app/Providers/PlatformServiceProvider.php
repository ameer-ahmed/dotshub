<?php

namespace App\Providers;

use App\Enums\Platform;
use App\Http\Services\V1\Web\Admin\Auth\AuthService;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    private const VERSIONS = [1];

    private int $version;
    private Platform $platform;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->detectPlatformAndVersion();
    }

    private function detectPlatformAndVersion(): void
    {
        // Skip detection if running in console (artisan commands)
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            $this->version = self::VERSIONS[0];
            $this->platform = Platform::cases()[0];
            return;
        }

        // Detect version from URL (api/v1/*, api/v2/*, etc.)
        $versionDetected = false;
        foreach (self::VERSIONS as $version) {
            $pattern = "api/v$version/*";
            if (request()->is($pattern)) {
                $this->version = $version;
                $versionDetected = true;
                break;
            }
        }

        if (!$versionDetected) {
            throw new \InvalidArgumentException(
                'Invalid API version. Supported versions: ' . implode(', ', self::VERSIONS)
            );
        }

        // Detect platform from X-Platform header
        $platformHeader = strtolower(request()->header('X-Platform', ''));

        if (empty($platformHeader)) {
            throw new \InvalidArgumentException('X-Platform header is required');
        }

        foreach (Platform::cases() as $platformCase) {
            if ($platformCase->value === $platformHeader) {
                $this->platform = $platformCase;
                return;
            }
        }

        $validPlatforms = implode(', ', array_map(fn($p) => $p->value, Platform::cases()));
        throw new \InvalidArgumentException(
            "Invalid platform '{$platformHeader}'. Supported platforms: {$validPlatforms}"
        );
    }

    public function register(): void
    {
        $this->bindServices();
        $this->bindRequests();
    }

    private function bindServices(): void
    {
        $serviceBindings = $this->getServiceImplementations($this->version);

        foreach ($serviceBindings as $abstract => $implementations) {
            $this->app->bind($abstract, function () use ($abstract, $implementations) {
                foreach ($implementations as $implementation) {
                    if ($implementation::platform() === $this->platform) {
                        return app($implementation);
                    }
                }
                throw new \RuntimeException("No implementation found for {$abstract} with platform {$this->platform->value}");
            });
        }
    }

    private function bindRequests(): void
    {
        $requestBindings = $this->getRequestImplementations($this->version);

        foreach ($requestBindings as $abstract => $implementations) {
            $this->app->bind($abstract, function () use ($abstract, $implementations) {
                foreach ($implementations as $implementation) {
                    if ($implementation::platform() === $this->platform) {
                        return app($implementation);
                    }
                }
                throw new \RuntimeException("No request implementation found for {$abstract} with platform {$this->platform->value}");
            });
        }
    }

    private function getServiceImplementations(int $version): array
    {
        return match ($version) {
            1 => [
                \App\Http\Services\V1\Abstracts\Merchant\Role\RoleAbstractService::class => [
                    \App\Http\Services\V1\Web\Merchant\Role\RoleService::class,
                ],
                \App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class => [
                    \App\Http\Services\V1\Web\Admin\Auth\AuthService::class,
                ],
                \App\Http\Services\V1\Abstracts\Merchant\Auth\AuthAbstractService::class => [
                    \App\Http\Services\V1\Web\Merchant\Auth\AuthService::class,
                ],
            ],
            // Add V2, V3, etc. here as you create them
            // 2 => [
            //     \App\Http\Services\V2\Abstracts\... => [...],
            // ],
            default => [],
        };
    }

    private function getRequestImplementations(int $version): array
    {
        return match ($version) {
            1 => [
                \App\Http\Requests\V1\Abstracts\Admin\Auth\SignInAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\Admin\Auth\SignInRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\Admin\Auth\SignUpAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\Admin\Auth\SignUpRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\Merchant\Auth\SignInAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\Merchant\Auth\SignInRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\Merchant\Auth\SignUpAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\Merchant\Auth\SignUpRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\Merchant\Role\RoleAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\Merchant\Role\RoleRequest::class,
                ],
            ],
            // Add V2, V3, etc. here as you create them
            // 2 => [
            //     \App\Http\Requests\V2\Abstracts\... => [...],
            // ],
            default => [],
        };
    }
}
