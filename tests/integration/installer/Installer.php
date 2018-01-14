<?php

namespace lucidtaz\yii2scssphp\tests\integration\installer;

use Symfony\Component\Process\Process;

class Installer
{
    /**
     * @var string Source of the library to be installed
     */
    private $librarySourceDirectory;

    /**
     * @var string Directory of the project where the library will be used in
     */
    private $projectTargetDirectory;

    /**
     * @var callable
     */
    private $output;

    /**
     * @var int Timeout used for spawned processes
     */
    private $innerProcessTimeoutSeconds = 60*60;

    public function __construct(
        string $librarySourceDirectory,
        string $projectTargetDirectory,
        callable $output = null
    ) {
        $this->librarySourceDirectory = $librarySourceDirectory;
        $this->projectTargetDirectory = $projectTargetDirectory;
        $this->output = $output ?? function () {
            // Ignore, no output
        };
    }

    public function run()
    {
        $this->removeOldInstallationIfNeeded();
        $this->installYiiApplication();
        $this->installLibrary();
        $this->insertTestApplicationCode();
    }

    private function removeOldInstallationIfNeeded()
    {
        $output = $this->output;

        $output("Cleaning up previous install...\n");

        $process = new Process('rm -rf ' . escapeshellarg($this->projectTargetDirectory));
        $process->run();
    }

    private function installYiiApplication()
    {
        $output = $this->output;

        $output("Installing Yii basic in $this->projectTargetDirectory...\n");

        $escapedTarget = escapeshellarg($this->projectTargetDirectory);

        // TODO: Make sure we install the same version that the build system
        // tests against (so the build matrix keeps working fine)
        // Probably ask Composer what is the current Yii version, or extract it from
        // composer.lock
        $process = new Process(
            "composer create-project --no-interaction --no-dev --prefer-dist yiisoft/yii2-app-basic $escapedTarget"
        );
        $process->setTimeout($this->innerProcessTimeoutSeconds);
        $statusCode = $process->run(function (string $type, string $data) use ($output) {
            $output($data);
        });

        if ($statusCode !== 0) {
            $output("Yii installation failed.\n");
            die();
        }
    }

    private function installLibrary()
    {
        $output = $this->output;

        $output("Updating composer.json to read library from disk...\n");

        $escapedRepositoryUrl = escapeshellarg(realpath($this->librarySourceDirectory));

        $addRepoProcess = new Process(
            "composer config --no-interaction repositories.lucidtaz path $escapedRepositoryUrl",
            $this->projectTargetDirectory
        );
        $addRepoProcess->run(function (string $type, string $data) use ($output) {
            $output($data);
        });

        $output("Installing library to $this->projectTargetDirectory...\n");

        $installLibraryProcess = new Process(
            "composer require --no-interaction lucidtaz/yii2-scssphp:dev-master",
            $this->projectTargetDirectory
        );
        $installLibraryProcess->setTimeout($this->innerProcessTimeoutSeconds);
        $installLibraryProcess->run(function (string $type, string $data) use ($output) {
            $output($data);
        });
    }

    private function insertTestApplicationCode()
    {
        $output = $this->output;

        $output("Installing test application files to $this->projectTargetDirectory...\n");

        copy(
            dirname(__DIR__) . '/files/AppAsset.php',
            $this->projectTargetDirectory . '/assets/AppAsset.php'
        ) or die('Application code copy failed');

        @mkdir($this->projectTargetDirectory . '/assets/source');
        copy(
            dirname(__DIR__) . '/files/test.scss',
            $this->projectTargetDirectory . '/assets/source/test.scss'
        ) or die('Application code copy failed');

        copy(
            dirname(__DIR__) . '/files/config_web.php',
            $this->projectTargetDirectory . '/config/web.php'
        ) or die('Application code copy failed');
    }
}
