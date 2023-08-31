<?php

namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;

class AlexGenoPhoneVerificationExtension extends Extension implements CompilerPassInterface
{

    private function loadManagerFactory(ContainerBuilder $container, array $config){
        $container->getDefinition('phone_verification.manager.factory')
            ->addArgument(new Reference('phone_verification.storage'))
            ->addArgument(new Reference('translator'))
            ->addArgument($this->getAlias())
            ->addArgument($config);
    }

    private function loadRedisStorage(ContainerBuilder $container, array $config){
        $container->getDefinition('phone_verification.storage')
                   ->setClass(\AlexGeno\PhoneVerification\Storage\Redis::class)
                   ->addArgument(new Reference('snc_redis.'.$config['connection']))
                   ->addArgument($config['settings']);
    }

    private function loadSender(ContainerBuilder $container, array $config){
        $container->getDefinition('phone_verification.sender')
                  ->addArgument(new Reference('notifier.channel.sms')) //TODO: check existence
                  ->addArgument(new Reference('phone_verification.sender.notification')) //check existence
                  ->addArgument(new Reference('phone_verification.sender.sms_recipient'))
                  ->addArgument($config['transport']);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $processedConfig = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $config = $container->resolveEnvPlaceholders($processedConfig, true);


        $storageDriver = $config['storage']['driver'];
       $loadStorageMethodName = 'load'.ucfirst($storageDriver).'Storage';

       if(method_exists($this, $loadStorageMethodName)){
           $this->$loadStorageMethodName($container, $config['storage'][$storageDriver]);
       }

       $this->loadSender($container, $config['sender']);
       $this->loadManagerFactory($container, $config['manager']);

    }

    public function process(ContainerBuilder $container){
    }
}