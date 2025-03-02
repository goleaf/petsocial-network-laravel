<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FailedJobsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('failed_jobs')->count() === 0) {
            // Sample job types
            $jobTypes = [
                'App\\Jobs\\SendNotification',
                'App\\Jobs\\ProcessImage',
                'App\\Jobs\\GenerateReport',
                'App\\Jobs\\SyncUserData',
                'App\\Jobs\\CleanupOldRecords'
            ];
            
            // Sample queues
            $queues = ['default', 'emails', 'notifications', 'processing', 'reports'];
            
            // Sample connections
            $connections = ['database', 'redis', 'sync'];
            
            // Create a few failed jobs
            $failedJobCount = rand(3, 8);
            
            for ($i = 0; $i < $failedJobCount; $i++) {
                $jobType = $jobTypes[array_rand($jobTypes)];
                $queue = $queues[array_rand($queues)];
                $connection = $connections[array_rand($connections)];
                $failedAt = now()->subDays(rand(1, 30))->subHours(rand(1, 24));
                
                DB::table('failed_jobs')->insert([
                    'uuid' => Str::uuid(),
                    'connection' => $connection,
                    'queue' => $queue,
                    'payload' => json_encode([
                        'displayName' => $jobType,
                        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
                        'maxTries' => 3,
                        'timeout' => 60,
                        'data' => [
                            'command' => serialize(new \stdClass()),
                        ],
                    ]),
                    'exception' => "Exception: An error occurred while processing the job.\n\nStack trace:\n#0 /app/Jobs/{$jobType}.php(45): Process->handle()\n#1 /vendor/laravel/framework/src/Illuminate/Queue/CallQueuedHandler.php(59): {$jobType}->handle()\n#2 /vendor/laravel/framework/src/Illuminate/Queue/Jobs/Job.php(88): Illuminate\\Queue\\CallQueuedHandler->call()\n#3 /vendor/laravel/framework/src/Illuminate/Queue/Worker.php(368): Illuminate\\Queue\\Jobs\\Job->fire()\n#4 /vendor/laravel/framework/src/Illuminate/Queue/Worker.php(314): Illuminate\\Queue\\Worker->process()\n#5 /vendor/laravel/framework/src/Illuminate/Queue/Worker.php(134): Illuminate\\Queue\\Worker->runJob()\n#6 /vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(112): Illuminate\\Queue\\Worker->daemon()\n#7 /vendor/laravel/framework/src/Illuminate/Queue/Console/WorkCommand.php(96): Illuminate\\Queue\\Console\\WorkCommand->runWorker()\n#8 /vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): Illuminate\\Queue\\Console\\WorkCommand->handle()\n#9 /vendor/laravel/framework/src/Illuminate/Container/Util.php(40): Illuminate\\Container\\BoundMethod::Illuminate\\Container\\{closure}()\n#10 /vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(93): Illuminate\\Container\\Util::unwrapIfClosure()\n#11 /vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(37): Illuminate\\Container\\BoundMethod::callBoundMethod()\n#12 /vendor/laravel/framework/src/Illuminate/Container/Container.php(651): Illuminate\\Container\\BoundMethod::call()\n#13 /vendor/laravel/framework/src/Illuminate/Console/Command.php(139): Illuminate\\Container\\Container->call()\n#14 /vendor/symfony/console/Command/Command.php(308): Illuminate\\Console\\Command->execute()\n#15 /vendor/laravel/framework/src/Illuminate/Console/Command.php(124): Symfony\\Component\\Console\\Command\\Command->run()\n#16 /vendor/symfony/console/Application.php(998): Illuminate\\Console\\Command->run()\n#17 /vendor/symfony/console/Application.php(299): Symfony\\Component\\Console\\Application->doRunCommand()\n#18 /vendor/symfony/console/Application.php(171): Symfony\\Component\\Console\\Application->doRun()\n#19 /vendor/laravel/framework/src/Illuminate/Console/Application.php(102): Symfony\\Component\\Console\\Application->run()\n#20 /vendor/laravel/framework/src/Illuminate/Foundation/Console/Kernel.php(155): Illuminate\\Console\\Application->run()\n#21 /artisan(37): Illuminate\\Foundation\\Console\\Kernel->handle()\n#22 {main}",
                    'failed_at' => $failedAt,
                ]);
            }
            
            $this->command->info('Failed jobs seeded successfully.');
        }
    }
}
