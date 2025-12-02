<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'view_counters',
            'edit_counters',
            'manage_queues',
            'call_queue',
            'serve_queue',
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p]);
        }

        $admin = Role::where('name','admin')->first();
        $cs = Role::where('name','customer_service')->first();

        if($admin) {
            $admin->permissions()->sync(Permission::pluck('id')->toArray());
        }

        if($cs) {
            $csPerms = Permission::whereIn('name', [
                'view_counters',
                'edit_counters',
                'call_queue',
                'serve_queue',
                'manage_queues',
            ])->pluck('id')->toArray();

            $cs->permissions()->sync($csPerms);
        }
    }
}
