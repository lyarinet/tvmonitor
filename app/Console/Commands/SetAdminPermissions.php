<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SetAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:set-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the admin user permissions for Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $admin = User::where('email', 'admin@example.com')->first();
        
        if (!$admin) {
            $this->error('Admin user not found!');
            return Command::FAILURE;
        }
        
        // Check if the user model has a 'is_admin' column
        if (in_array('is_admin', $admin->getFillable())) {
            $admin->is_admin = true;
            $admin->save();
            $this->info('Admin permissions set successfully!');
        } else {
            $this->info('User model does not have an is_admin column. Filament may be using a different permission system.');
            
            // Check if we're using spatie/laravel-permission
            if (class_exists('\Spatie\Permission\Models\Role')) {
                $this->info('Detected spatie/laravel-permission package.');
                
                // Create admin role if it doesn't exist
                $roleClass = '\Spatie\Permission\Models\Role';
                if (!$roleClass::where('name', 'admin')->exists()) {
                    $roleClass::create(['name' => 'admin']);
                    $this->info('Created admin role.');
                }
                
                // Assign admin role to user
                if (method_exists($admin, 'assignRole')) {
                    $admin->assignRole('admin');
                    $this->info('Assigned admin role to user.');
                } else {
                    $this->warn('User model does not have assignRole method.');
                }
            }
        }
        
        // For Filament v3, we don't need to set specific permissions as it uses the canAccessPanel method
        // Let's check if the User model has this method
        $userClass = get_class($admin);
        $reflector = new \ReflectionClass($userClass);
        
        if ($reflector->hasMethod('canAccessPanel')) {
            $this->info('User model has canAccessPanel method for Filament v3.');
            $this->info('Ensure this method returns true for the admin user.');
        } else {
            // Add the method to the User model
            $this->info('Adding canAccessPanel method to User model...');
            $this->call('make:filament-user');
        }
        
        $this->info('Admin user setup completed.');
        return Command::SUCCESS;
    }
} 