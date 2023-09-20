<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Controller;

use AlexGeno\PhoneVerificationBundle\TranslatorTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ControllerTestCase extends WebTestCase
{
    use TranslatorTrait;

    protected KernelBrowser $client;

    protected static function getKernelClass(): string
    {
        return \AlexGeno\PhoneVerificationBundle\Tests\TestApplication\Kernel::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->translator = static::getContainer()->get(TranslatorInterface::class);
    }

    public function tearDown(): void
    {
        static::getContainer()->get(\Predis\Client::class)->flushdb();
        parent::tearDown();
    }

    /**
     * Asserts that the client response is JSON and equals the given array.
     *
     * @param array<mixed> $actual
     */
    protected function assertResponseJson(array $actual): void
    {
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        $this->assertEquals($content, json_encode($actual));
    }
}
