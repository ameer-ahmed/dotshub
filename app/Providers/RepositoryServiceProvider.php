<?php

namespace App\Providers;

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
