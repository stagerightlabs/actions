<?php

namespace StageRightLabs\Actions\Laravel;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeAction extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action class';

    /**
     * The file path destination for the new file.
     *
     * @var string
     */
    protected $path;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action
                            {name}
                            {--d|domain= : Optionally specify a domain for this action }
                            {--p|path= : Optionally specify a specific path for this action }';

    /**
     * The template used to build the new class file.
     *
     * @var string
     */
    protected $stub;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getStub();
        $this->determineOutputPath();

        if (file_exists($this->path)) {
            $this->error('This action already exists.');
            $this->info($this->path);
            return 1;
        }

        $this->ensureDestinationDirectoryExists();
        $this->makeAction();
        $this->info("Published {$this->path}");
        $this->makeUnitTest();

        return 0;
    }

    /**
     * Retrieve the template stub.
     *
     * @return void
     */
    protected function getStub()
    {
        if (file_exists(base_path('stubs/action.stub'))) {
            return file_get_contents(base_path('stubs/action.stub'));
        }

        $this->stub = file_get_contents(__DIR__ . '/stubs/action.stub');
    }

    /**
     * Determine the location to store the new file.
     *
     * @return void
     */
    protected function determineOutputPath()
    {
        $destination = $this->option('path') ?? config('actions.paths.action');

        if ($domain = $this->option('domain')) {
            $destination = config('actions.paths.domain') . '/' . $domain . '/Actions';
        }

        $this->path = Str::finish(base_path($destination), '/')
            . collect(preg_split('/[.\/]+/', $this->argument('name')))
                ->map([Str::class, 'studly'])
                ->implode('/')
            . '.php';
    }

    /**
     * Ensure the destination directories indicated in the path exist.
     *
     * @return void
     */
    public function ensureDestinationDirectoryExists()
    {
        File::makeDirectory(
            dirname($this->path),
            $mode = 0777,
            $recursive = true,
            $force = true
        );
    }

    /**
     * Convert the template into an action class and write it to disk.
     *
     * @return void
     */
    protected function makeAction()
    {
        $action = preg_replace_array(
            ['/\[class\]/', '/\[namespace\]/'],
            [$this->className(), $this->namespace()],
            $this->stub
        );

        File::put($this->path, $action);
    }

    /**
     * Determine the appropriate namespace for the new class.
     *
     * @return string
     */
    protected function namespace()
    {
        return $this->laravel->getNamespace()
            . str_replace('/', '\\', Str::after(dirname($this->path), app_path() . '/'));
    }

    /**
     * Determine an appropriate name for the new class.
     *
     * @return string
     */
    protected function className()
    {
        return Str::studly(class_basename($this->argument('name')));
    }

    /**
     * Call the artisan make:test --unit command.
     *
     * @return void
     */
    protected function makeUnitTest()
    {
        $name = 'Actions/';

        if ($domain = $this->option('domain')) {
            $name .= $domain . '/';
        }

        $name .= $this->argument('name')
            . 'Test';

        $this->call('make:test', [
            'name' => $name,
            '--unit' => true,
        ]);
    }
}
