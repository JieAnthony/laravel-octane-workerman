<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands\Concerns;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Workerman\Psr7\Response;
use Workerman\Worker;

trait InstallsWorkermanDependencies
{
    /**
     * Ensure the Workerman package is installed into the project.
     *
     * @return bool
     */
    protected function ensureWorkermanPackageIsInstalled()
    {
        if (class_exists(Worker::class) && class_exists(Response::class)) {
            return true;
        }

        if (!$this->confirm('Octane requires "workerman/workerman" and "workerman/psr7". Do you wish to install it as a dependency?')) {
            $this->error('Octane requires "workerman/workerman" and and "workerman/psr7".');

            return false;
        }

        $command = $this->findComposer() . ' require workerman/workerman:^4.0 workerman/psr7:^1.4.4 --with-all-dependencies';

        $process = Process::fromShellCommandline($command, null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('Warning: ' . $e->getMessage());
            }
        }

        try {
            $process->run(function ($type, $line) {
                $this->output->write($line);
            });
        } catch (ProcessSignaledException $e) {
            if (extension_loaded('pcntl') && $e->getSignal() !== SIGINT) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    protected function findComposer()
    {
        $composerPath = getcwd() . '/composer.phar';

        $phpPath = (new PhpExecutableFinder)->find();

        if (!file_exists($composerPath)) {
            $composerPath = (new ExecutableFinder())->find('composer');
        }

        return '"' . $phpPath . '" ' . $composerPath;
    }
}
