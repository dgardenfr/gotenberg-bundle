<?php
/**
 * This file is a part of the Digital Garden gotenberg bundle.
 */
namespace DigitalGarden\GotenbergBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Gotenberg bundle.
 */
class DigitalGardenGotenbergBundle extends AbstractBundle
{
    /**
     * Extension alias.
     */
    protected string $extensionAlias = 'dgarden';

    /**
     * {@inheritDoc}
     */
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('gotenberg')
            ->end()
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $bundle = $config['gotenberg'] ?? [];

        // Load services
        $container->import(__DIR__ . '/../config/services.xml');

        // Configure parameters
    }
}