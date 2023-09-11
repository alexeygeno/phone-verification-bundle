<?php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes): void {
    $routes->import('@AlexGenoPhoneVerificationBundle/Resources/config/routes.php');
};