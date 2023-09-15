<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AlexGeno\PhoneVerification\Manager;
use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerification\Sender\I as ISender;
use AlexGeno\PhoneVerification\Storage\I as IStorage;
use AlexGeno\PhoneVerificationBundle\Command\PhoneVerificationCompleteCommand;
use AlexGeno\PhoneVerificationBundle\Command\PhoneVerificationInitiateCommand;
use AlexGeno\PhoneVerificationBundle\Controller\PhoneVerificationController;
use AlexGeno\PhoneVerificationBundle\Factory\ManagerFactory;
use AlexGeno\PhoneVerificationBundle\Notifier\Sender;
use AlexGeno\PhoneVerificationBundle\Notifier\SmsRecipient;
use Symfony\Component\Notifier\Notification\Notification;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // @see https://symfony.com/doc/current/bundles/best_practices.html#services
    // Services should not use autowiring or autoconfiguration. Instead, all services should be defined explicitly.
    $services->set(PhoneVerificationController::class)
       ->args([service('translator')])
       ->call('setContainer', [service('service_container')])
       ->tag('controller.service_arguments');

    $services->set(PhoneVerificationInitiateCommand::class)
             ->args([service('alex_geno_phone_verification.manager.initiator'),
                    service('translator')])
             ->tag('console.command');

    $services->set(PhoneVerificationCompleteCommand::class)
             ->args([service('alex_geno_phone_verification.manager.completer'),
                    service('translator')])
              ->tag('console.command');

    $services
        ->set('alex_geno_phone_verification.manager.factory', ManagerFactory::class);

    $services
        ->set('alex_geno_phone_verification.manager.initiator', Manager::class)
        ->factory([service('alex_geno_phone_verification.manager.factory'), 'initiator'])
        ->args([service('alex_geno_phone_verification.sender')])
        ->alias(Initiator::class, 'alex_geno_phone_verification.manager.initiator')
    ;

    $services
        ->set('alex_geno_phone_verification.manager.completer', Manager::class)
        ->factory([service('alex_geno_phone_verification.manager.factory'), 'completer'])
        ->alias(Completer::class, 'alex_geno_phone_verification.manager.completer');

    $services
        ->set('alex_geno_phone_verification.sender', Sender::class)
        ->alias(ISender::class, 'alex_geno_phone_verification.sender');

    $services
        ->set('alex_geno_phone_verification.sender.notification', Notification::class);

    $services
        ->set('alex_geno_phone_verification.sender.sms_recipient.empty', SmsRecipient::class);

    $services
        ->set('alex_geno_phone_verification.storage')
        ->alias(IStorage::class, 'alex_geno_phone_verification.storage');
};
