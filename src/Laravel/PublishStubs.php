<?php

namespace StageRightLabs\Actions\Laravel;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishStubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actions:stub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish action stub';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Ensure the stubs directory is available.
        if (!is_dir($stubsPath = base_path('stubs'))) {
            (new Filesystem())->makeDirectory($stubsPath);
        }

        // Check to make sure the stub has not already been published.
        if (file_exists($stubsPath . '/action.stub')) {
            $this->error('The action stub has already been published.');
            $this->info($stubsPath . '/action.stub');
        } else {
            file_put_contents(
                $stubsPath . '/action.stub',
                file_get_contents(__DIR__ . '/stubs/action.stub')
            );

            $this->info('Published ' . $stubsPath . '/action.stub');
        }

        return 0;
    }
}
