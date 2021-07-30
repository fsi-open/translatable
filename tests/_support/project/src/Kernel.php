<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

use function sprintf;

final class Kernel extends HttpKernel\Kernel
{
    use MicroKernelTrait;

    /**
     * @return array<Bundle>
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle()
        ];
    }

    public function getCacheDir(): string
    {
        return sprintf('%s/../var/cache/%s', __DIR__, $this->getEnvironment());
    }

    public function getLogDir(): string
    {
        return sprintf('%s/../var/log', __DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'secret' => 'fsi_component_translatable_secret'
        ]);

        $container->loadFromExtension('twig', [
            'paths' => [sprintf('%s/../templates', __DIR__)]
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'user' => 'admin',
                'charset' => 'UTF8',
                'path' => sprintf('%s/../var/data.sqlite', __DIR__)
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy' => 'doctrine.orm.naming_strategy.underscore',
                'auto_mapping' => true,
                'mappings' => [
                    'shared_kernel' => [
                        'type' => 'xml',
                        'dir' => sprintf('%s/Resources/config/doctrine', __DIR__),
                        'alias' => 'FSi',
                        'prefix' => 'Tests\FSi\App\Entity',
                        'is_bundle' => false
                    ]
                ]
            ]
        ]);
    }

    public function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
