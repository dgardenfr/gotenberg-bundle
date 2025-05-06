<?php

namespace DigitalGarden\GotenbergBundle\Composer;

use Composer\Script\Event;

/**
 * Scripts launched on composer install.
 */
class ComposerInstall
{
    /**
     * Generate the config/packages/dgarden_gotenberg.yaml file.
     *
     * @param Event $event The composer install event.
     *
     * @return void
     * @internal
     */
    public static function createConfigurationFile(Event $event): void
    {
        $io = $event->getIO();
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $dir = dirname($vendorDir);

        if (!is_dir("$dir/config/packages")) {
            $io->warning("Cannot find directory $dir/config/packages, gotenberg bundle left unconfigured.");
        } elseif (!file_exists($dir . '/config/packages/dgarden_gotenberg.yaml')) {
            $io->info("Generate $dir/config/packages/dgarden_gotenberg.yaml.");
            file_put_contents($dir . '/config/packages/dgarden_gotenberg.yaml', <<<EOF
dgarden:
  gotenberg:
    output_path: '%kernel.project_dir%/var/pdf'
EOF
);
        }
    }

    /**
     * Generate the config/routes/dgarden_gotenberg.yaml file.
     *
     * @param Event $event The composer install event.
     *
     * @return void
     * @internal
     */
    public static function createRouteFile(Event $event): void
    {
        $io = $event->getIO();
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        $dir = dirname($vendorDir);

        if (!is_dir("$dir/config/routes")) {
            $io->warning("Cannot find directory $dir/config/routes, gotenberg bundle routes left unconfigured.");
        } elseif (!file_exists($dir . '/config/routes/dgarden_gotenberg.yaml')) {
            $io->info("Generate $dir/config/routes/dgarden_gotenberg.yaml.");
            file_put_contents($dir . '/config/routes/dgarden_gotenberg.yaml', <<<EOF
dgarden_gotenberg:
  resource: '@DigitalGardenGotenbergBundle/config/routes.php'
EOF
            );
        }
    }
}