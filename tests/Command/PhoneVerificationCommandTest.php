<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Command;

use phpmock\phpunit\PHPMock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PhoneVerificationCommandsTest extends CommandTestCase
{
    use PHPMock;

    /**
     * Test verification process using commands.
     *
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

    /**
     * Test the failure of the 'initiate' command.
     */
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

    /**
     * Test the failure of the 'complete' command.
     */
    public function testCompleteFail(): void
    {
        $to = '+15417543010';
        $wrongOtp = 0;
        $commandTester = new CommandTester($this->application->find('phone-verification:complete'));

        $commandTester->execute(['--to' => $to, '--otp' => $wrongOtp]);
        $this->assertEquals(Command::FAILURE, $commandTester->getStatusCode());
    }
}
