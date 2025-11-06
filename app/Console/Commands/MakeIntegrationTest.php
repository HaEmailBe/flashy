<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeIntegrationTest extends GeneratorCommand
{
    protected $name = 'make:integration-test {name}';

    protected $description = 'Create a new integration test';

    protected $type = 'Integration Test';

    public function __construct(\Illuminate\Filesystem\Filesystem $files)
    {
        parent::__construct($files);
    }

    protected function getStub()
    {
        return $this->resolveStubPath('/stubs/integration-test.stub');
    }

    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . $stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return 'Tests\\Integration';
    }

    protected function rootNamespace()
    {
        return '';
    }

    protected function getPath($name)
    {
        $name = str_replace('Tests\\Integration\\', '', $name);
        return base_path('tests/Integration/' . str_replace('\\', '/', $name) . '.php');
    }

    // protected function getArguments()
    // {
    //     return [
    //         ['name', InputArgument::REQUIRED, 'The name of the test'],
    //     ];
    // }
}
