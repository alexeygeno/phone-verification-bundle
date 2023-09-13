<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AlexGeno\PhoneVerification\Manager;
use AlexGeno\PhoneVerificationBundle\Command\PhoneVerificationInitiateCommand;
use AlexGeno\PhoneVerificationBundle\Command\PhoneVerificationCompleteCommand;
use AlexGeno\PhoneVerificationBundle\Controller\PhoneVerificationController;
use AlexGeno\PhoneVerificationBundle\ManagerFactory;
use AlexGeno\PhoneVerificationBundle\Sender\SmsRecipient;
use AlexGeno\PhoneVerification\Manager\Initiator;
use AlexGeno\PhoneVerification\Manager\Completer;
use AlexGeno\PhoneVerificationBundle\Sender;
use AlexGeno\PhoneVerification\Sender\I as ISender;
use AlexGeno\PhoneVerification\Storage\I as IStorage;
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
             ->args([service('phone_verification.manager.initiator'),
                    service('translator')])
             ->tag('console.command');

    $services->set(PhoneVerificationCompleteCommand::class)
             ->args([service('phone_verification.manager.completer'),
                    service('translator')])
              ->tag('console.command');

    $services
        ->set('phone_verification.manager.factory', ManagerFactory::class);

    $services
        ->set('phone_verification.manager.initiator', Manager::class)
        ->factory([service('phone_verification.manager.factory'), 'initiator'])
        ->args([service('phone_verification.sender')])
        ->alias(Initiator::class, 'phone_verification.manager.initiator')
    ;

    $services
        ->set('phone_verification.manager.completer', Manager::class)
        ->factory([service('phone_verification.manager.factory'), 'completer'])
        ->alias(Completer::class, 'phone_verification.manager.completer');

    $services
        ->set('phone_verification.sender', Sender::class)
        ->alias(ISender::class, 'phone_verification.sender');

    $services
        ->set('phone_verification.sender.notification', Notification::class);

    $services
        ->set('phone_verification.sender.sms_recipient.empty', SmsRecipient::class);

    $services
        ->set('phone_verification.storage')
        ->alias(IStorage::class, 'phone_verification.storage');
};
