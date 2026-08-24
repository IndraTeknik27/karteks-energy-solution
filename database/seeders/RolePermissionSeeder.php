<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        $resources = [
            'users', 'roles', 'permissions',
            'categories', 'brands', 'products', 'services',
            'orders', 'payments', 'shipments',
            'inventories', 'warehouses', 'stock-adjustments',
            'carts', 'coupons', 'reviews', 'wishlists',
            'custom-battery-requests', 'quotations', 'service-bookings',
            'blogs', 'blog-categories', 'tags', 'pages',
            'faqs', 'testimonials', 'banners', 'menus', 'homepage-sections',
            'newsletter-subscribers', 'contact-messages',
            'site-settings', 'audit-logs', 'notifications', 'fcm-tokens',
            'reports',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $specialPermissions = [
            'manage-settings',
            'view-audit-logs',
            'export-data',
            'import-data',
            'process-refunds',
            'manage-roles',
            'assign-roles',
        ];

        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = "{$action}-{$resource}";
            }
        }

        foreach ($specialPermissions as $special) {
            $permissions[] = $special;
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super-admin' => $permissions,
            'admin' => array_filter($permissions, fn ($p) => ! str_contains($p, 'roles') && ! str_contains($p, 'permissions')),

            'manager' => [
                'view-users', 'create-users', 'update-users',
                'view-orders', 'create-orders', 'update-orders',
                'view-payments', 'update-payments', 'process-refunds',
                'view-products', 'create-products', 'update-products',
                'view-categories', 'create-categories', 'update-categories',
                'view-brands', 'create-brands', 'update-brands',
                'view-services', 'create-services', 'update-services',
                'view-inventories', 'update-inventories',
                'view-warehouses',
                'view-custom-battery-requests', 'update-custom-battery-requests',
                'view-quotations', 'create-quotations', 'update-quotations',
                'view-service-bookings', 'update-service-bookings',
                'view-coupons', 'create-coupons', 'update-coupons',
                'view-reviews', 'update-reviews',
                'view-reports', 'view-audit-logs', 'export-data',
            ],

            'sales' => [
                'view-orders', 'create-orders', 'update-orders',
                'view-products', 'view-categories', 'view-brands',
                'view-custom-battery-requests', 'update-custom-battery-requests',
                'view-quotations', 'create-quotations', 'update-quotations',
                'view-coupons', 'view-reports',
            ],

            'warehouse' => [
                'view-products', 'view-inventories', 'update-inventories',
                'view-warehouses', 'create-warehouses', 'update-warehouses',
                'view-stock-adjustments', 'create-stock-adjustments', 'update-stock-adjustments',
                'view-orders', 'view-shipments', 'create-shipments', 'update-shipments',
            ],

            'finance' => [
                'view-orders', 'view-payments', 'update-payments', 'process-refunds',
                'view-reports', 'export-data',
                'view-custom-battery-requests', 'view-quotations',
            ],

            'customer-service' => [
                'view-users', 'update-users',
                'view-orders', 'view-payments',
                'view-contact-messages', 'update-contact-messages',
                'view-reviews', 'update-reviews',
                'view-service-bookings', 'update-service-bookings',
                'view-newsletter-subscribers',
            ],

            'technician' => [
                'view-service-bookings', 'update-service-bookings',
                'view-products', 'view-services',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // admin-access: gate untuk Filament admin panel
        $adminAccessRole = Role::firstOrCreate(['name' => 'admin-access', 'guard_name' => 'web']);
        // Assign admin-access role ke semua user yang sudah punya super-admin atau admin
        $adminAccessRole->users()->syncWithoutDetaching(
            \App\Models\User::whereHas('roles', fn ($q) => $q->whereIn('name', ['super-admin', 'admin']))->pluck('id')
        );

        $this->command->info('Roles & Permissions seeded: '.count($permissions).' permissions, '.count($roles).'+2 roles.');
    }
}