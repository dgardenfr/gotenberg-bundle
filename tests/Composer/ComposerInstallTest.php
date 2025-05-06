<?php

namespace DigitalGarden\GotenbergBundle\Tests\Composer;

use Composer\Composer;
use Composer\Config;
use Composer\IO\ConsoleIO;
use Composer\Script\Event;
use DigitalGarden\GotenbergBundle\Composer\ComposerInstall;
use PHPUnit\Framework\TestCase;

/**
 * ComposerInstall test suite.
 *
 * @covers \DigitalGarden\GotenbergBundle\Composer\ComposerInstall
 */
class ComposerInstallTest extends TestCase
{
    /**
     * Test configuration files creation.
     *
     * @covers \DigitalGarden\GotenbergBundle\Composer\ComposerInstall::createRouteFile
     * @covers \DigitalGarden\GotenbergBundle\Composer\ComposerInstall::createConfigurationFile
     *
     * @return void
     */
    public function testComposerInstallFileCreation(): void
    {
        $vardir = realpath(__DIR__ . '/../var');
        $routeDir = "$vardir/config/routes";
        $packageDir = "$vardir/config/packages";
        foreach ([$routeDir, $packageDir] as $outdir) {
            if (is_dir($outdir)) {
                $dir = opendir($outdir);
                while ($file = readdir($dir)) {
                    if ($file != "." && $file != "..") {
                        unlink("$outdir/$file");
                    }
                }
                closedir($dir);
                rmdir($outdir);
            }
        }
        mkdir($routeDir, 0777, true);
        mkdir($packageDir, 0777, true);

        $io = $this->getMockBuilder(ConsoleIO::class)
            ->disableOriginalConstructor()
            ->addMethods(['getComposer'])
            ->onlyMethods(['info'])
            ->getMock();
        $composer = $this->getMockBuilder(Composer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();
        $config = $this->createMock(Config::class);
        $event = new Event('install', $composer, $io,);

        $io->method('getComposer')->willReturn($composer);

        $matcher = self::exactly(2);
        $io->expects($matcher)->method('info')->willReturnCallback(
            function (string $message) use ($vardir, $matcher) {
                $this->assertEquals(match($matcher->getInvocationCount()) {
                    1 => "Generate $vardir/config/packages/dgarden_gotenberg.yaml.",
                    2 => "Generate $vardir/config/routes/dgarden_gotenberg.yaml."
                }, $message);
            }
        );
        $composer->method('getConfig')->willReturn($config);
        $config->expects(self::exactly(2))->method('get')->with('vendor-dir')->willReturn("$vardir/vendors");

        ComposerInstall::createConfigurationFile($event);
        ComposerInstall::createRouteFile($event);

        unlink("$vardir/config/routes/dgarden_gotenberg.yaml");
        unlink("$vardir/config/packages/dgarden_gotenberg.yaml");
        rmdir("$vardir/config/routes");
        rmdir("$vardir/config/packages");
        rmdir("$vardir/config");
    }

    /**
     * Test configuration files creation without creating packages/routes directories.
     *
     * @covers \DigitalGarden\GotenbergBundle\Composer\ComposerInstall::createRouteFile
     * @covers \DigitalGarden\GotenbergBundle\Composer\ComposerInstall::createConfigurationFile
     *
     * @return void
     */
    public function testConfigurationFileNoCreation(): void
    {
        $vardir = realpath(__DIR__ . '/../var');
        $routeDir = "$vardir/config/routes";
        $packageDir = "$vardir/config/packages";
        foreach ([$routeDir, $packageDir] as $outdir) {
            if (is_dir($outdir)) {
                $dir = opendir($outdir);
                while ($file = readdir($dir)) {
                    if ($file != "." && $file != "..") {
                        unlink("$outdir/$file");
                    }
                }
                closedir($dir);
                rmdir($outdir);
            }
        }

        $io = $this->getMockBuilder(ConsoleIO::class)
            ->disableOriginalConstructor()
            ->addMethods(['getComposer'])
            ->onlyMethods(['warning'])
            ->getMock();
        $composer = $this->getMockBuilder(Composer::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfig'])
            ->getMock();
        $config = $this->createMock(Config::class);
        $event = new Event('install', $composer, $io,);

        $io->method('getComposer')->willReturn($composer);

        $matcher = self::exactly(2);
        $io->expects($matcher)->method('warning')->willReturnCallback(
            function (string $message) use ($vardir, $matcher) {
                $this->assertEquals(match($matcher->getInvocationCount()) {
                    1 => "Cannot find directory $vardir/config/packages, gotenberg bundle left unconfigured.",
                    2 => "Cannot find directory $vardir/config/routes, gotenberg bundle routes left unconfigured.",
                }, $message);
            }
        );
        $composer->method('getConfig')->willReturn($config);
        $config->expects(self::exactly(2))->method('get')->with('vendor-dir')->willReturn("$vardir/vendors");

        ComposerInstall::createConfigurationFile($event);
        ComposerInstall::createRouteFile($event);
    }
}