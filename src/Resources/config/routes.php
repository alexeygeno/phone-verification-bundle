<?php
use AlexGeno\PhoneVerificationBundle\Controller\PhoneVerificationController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {

    $routes->add('phone_verification_initiate', '/phone-verification/initiate/{to}')
        ->controller([PhoneVerificationController::class, 'initiate'])
    ;

    $routes->add('phone_verification_complete', '/phone-verification/complete/{to}/{otp}')
        ->controller([PhoneVerificationController::class, 'complete'])
    ;
};