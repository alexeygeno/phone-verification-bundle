<?php

use phpmock\phpunit\PHPMock;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PhoneVerificationCommandsTest extends KernelTestCase
{
    use PHPMock;

    protected Application $application;

    protected static function getKernelClass(): string
    {
        return \AlexGeno\PhoneVerificationBundle\Tests\Application\Kernel::class;
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->application = new Application(self::bootKernel());
        static::getContainer()->get('snc_redis.mock.default')->flushdb();
    }

    /**
     * @runInSeparateProcess
     *
     * @see https://github.com/php-mock/php-mock-phpunit#restrictions
     */
    public function testProcessOk(): void
    {
        $otp = 1234;
        $to = '+15417543010';

        // Mock otp
        $rand = $this->getFunctionMock('AlexGeno\PhoneVerification', 'rand');
        $rand->expects($this->once())->willReturn($otp);

        // Initiation
        $commandInitiateTester = new CommandTester($this->application->find('phone-verification:initiate'));
        $commandInitiateTester->execute(['--to' => $to]);
        $commandInitiateTester->assertCommandIsSuccessful();

        // Completion
        $commandCompleteTester = new CommandTester($this->application->find('phone-verification:complete'));
        $commandCompleteTester->execute(['--to' => $to, '--otp' => $otp]);
        $commandCompleteTester->assertCommandIsSuccessful();
    }

    public function testInitiateFail(): void
    {
        $to = '+15417543010';

        $count = static::getContainer()->getParameter('alex_geno_phone_verification.manager.rate_limits.initiate.count');
        $commandTester = new CommandTester($this->application->find('phone-verification:initiate'));

        foreach (range(0, $count) as $iteration) {
            $commandTester->execute(['--to' => $to]);
            if ($iteration < $count) {
                $commandTester->assertCommandIsSuccessful();
            } else { // limit exceeded
                $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
            }
        }
    }

    public function testCompleteFail(): void
    {
        $to = '+15417543010';
        $wrongOtp = 0;
        $commandTester = new CommandTester($this->application->find('phone-verification:complete'));

        $commandTester->execute(['--to' => $to, '--otp' => $wrongOtp]);
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }
}
