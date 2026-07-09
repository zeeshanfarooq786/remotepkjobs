<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RunPythonAgent extends Command
{
    private const ALLOWED_AGENTS = [
        'job_scraper',
        'rate_updater',
        'github_updater',
        'update_exchange_rates',
        'blog_generator',
    ];

    protected $signature = 'agent:run {agent : Agent script name without .py (e.g. job_scraper)}';

    protected $description = 'Run a DevRates Python agent from the python/agents directory';

    public function handle(): int
    {
        $agent = $this->argument('agent');

        if (! in_array($agent, self::ALLOWED_AGENTS, true)) {
            $this->error('Unknown agent. Allowed: '.implode(', ', self::ALLOWED_AGENTS));

            return self::FAILURE;
        }

        $pythonDir = base_path('python');
        $script = $pythonDir.DIRECTORY_SEPARATOR.'agents'.DIRECTORY_SEPARATOR.$agent.'.py';

        if (! is_file($script)) {
            $this->error("Agent script not found: {$script}");

            return self::FAILURE;
        }

        $pythonBinary = $this->resolvePythonBinary($pythonDir);
        $logDir = $pythonDir.DIRECTORY_SEPARATOR.'logs';
        $logFile = $logDir.DIRECTORY_SEPARATOR.'cron_'.now()->format('Ymd').'.log';

        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $this->info("Running {$agent} with {$pythonBinary}");

        $result = Process::path($pythonDir)
            ->timeout(3600)
            ->run(
                [$pythonBinary, 'agents'.DIRECTORY_SEPARATOR.$agent.'.py'],
                function (string $type, string $output) use ($logFile) {
                    file_put_contents($logFile, $output, FILE_APPEND);
                    $this->output->write($output);
                }
            );

        if (! $result->successful()) {
            $this->error("Agent {$agent} failed with exit code {$result->exitCode()}.");

            return self::FAILURE;
        }

        $this->info("Agent {$agent} completed successfully.");

        return self::SUCCESS;
    }

    private function resolvePythonBinary(string $pythonDir): string
    {
        $configured = env('PYTHON_PATH');

        if ($configured && is_file($configured)) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            return 'python';
        }

        $venvPython = $pythonDir.DIRECTORY_SEPARATOR.'venv'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'python';

        if (is_file($venvPython)) {
            return $venvPython;
        }

        return 'python3';
    }
}
