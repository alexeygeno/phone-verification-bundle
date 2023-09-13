<?php

namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use AlexGeno\PhoneVerificationBundle\Exception;

class AlexGenoPhoneVerificationExtension extends Extension implements CompilerPassInterface
{
    private array $config;

    private function config(array $configs, ContainerBuilder $container){
        if(!isset($this->config)){
            $configuration = $this->getConfiguration($configs, $container);
            $this->config = $this->processConfiguration($configuration, $configs);
        }
        return $this->config;
    }

    private function loadSender(ContainerBuilder $container, array $config){
        $container->getDefinition('phone_verification.sender')
            ->addArgument(new Reference('notifier.channel.sms')) //TODO: check existence
            ->addArgument(new Reference('phone_verification.sender.notification'))
            ->addArgument(new Reference('phone_verification.sender.sms_recipient.empty'))
            ->addArgument($config['transport']);
    }

    private function processManagerFactory(ContainerBuilder $container, array $config){
        $container->getDefinition('phone_verification.manager.factory')
            ->addArgument(new Reference('phone_verification.storage'))
            ->addArgument(new Reference('translator'))
            ->addArgument($this->getAlias())
            ->addArgument($config);
    }

    private function processRedisStorage(ContainerBuilder $container, array $config){

        if(!$container->has('snc_redis.client.default_options')){
            throw new Exception("snc/redis-bundle must be installed to use 'redis' as a storage");
        }
        $connectionServiceId = 'snc_redis.'.$config['connection'];

        if(!$container->has($connectionServiceId)){
            throw new Exception("No connection '{$config['connection']}' in the 'snc_redis' configuration.");
        }

        $container->getDefinition('phone_verification.storage')
                   ->setClass(\AlexGeno\PhoneVerification\Storage\Redis::class)
                   ->addArgument(new Reference($connectionServiceId))
                   ->addArgument($config['settings']);
    }

    private function processMongodbStorage(ContainerBuilder $container, array $config){

        if(!$container->has('doctrine_mongodb')){
           throw new Exception("doctrine/mongodb-odm-bundle must be installed to use 'mongodb' as a storage");
        }

        $connectionServiceId = 'doctrine_mongodb.odm.'.$config['connection'].'_connection';

        if(!$container->has($connectionServiceId)){
            throw new Exception("No connection '{$config['connection']}' in the 'doctrine_mongodb' configuration.");
        }

        $doctrineMongoDbConfig = $container->resolveEnvPlaceholders(current($container->getExtensionConfig('doctrine_mongodb')), true);

        // getting db name
        $db = $doctrineMongoDbConfig['connections'][$config['connection']]['options']['db'] ??
                    ($doctrineMongoDbConfig['default_database'] ?? false);
        if(!$db){
            throw new Exception("No database in the 'doctrine_mongodb' configuration.");
        }

        $config['settings']['db'] = $db;
        $container->getDefinition('phone_verification.storage')
                    ->setClass(\AlexGeno\PhoneVerification\Storage\MongoDb::class)
                    ->addArgument(new Reference($connectionServiceId))
                    ->addArgument($config['settings']);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {

       $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
       $loader->load('services.php');

       $config = $this->config($configs, $container);

       $this->loadSender($container, $config['sender']);
    }

    public function process(ContainerBuilder $container){
        $configs = $container->getExtensionConfig($this->getAlias());

        $config = $container->resolveEnvPlaceholders($this->config($configs, $container), true);

        $this->processManagerFactory($container, $config['manager']);

        $storageDriver = $config['storage']['driver'];
        $processStorageMethodName = 'process'.ucfirst($storageDriver).'Storage';
        if(method_exists($this, $processStorageMethodName)){
            if(!isset($config['storage'][$storageDriver])){
                throw new Exception("The configuration {$this->getAlias()}.storage.$storageDriver is not defined");
            }
            $this->$processStorageMethodName($container, $config['storage'][$storageDriver]);
        }else{
            throw new Exception("Not supported storage driver '{$storageDriver}'. Check the configuration.");
        }
    }
}