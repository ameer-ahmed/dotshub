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
        $this->bindResources();
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

    private function bindResources(): void
    {
        $resourceBindings = $this->getResourceImplementations($this->version);

        foreach ($resourceBindings as $abstract => $implementations) {
            $this->app->bind($abstract, function ($app, $parameters) use ($implementations, $abstract) {
                foreach ($implementations as $implementation) {
                    if ($implementation::platform() === $this->platform) {
                        return $app->make($implementation, $parameters);
                    }
                }
                throw new \RuntimeException("No resource implementation found for {$abstract} with platform {$this->platform->value}");
            });
        }
    }

    private function getServiceImplementations(int $version): array
    {
        return match ($version) {
            1 => [
                \App\Http\Services\V1\Abstracts\System\Role\RoleAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Role\RoleService::class,
                ],
                \App\Http\Services\V1\Abstracts\Admin\Auth\AuthAbstractService::class => [
                    \App\Http\Services\V1\Web\Admin\Auth\AuthService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\Auth\AuthAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Auth\AuthService::class,
                ],                \App\Http\Services\V1\Abstracts\System\Branch\BranchAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Branch\BranchService::class,
                    \App\Http\Services\V1\Mobile\System\Branch\BranchService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\Settings\SettingAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Settings\SettingService::class,
                    \App\Http\Services\V1\Mobile\System\Settings\SettingService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\Question\QuestionAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Question\QuestionService::class,
                    \App\Http\Services\V1\Mobile\System\Question\QuestionService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractService::class => [
                    \App\Http\Services\V1\Web\System\QuestionOption\QuestionOptionService::class,
                    \App\Http\Services\V1\Mobile\System\QuestionOption\QuestionOptionService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractService::class => [
                    \App\Http\Services\V1\Web\System\Questionnaire\QuestionnaireService::class,
                    \App\Http\Services\V1\Mobile\System\Questionnaire\QuestionnaireService::class,
                ],
                \App\Http\Services\V1\Abstracts\System\QuestionnaireQuestion\QuestionnaireQuestionAbstractService::class => [
                    \App\Http\Services\V1\Web\System\QuestionnaireQuestion\QuestionnaireQuestionService::class,
                    \App\Http\Services\V1\Mobile\System\QuestionnaireQuestion\QuestionnaireQuestionService::class,
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
                \App\Http\Requests\V1\Abstracts\System\Auth\SignInAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Auth\SignInRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\Auth\SignUpAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Auth\SignUpRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\Role\RoleAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Role\RoleRequest::class,
                ],                \App\Http\Requests\V1\Abstracts\System\Branch\BranchAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Branch\BranchRequest::class,
                    \App\Http\Requests\V1\Mobile\System\Branch\BranchRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\Settings\SettingAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Settings\SettingRequest::class,
                    \App\Http\Requests\V1\Mobile\System\Settings\SettingRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\Question\QuestionAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Question\QuestionRequest::class,
                    \App\Http\Requests\V1\Mobile\System\Question\QuestionRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\QuestionOption\QuestionOptionRequest::class,
                    \App\Http\Requests\V1\Mobile\System\QuestionOption\QuestionOptionRequest::class,
                ],
                \App\Http\Requests\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\Questionnaire\QuestionnaireRequest::class,
                    \App\Http\Requests\V1\Mobile\System\Questionnaire\QuestionnaireRequest::class,
                ],

            ],
            // Add V2, V3, etc. here as you create them
            // 2 => [
            //     \App\Http\Requests\V2\Abstracts\... => [...],
            // ],
            1 => [
                \App\Http\Requests\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractRequest::class => [
                    \App\Http\Requests\V1\Web\System\QuestionOption\QuestionOptionRequest::class,
                ],
            ],
            default => [],
        };
    }

    private function getResourceImplementations(int $version): array
    {
        return match ($version) {
            1 => [
                \App\Http\Resources\V1\Abstracts\System\Branch\BranchAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\Branch\BranchResource::class,
                    \App\Http\Resources\V1\Mobile\System\Branch\BranchResource::class,
                ],
                \App\Http\Resources\V1\Abstracts\System\Question\QuestionTypeAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\Question\QuestionTypeResource::class,
                ],
                \App\Http\Resources\V1\Abstracts\System\Settings\SettingAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\Settings\SettingResource::class,
                    \App\Http\Resources\V1\Mobile\System\Settings\SettingResource::class,
                ],                \App\Http\Resources\V1\Abstracts\System\Question\QuestionAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\Question\QuestionResource::class,
                    \App\Http\Resources\V1\Mobile\System\Question\QuestionResource::class,
                ],
                \App\Http\Resources\V1\Abstracts\System\QuestionOption\QuestionOptionAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\QuestionOption\QuestionOptionResource::class,
                    \App\Http\Resources\V1\Mobile\System\QuestionOption\QuestionOptionResource::class,
                ],
                \App\Http\Resources\V1\Abstracts\System\Questionnaire\QuestionnaireAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\Questionnaire\QuestionnaireResource::class,
                    \App\Http\Resources\V1\Mobile\System\Questionnaire\QuestionnaireResource::class,
                ],
                \App\Http\Resources\V1\Abstracts\System\QuestionnaireQuestion\QuestionnaireQuestionAbstractResource::class => [
                    \App\Http\Resources\V1\Web\System\QuestionnaireQuestion\QuestionnaireQuestionResource::class,
                    \App\Http\Resources\V1\Mobile\System\QuestionnaireQuestion\QuestionnaireQuestionResource::class,
                ],

            ],
            // Add V2, V3, etc. here as you create them
            // 2 => [
            //     \App\Http\Resources\V2\Abstracts\... => [...],
            // ],
            default => [],
        };
    }
}
