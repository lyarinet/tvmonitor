<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetDashboardPermissions extends Command
{
    protected $signature = 'dashboard:set-permissions';
    protected $description = 'Set up dashboard permissions and assign them to admin role';

    public function handle()
    {
        // Create the view_dashboard permission if it doesn't exist
        $permission = Permission::firstOrCreate(['name' => 'view_dashboard']);
        $this->info('Dashboard permission created.');
        
        // Create the admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $this->info('Admin role ' . ($adminRole->wasRecentlyCreated ? 'created' : 'found') . '.');
        
        // Give the permission to the admin role
        $adminRole->givePermissionTo($permission);
        
        $this->info('Dashboard permissions set up successfully!');
        return Command::SUCCESS;
    }
} 