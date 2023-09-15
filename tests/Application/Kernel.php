<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \AlexGeno\PhoneVerificationBundle\AlexGenoPhoneVerificationBundle(),
            new \Snc\RedisBundle\SncRedisBundle(),
            new \Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
