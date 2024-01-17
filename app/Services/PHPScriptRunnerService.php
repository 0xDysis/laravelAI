<?php
namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class PHPScriptRunnerService
{
    private $phpBinaryPath;
    private $scriptPath;

    public function __construct()
    {
        // Assign the paths directly, without using the config function
        $this->phpBinaryPath = '/Users/dysisx/Library/Application Support/Herd/bin/php';
        $this->scriptPath = '/Users/dysisx/Documents/assistant/app/Http/Controllers/OpenaiAssistantController.php';
    }

    public function runScript($function, $args = [])
    {
        // Ensure the arguments are correctly formatted
        $process = new Process(array_merge([$this->phpBinaryPath, $this->scriptPath, $function], $args));
        $process->setWorkingDirectory(base_path());  
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = trim($process->getOutput());
        if (!$output) {
            throw new \Exception("No output from PHP script for function: $function");
        }

        return $output;
    }
}

