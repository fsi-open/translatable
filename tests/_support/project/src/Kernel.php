<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\FSi\App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouteCollectionBuilder;

use function sprintf;

use const PHP_VERSION_ID;

final class Kernel extends HttpKernel\Kernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return array<Bundle>
     */
    public function registerBundles(): iterable
    {
        $contents = require "{$this->getProjectDir()}/config/bundles.php";
        /** @var class-string<Bundle> $class */
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
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
        $configDirectory = $this->getProjectDir() . '/config';
        $container->addResource(new FileResource($configDirectory . '/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', PHP_VERSION_ID < 70400 || $this->debug);
        $container->setParameter('container.dumper.inline_factories', true);

        $loader->load($configDirectory . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{packages}/' . $this->environment . '/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($configDirectory . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');

        $loader->load($configDirectory . '/services.yaml');
    }

//    TODO Use this version after dropping Symfony 4.4
//    protected function configureRoutes(RoutingConfigurator $routes): void

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import("{$this->getProjectDir()}/config/routes.yaml");
    }
}
