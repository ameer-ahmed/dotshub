<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaratrustSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void|bool
     */
    public function run()
    {
        $this->truncateLaratrustTables();

        $rolesStructure = Config::get('laratrust_seeder.roles_structure');

        if ($rolesStructure === null) {
            $this->command->error(
                'The configuration has not been published. Did you run `php artisan vendor:publish --tag="laratrust-seeder"`'
            );
            $this->command->line('');
            return false;
        }

        // Maps like: c => create, r => read...
        $mapPermission = collect(Config::get('laratrust_seeder.permissions_map', []));

        // Translations: action (create/read/update/delete)
        $actionTranslations = Config::get('laratrust_seeder.permissions_map_translations', []);

        // Translations: module/entity (admins/users/payments/...)
        // Support both keys for safety.
        $moduleTranslations =
            Config::get('laratrust_seeder.permissions_module_translations', []) ?:
                Config::get('laratrust_seeder.permissions_mapped_translations', []);

        foreach ($rolesStructure as $roleKey => $modules) {
            // Role translations (JSON ar/en)
            $roleTranslations = Config::get("laratrust_seeder.roles_translations.{$roleKey}", [
                'display_name' => ['ar' => null, 'en' => null],
                'description'  => ['ar' => null, 'en' => null],
            ]);

            // Create or update role (idempotent)
            $role = \App\Models\Tenant\Role::updateOrCreate(
                ['name' => $roleKey],
                [
                    'display_name' => $roleTranslations['display_name'] ?? ['ar' => null, 'en' => null],
                    'description'  => $roleTranslations['description'] ?? ['ar' => null, 'en' => null],
                    'is_private'   => in_array($roleKey, Config::get('laratrust_seeder.private_roles', []), true),
                    'is_editable'  => !in_array($roleKey, Config::get('laratrust_seeder.not_editable_roles', []), true),
                ]
            );

            $this->command->info('Creating Role ' . strtoupper($roleKey));

            $permissionIds = [];

            // Build permissions for each module
            foreach ($modules as $module => $value) {
                $permsForModule = array_filter(array_map('trim', explode(',', (string) $value)));

                foreach ($permsForModule as $permCode) {
                    $action = $mapPermission->get($permCode); // e.g. 'create'
                    if (!$action) {
                        $this->command->warn("Unknown permission code '{$permCode}' in '{$module}', skipping.");
                        continue;
                    }

                    // Resolve translations (with fallback)
                    $actionAr = $actionTranslations[$action]['ar'] ?? ucfirst($action);
                    $actionEn = $actionTranslations[$action]['en'] ?? ucfirst($action);

                    $moduleAr = $moduleTranslations[$module]['ar'] ?? $this->fallbackArabicModule($module);
                    $moduleEn = $moduleTranslations[$module]['en'] ?? $this->fallbackEnglishModule($module);

                    $displayName = [
                        'ar' => trim($actionAr . ' ' . $moduleAr),
                        'en' => trim($actionEn . ' ' . $moduleEn),
                    ];

                    // Keep slug as action-module => e.g., "create-users"
                    $slug = "{$action}-{$module}";

                    // Create once, update labels if re-seeded elsewhere
                    $permission = \App\Models\Tenant\Permission::updateOrCreate(
                        ['name' => $slug],
                        ['display_name' => $displayName]
                    );

                    $permissionIds[] = $permission->id;

                    $this->command->info("Ensured Permission {$slug} ({$displayName['en']} / {$displayName['ar']})");
                }
            }

            // Attach all permissions to the role (replace existing)
            $role->permissions()->sync($permissionIds);

            // Optionally create a user per role
            if (Config::get('laratrust_seeder.create_users', false)) {
                $this->command->info("Creating '{$roleKey}' user");

                $user = \App\Models\Tenant\User::updateOrCreate(
                    ['email' => "{$roleKey}@app.com"],
                    [
                        'name'     => ucwords(str_replace('_', ' ', $roleKey)),
                        'password' => bcrypt('password'),
                    ]
                );

                // Ensure the role is attached (sync to this one role)
                $user->syncRoles([$role->id]);
            }
        }
    }

    /**
     * Truncates all the laratrust tables and the users table (if configured)
     *
     * @return  void
     */
    public function truncateLaratrustTables()
    {
        $this->command->info('Truncating User, Role and Permission tables');
        Schema::disableForeignKeyConstraints();

        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();

        if (Config::get('laratrust_seeder.truncate_tables', true)) {
            DB::table('roles')->truncate();
            DB::table('permissions')->truncate();

            if (Config::get('laratrust_seeder.create_users', false)) {
                $usersTable = (new \App\Models\Tenant\User)->getTable();
                DB::table($usersTable)->truncate();
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Fallback Arabic module label if not present in config.
     */
    protected function fallbackArabicModule(string $module): string
    {
        return str_replace('_', ' ', $module);
    }

    /**
     * Fallback English module label if not present in config.
     */
    protected function fallbackEnglishModule(string $module): string
    {
        return ucwords(str_replace('_', ' ', $module));
    }
}
