<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestB2Connection extends Command
{
    protected $signature = 'b2:test';
    protected $description = 'Test B2 storage connection';

    public function handle()
    {
        $this->info('Testing B2 Storage Connection...');

        // Check configuration
        // $this->info('B2 Configuration:');
        // $this->line('Account ID: ' . config('filesystems.disks.b2.key'));
        // $this->line('Bucket: ' . config('filesystems.disks.b2.bucket'));
        // $this->line('Region: ' . config('filesystems.disks.b2.region'));
        // $this->line('Endpoint: ' . config('filesystems.disks.b2.endpoint'));

            $this->info('B2 Configuration (from config/app.php):');
            $this->line('Account ID: ' . config('app.b2.key'));
            $this->line('Bucket: ' . config('app.b2.bucket'));
            $this->line('Region: ' . config('app.b2.region'));
            $this->line('Endpoint: ' . config('app.b2.endpoint'));

        try {

            $config = config('filesystems.disks.b2');

            $this->info('Loaded B2 Disk Configuration:');
            foreach ($config as $key => $value) {
                $this->line(" - {$key}: " . (is_null($value) ? 'NULL' : $value));
            }



            // Test connection by listing buckets (or files)
            $this->info('Testing connection...');
            
            // Try to list files in root directory
            $files = Storage::disk('b2')->files('/');
            
            $this->info('Connection successful!');
           $this->info('Files in root: ' . count($files));
            
            foreach ($files as $file) {
                $this->line(" - {$file}");
            }

            // Test upload
            $this->info('Testing upload...');
            $testContent = 'Test file content ' . now()->toString();
            $testPath = 'test-' . uniqid() . '.txt';
            
            $uploaded = Storage::disk('b2')->put($testPath, $testContent);
            
            if ($uploaded) {
                $this->info('Upload test successful!');
                
                // Verify the file exists
                $exists = Storage::disk('b2')->exists($testPath);
                $this->info('File exists: ' . ($exists ? 'YES' : 'NO'));
                
                // Read back the content
                $content = Storage::disk('b2')->get($testPath);
                $this->info('File content: ' . $content);
                
                // Clean up
                Storage::disk('b2')->delete($testPath);
                $this->info('Test file cleaned up');
            } else {
                $this->error('Upload test failed!');
            }

        } catch (\Exception $e) {
            $this->error('Connection failed: ' . $e->getMessage());
            $this->error('Full error: ' . $e->getTraceAsString());
        }

        return Command::SUCCESS;
    }
}