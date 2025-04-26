<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tvmonitor:install-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install demo data for the TV Monitor System';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing TV Monitor System demo data...');
        
        $this->info('Seeding demo data...');
        Artisan::call('db:seed', ['--class' => 'DemoDataSeeder']);
        $this->info('Demo data seeded successfully!');
        
        $this->info('Demo data installation complete!');
        $this->info('');
        $this->info('You can now access the demo data through the admin panel.');
        $this->info('For more information, visit the Demo Data Guide at: /guides/demo-data-guide');
        
        return Command::SUCCESS;
    }
} 