<?php

namespace Database\Seeders;

use App\Models\InputStream;
use Illuminate\Database\Seeder;

class TestUdpStreamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test UDP stream with the specific options
        InputStream::create([
            'name' => 'Test UDP Stream with Advanced Options',
            'protocol' => 'udp',
            'url' => 'udp://@239.17.17.81:1234?localaddr=192.168.212.252',
            'status' => 'active',
            'program_id' => '2169',
            'ignore_unknown' => true,
            'map_disable_data' => true,
            'map_disable_subtitles' => true,
            'local_address' => '192.168.212.252',
            'metadata' => [
                'description' => 'Test UDP stream with program ID 2169 and advanced options',
            ],
        ]);
        
        $this->command->info('Test UDP stream created successfully');
    }
} 