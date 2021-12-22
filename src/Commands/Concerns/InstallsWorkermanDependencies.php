<?php

namespace JieAnthony\LaravelOctaneWorkerman\Commands\Concerns;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
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
        if (class_exists(Worker::class)) {
            return true;
        }

        if (!extension_loaded('pcntl')) {
            $this->error("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");

            return false;
        }

        if (!extension_loaded('posix')) {
            $this->error("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");

            return false;
        }

        if (!$this->confirm('Octane requires "workerman/workerman". Do you wish to install it as a dependency?')) {
            $this->error('Octane requires "workerman/workerman"');

            return false;
        }

        $command = $this->findComposer() . ' require workerman/workerman:^4.0 --with-all-dependencies';

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

        $phpPath = (new PhpExecutableFinder())->find();

        if (!file_exists($composerPath)) {
            $composerPath = (new ExecutableFinder())->find('composer');
        }

        return '"' . $phpPath . '" ' . $composerPath;
    }
}
