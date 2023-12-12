<?php

namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use AlexGeno\PhoneVerificationBundle\Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Load configuration and execute compiler passes.
 *
 * @see https://symfony.com/doc/6.0/components/dependency_injection/compilation.html#execute-code-during-compilation
 */
class AlexGenoPhoneVerificationExtension extends Extension implements CompilerPassInterface
{
    /**
     * @var array<mixed> ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    protected array $config;

    /**
     * Get the extension configuration as an array.
     *
     * @param array<mixed> $configs
     *
     * @return array<mixed> ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    protected function config(array $configs, ContainerBuilder $container): array
    {
        if (!isset($this->config)) {
            $configuration = $this->getConfiguration($configs, $container);
            $this->config = $this->processConfiguration($configuration, $configs);
        }

        return $this->config;
    }

    /**
     * Load the Sender into the container.
     *
     * @param array<mixed> $config ['transport' => string]
     */
    protected function loadSender(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('alex_geno_phone_verification.sender')
            ->addArgument(new Reference('notifier.channel.sms'))
            ->addArgument(new Reference('alex_geno_phone_verification.sender.notification'))
            ->addArgument(new Reference('alex_geno_phone_verification.sender.sms_recipient.empty'))
            ->addArgument($config['transport']);
    }

    /**
     * Process(Compiler Pass) the Manager factory into the container.
     *
     * @param array<mixed> $config ['otp' => [...], 'rate_limits' => [...]]
     */
    protected function processManagerFactory(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('alex_geno_phone_verification.manager.factory')
            ->addArgument(new Reference('alex_geno_phone_verification.storage'))
            ->addArgument(new Reference('translator'))
            ->addArgument($config);
    }

    /**
     * Process(Compiler Pass) the Redis storage into the container.
     *
     * @param array<mixed> $config ['connection' => string, 'settings' => ['prefix' => string, 'session_key' => string, 'session_counter_key' => string]]
     */
    protected function processRedisStorage(ContainerBuilder $container, array $config): void
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
     * Process(Compiler Pass) the Mongodb storage into the container.
     *
     * @param array<mixed> $config ['connection' => string, 'settings' => ['collection_session' => string, 'collection_session_counter' => string]]
     */
    protected function processMongodbStorage(ContainerBuilder $container, array $config): void
    {
        if (!$container->hasExtension('doctrine_mongodb')) {
            throw new Exception("doctrine/mongodb-odm-bundle must be installed to use 'mongodb' as a storage");
        }

        $connectionServiceId = 'doctrine_mongodb.odm.'.$config['connection'].'_connection';

        if (!$container->has($connectionServiceId)) {
            throw new Exception("No connection '{$config['connection']}' in the 'doctrine_mongodb' configuration.");
        }

        $doctrineMongoDbConfig = $container->resolveEnvPlaceholders(current($container->getExtensionConfig('doctrine_mongodb')), true);

        // Get DB name.
        $db = $doctrineMongoDbConfig['connections'][$config['connection']]['options']['db'] ??
                    ($doctrineMongoDbConfig['default_database'] ?? false);
        if (!$db) {
            throw new Exception("No database defined in the 'doctrine_mongodb' configuration.");
        }

        $config['settings']['db'] = $db;
        $container->getDefinition('alex_geno_phone_verification.storage')
                    ->setClass(\AlexGeno\PhoneVerification\Storage\MongoDb::class)
                    ->addArgument(new Reference($connectionServiceId))
                    ->addArgument($config['settings']);
    }

    /**
     * Process(Compiler Pass) the configuration into the container parameters.
     *
     * @param array<mixed> $config ['storage' => [...], 'sender' => [...], 'manager' => [...]]
     */
    protected function processParameters(ContainerBuilder $container, array $config, string $storageDriver): void
    {
        // Change the structure of the array so it's ready for the conversion
        $config['storage'] = ['driver' => $storageDriver] + $config['storage'][$storageDriver];
        unset($config['storage'][$storageDriver]);
        unset($config['enabled']);

        // Convert multidimensional array to dot notation keys and set the respective parameters
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

        // Existence of the 'sender' key has been checked in \DependencyInjection\Configuration.

        $this->loadSender($container, $config['sender']);
    }

    public function process(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $container->resolveEnvPlaceholders($this->config($configs, $container), true);

        // Existence of the 'storage.driver' key has been checked in \DependencyInjection\Configuration.
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
