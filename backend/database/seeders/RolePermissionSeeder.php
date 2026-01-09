<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            'scan.create',
            'scan.view-own',
            'scan.view-all',
            'scan.correct',
            'scan.approve-training',
            'model.manage',
            'model.retrain',
            'plant-type.manage',
        ];
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo(['scan.create', 'scan.view-own']);
        $researcherRole = Role::create(['name' => 'researcher']);
        $researcherRole->givePermissionTo([
            'scan.create',
            'scan.view-own',
            'scan.view-all',
            'scan.correct',
            'scan.approve-training',
        ]);
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
