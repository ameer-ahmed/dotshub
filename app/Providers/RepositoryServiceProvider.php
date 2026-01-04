<?php

namespace App\Providers;

use App\Repository\Eloquent\Tenant\QuestionnaireQuestionRepository;
use App\Repository\Contracts\Tenant\QuestionnaireQuestionRepositoryInterface;
use App\Repository\Eloquent\Tenant\QuestionnaireRepository;
use App\Repository\Contracts\Tenant\QuestionnaireRepositoryInterface;
use App\Repository\Eloquent\Tenant\QuestionOptionRepository;
use App\Repository\Contracts\Tenant\QuestionOptionRepositoryInterface;
use App\Repository\Eloquent\Tenant\QuestionRepository;
use App\Repository\Contracts\Tenant\QuestionRepositoryInterface;
use App\Repository\Eloquent\Tenant\SettingRepository;
use App\Repository\Contracts\Tenant\SettingRepositoryInterface;
use App\Repository\Eloquent\Tenant\BranchRepository;
use App\Repository\Contracts\Tenant\BranchRepositoryInterface;
use App\Repository\Contracts\DomainRepositoryInterface;
use App\Repository\Contracts\TenantRepositoryInterface;
use App\Repository\Contracts\RepositoryInterface;
use App\Repository\Contracts\Tenant\RoleRepositoryInterface;
use App\Repository\Contracts\Tenant\UserRepositoryInterface;
use App\Repository\Eloquent\DomainRepository;
use App\Repository\Eloquent\TenantRepository;
use App\Repository\Eloquent\Repository;
use App\Repository\Eloquent\Tenant\RoleRepository;
use App\Repository\Eloquent\Tenant\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(QuestionnaireQuestionRepositoryInterface::class, QuestionnaireQuestionRepository::class);
        $this->app->singleton(QuestionnaireRepositoryInterface::class, QuestionnaireRepository::class);
        $this->app->singleton(QuestionOptionRepositoryInterface::class, QuestionOptionRepository::class);
        $this->app->singleton(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->singleton(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->singleton(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->singleton(DomainRepositoryInterface::class, DomainRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(RepositoryInterface::class, Repository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(TenantRepositoryInterface::class, TenantRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
