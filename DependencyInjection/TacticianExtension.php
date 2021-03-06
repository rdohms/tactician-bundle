<?php namespace Xtrasmal\TacticianBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class TacticianExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config/services'));
        $loader->load('services.yml');

        $this->configureCommandBuses($mergedConfig, $container);
    }

    public function getAlias()
    {
        return 'tactician';
    }

    /**
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     */
    private function configureCommandBuses(array $mergedConfig, ContainerBuilder $container)
    {
        foreach ($mergedConfig['commandbus'] as $commandBusName => $commandBusConfig) {
            $middlewares = array_map(
                function ($middlewareServiceId) {
                    return new Reference($middlewareServiceId);
                },
                $commandBusConfig['middleware']
            );

            $serviceName = 'tactician.commandbus.' . $commandBusName;
            $definition = new Definition($container->getParameter('tactician.commandbus.class'), [$middlewares]);
            $container->setDefinition($serviceName, $definition);

            if ($commandBusName === $mergedConfig['default_bus']) {
                $container->setAlias('tactician.commandbus', $serviceName);
            }
        }
    }
}
