<?php

namespace AlexGeno\PhoneVerificationBundle\Tests\Application;

use phpmock\phpunit\PHPMock;

class RoutesTest extends ApplicationTestCase
{
    use PHPMock;

    /**
     * Test the 'phone-verification/initiate' route.
     *
     * @return void
     */
    public function test_initiation_ok(): void
    {

        $this->client->request('GET', '/phone-verification/initiate/+380935258272');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $this->assertResponseJson(['ok' => true, 'message' => $this->trans('initiation_success')]);
    }

    /**
     * Test verification process using the routes when OTP is expired.
     *
     * @return void
     */
    public function test_completion_otp_expired()
    {
        // No initiation has the same behavior as the initiation expiration - the key just doesn't exist in a storage
        $expirationPeriodSecs = static::getContainer()->getParameter('alex_geno_phone_verification.rate_limit.complete.period_secs');

        $otp = 0;
        $to = '+15417543010';

        $this->client->request('GET', "/phone-verification/complete/$to/$otp");
        $this->assertResponseStatusCodeSame(406);
        $this->assertResponseJson(['ok' => false, 'message' => $this->trans('expired', ['%minutes%' => $expirationPeriodSecs/60])]);
    }

    /**
     * Test verification process using the routes when OTP is incorrect.
     *
     * @return void
     */
    public function test_completion_otp_incorrect()
    {
        $otp = 0;
        $to = '+15417543010';

        $this->client->request('GET', "/phone-verification/initiate/$to");
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseJson(['ok' => true, 'message' => $this->trans('initiation_success')]);


        $this->client->request('GET', "/phone-verification/complete/$to/$otp");
        $this->assertResponseStatusCodeSame(406);
        $this->assertResponseJson(['ok' => false, 'message' => $this->trans('incorrect')]);

    }


    /**
     * Test verification process using the routes.
     *
     * @runInSeparateProcess
     *
     * @see https://github.com/php-mock/php-mock-phpunit#restrictions
     *
     * @return void
     */
    public function test_process_ok()
    {
        $otp = 1234;
        $to = '+15417543010';

        $rand = $this->getFunctionMock('AlexGeno\PhoneVerification', 'rand');
        $rand->expects($this->once())->willReturn($otp);

        $this->client->request('GET', "/phone-verification/initiate/$to");
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseJson(['ok' => true, 'message' => $this->trans('initiation_success')]);

        $this->client->request('GET', "/phone-verification/complete/$to/$otp");
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseJson(['ok' => true, 'message' => $this->trans('completion_success')]);
    }

    /**
     * Test the initiation rate limit.
     *
     * @return void
     */
    public function test_initiation_rate_limit_exceeded()
    {
        $to = '+15417543010';

        $count = static::getContainer()->getParameter('alex_geno_phone_verification.rate_limit.initiate.count');
        $periodSecs = static::getContainer()->getParameter('alex_geno_phone_verification.rate_limit.initiate.period_secs');

        foreach (range(0, $count) as $iteration){
            $this->client->request('GET', "/phone-verification/initiate/$to");
            if($iteration<$count) {
                $this->assertResponseIsSuccessful();
                $this->assertResponseStatusCodeSame(200);
                $this->assertResponseJson(['ok' => true, 'message' => $this->trans('initiation_success')]);
            }else{ // limit exceeded
                $this->assertResponseStatusCodeSame(406);
                $this->assertResponseJson(['ok' => false, 'message' => $this->trans('initiation_rate_limit', ['%sms%' => $count, '%hours%' => $periodSecs/60/60])]);
            }
        }
    }

    /**
     * Test the completion rate limit.
     *
     * @return void
     */
    public function test_process_rate_limit_exceeded()
    {
        $wrongOtp = 0;
        $to = '+15417543010';

        $count = static::getContainer()->getParameter('alex_geno_phone_verification.rate_limit.complete.count');
        $periodSecs = static::getContainer()->getParameter('alex_geno_phone_verification.rate_limit.complete.period_secs');

        $this->client->request('GET', "/phone-verification/initiate/$to");
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseJson(['ok' => true, 'message' => $this->trans('initiation_success')]);

        foreach (range(0, $count) as $iteration) {
            $this->client->request('GET', "/phone-verification/complete/$to/$wrongOtp");
            $this->assertResponseStatusCodeSame(406);
            if($iteration<$count) {
                $this->assertResponseJson(['ok' => false, 'message' => $this->trans('incorrect')]);
            }else{ // limit exceeded
                dump($iteration);
                $this->assertResponseJson(['ok' => false, 'message' => $this->trans('completion_rate_limit', ['%times%' => $count, '%minutes%' => $periodSecs/60])]);
            }
        }

    }

}
