<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Command;

use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class CommandTestCase extends KernelTestCase
{
    use TranslatorTrait;

    protected Application $application;

    protected static function getKernelClass(): string
    {
        return \AlexGeno\PhoneVerificationBundle\Tests\TestApplication\Kernel::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
        $this->application = new Application(self::bootKernel());
    }

    public function tearDown(): void
    {
        static::getContainer()->get(\Predis\Client::class)->flushdb();
        parent::tearDown();
    }
}
