<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    // mocking redis
    $services
        ->set('snc_redis.mock.factory', \M6Web\Component\RedisMock\RedisMockFactory::class);

    $services
        ->set('snc_redis.mock.default', \M6Web\Component\RedisMock\RedisMock::class)
        ->decorate('snc_redis.default')
        ->factory([service('snc_redis.mock.factory'), 'getAdapter'])
        ->args([\Predis\Client::class]);
};
