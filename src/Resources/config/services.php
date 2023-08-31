<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use AlexGeno\PhoneVerification\Manager;
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

    //TODO: no autowire or autoconfigure in a bundle
    $services->set(PhoneVerificationController::class)->autowire()->autoconfigure();

    $services
        ->set('phone_verification.manager.factory', \AlexGeno\PhoneVerificationBundle\ManagerFactory::class);

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
        ->set('phone_verification.sender.sms_recipient', SmsRecipient::class);

    $services
        ->set('phone_verification.storage')
        ->alias(IStorage::class, 'phone_verification.storage');
};
