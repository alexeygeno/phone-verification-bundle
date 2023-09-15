<?php

namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use AlexGeno\PhoneVerificationBundle\Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AlexGenoPhoneVerificationExtension extends Extension implements CompilerPassInterface
{
    /**
     * @var array<mixed> ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    private array $config;

    /**
     * @param array<mixed> $configs
     *
     * @return array<mixed> ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    private function config(array $configs, ContainerBuilder $container): array
    {
        if (!isset($this->config)) {
            $configuration = $this->getConfiguration($configs, $container);
            $this->config = $this->processConfiguration($configuration, $configs);
        }

        return $this->config;
    }

    /**
     * @param array<mixed> $config ['transport' => string]
     */
    private function loadSender(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('alex_geno_phone_verification.sender')
            ->addArgument(new Reference('notifier.channel.sms'))
            ->addArgument(new Reference('alex_geno_phone_verification.sender.notification'))
            ->addArgument(new Reference('alex_geno_phone_verification.sender.sms_recipient.empty'))
            ->addArgument($config['transport']);
    }

    /**
     * @param array<mixed> $config ['otp' => [...], 'rate_limits' => [...]]
     */
    private function processManagerFactory(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('alex_geno_phone_verification.manager.factory')
            ->addArgument(new Reference('alex_geno_phone_verification.storage'))
            ->addArgument(new Reference('translator'))
            ->addArgument($this->getAlias())
            ->addArgument($config);
    }

    /**
     * @param array<mixed> $config ['connection' => string, 'settings' => ['prefix' => string, 'session_key' => string, 'session_counter_key' => string]]
     */
    private function processRedisStorage(ContainerBuilder $container, array $config): void
    {
        if (!$container->hasExtension('snc_redis')) {
            throw new Exception("snc/redis-bundle must be installed to use 'redis' as a storage");
        }
        $connectionServiceId = 'snc_redis.'.$config['connection'];

        if (!$container->has($connectionServiceId)) {
            throw new Exception("No connection '{$config['connection']}' in the 'snc_redis' configuration.");
        }

        $container->getDefinition('alex_geno_phone_verification.storage')
                   ->setClass(\AlexGeno\PhoneVerification\Storage\Redis::class)
                   ->addArgument(new Reference($connectionServiceId))
                   ->addArgument($config['settings']);
    }

    /**
     * @param array<mixed> $config ['connection' => string, 'settings' => ['collection_session' => string, 'collection_session_counter' => string]]
     */
    private function processMongodbStorage(ContainerBuilder $container, array $config): void
    {
        if (!$container->hasExtension('doctrine_mongodb')) {
            throw new Exception("doctrine/mongodb-odm-bundle must be installed to use 'mongodb' as a storage");
        }

        $connectionServiceId = 'doctrine_mongodb.odm.'.$config['connection'].'_connection';

        if (!$container->has($connectionServiceId)) {
            throw new Exception("No connection '{$config['connection']}' in the 'doctrine_mongodb' configuration.");
        }

        $doctrineMongoDbConfig = $container->resolveEnvPlaceholders(current($container->getExtensionConfig('doctrine_mongodb')), true);

        // getting db name
        $db = $doctrineMongoDbConfig['connections'][$config['connection']]['options']['db'] ??
                    ($doctrineMongoDbConfig['default_database'] ?? false);
        if (!$db) {
            throw new Exception("No database in the 'doctrine_mongodb' configuration.");
        }

        $config['settings']['db'] = $db;
        $container->getDefinition('alex_geno_phone_verification.storage')
                    ->setClass(\AlexGeno\PhoneVerification\Storage\MongoDb::class)
                    ->addArgument(new Reference($connectionServiceId))
                    ->addArgument($config['settings']);
    }

    /**
     * @param array<mixed> $config ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    private function processParameters(ContainerBuilder $container, array $config, string $storageDriver): void
    {
        // Change the structure of the array so it's ready for the conversion
        $config['storage'] = ['driver' => $storageDriver] + $config['storage'][$storageDriver];
        unset($config['storage'][$storageDriver]);
        unset($config['enabled']);

        // Convert multidimensional array to 2D dot notation keys and set respective parameters
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($config));
        $prefix = $this->getAlias();
        foreach ($iterator as $leafValue) {
            $keys = [$prefix];
            foreach (range(0, $iterator->getDepth()) as $depth) {
                $keys[] = $iterator->getSubIterator($depth)->key();
            }
            $key = join('.', $keys);
            $container->setParameter($key, $leafValue);
        }
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $config = $this->config($configs, $container);

        $this->loadSender($container, $config['sender']);
    }

    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $container->resolveEnvPlaceholders($this->config($configs, $container), true);

        // Existence has been checked in \DependencyInjection\Configuration
        $storageDriver = $config['storage']['driver'];

        if (!isset($config['storage'][$storageDriver])) {
            throw new Exception("The configuration {$this->getAlias()}.storage.$storageDriver is not defined");
        }
        $processStorageMethodName = 'process'.ucfirst($storageDriver).'Storage';
        if (method_exists($this, $processStorageMethodName)) {
            $this->$processStorageMethodName($container, $config['storage'][$storageDriver]);
        } else {
            throw new Exception("Not supported storage driver '{$storageDriver}'. Check the configuration.");
        }

        $this->processManagerFactory($container, $config['manager']);
        $this->processParameters($container, $config, $storageDriver);
    }
}
