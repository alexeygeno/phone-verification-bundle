<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Application;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class ApplicationTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected static function getKernelClass():string{
        return \AlexGeno\PhoneVerificationBundle\Tests\Application\Kernel::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        static::getContainer()->get('snc_redis.default')->flushdb();

    }

    protected function trans(string $id, array $parameters = [], string $domain = 'alex_geno_phone_verification', string $locale = null){
        return static::getContainer()->get(TranslatorInterface::class)->trans($id, $parameters, $domain, $locale);
    }

    protected function assertResponseJson(array $json){
        $content = $this->client->getResponse()->getContent();
        $this->assertJson($content);
        $this->assertEquals($content, json_encode($json));
    }

}
