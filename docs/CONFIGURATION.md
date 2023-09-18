## Configuration
#### Add  **AlexGenoPhoneVerificationBundle** to `config/bundles.php`
```php
return [
    // ...
    AlexGeno\PhoneVerificationBundle\AlexGenoPhoneVerificationBundle::class => ['all' => true],
    // ...
];
```
#### Create the package configuration `config/packages/alex_geno_phone_verification.yaml`
```yaml
alex_geno_phone_verification:
    storage:
        driver: redis # redis || mongodb
        redis:
            connection: default
            settings: # the key settings - normally you don't need to change them
                prefix: pv:1
                session_key: session
                session_counter_key: session_counter
        mongodb:
            connection: default
            settings: # the collection settings - normally you don't need to change them
                collection_session: session
                collection_session_counter: session_counter
    sender:
        transport: vonage # vonage || twilio || messagebird and many more https://symfony.com/doc/current/notifier.html#sms-channel
    manager:
        otp:
            length: '%env(int:PHONE_VERIFICATION_OTP_LENGTH)%'
        rate_limits:
            initiate: #for every 'to' no more than 'count' initiations over 'period_secs' seconds
                period_secs: '%env(int:PHONE_VERIFICATION_RATE_LIMIT_INITIATE_PERIOD_SECS)%'
                count: '%env(int:PHONE_VERIFICATION_RATE_LIMIT_INITIATE_COUNT)%'
            complete: #for every 'to' no more than 'count' failed completions over 'period_secs' seconds
                period_secs: '%env(int:PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_PERIOD_SECS)%' # this is also the expiration period for OTP
                count: '%env(int:PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_COUNT)%'
```
#### Add the respective vars to `.env`
```dotenv
###> alexgeno/phone-verification-bundle ###
# 1000..9999
PHONE_VERIFICATION_OTP_LENGTH=4
# for every 'to' no more than 10 initiations over 24 hours
PHONE_VERIFICATION_RATE_LIMIT_INITIATE_PERIOD_SECS=86400
PHONE_VERIFICATION_RATE_LIMIT_INITIATE_COUNT=10
# for every 'to' no more than 5 failed completions over 5 minutes
PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_PERIOD_SECS=300 # this is also the expiration period for OTP
PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_COUNT=5
###< alexgeno/phone-verification-bundle ###
```

#### If you use *redis* as a storage the respective connection must be defined in `config/packages/snc_redis.yaml`
```yaml
snc_redis:
# ...
    clients:
        default:
            type: predis
            alias: default
            dsn: '%env(REDIS_URL)%'
# ...

```
#### if you use *mongodb* as a storage the respective connection must be defined in `config/packages/doctrine_mongodb.yaml`
```yaml

doctrine_mongodb:
# ...
  connections:
        default:
            server: '%env(resolve:MONGODB_URL)%'
            options: {
                db: phone_verification
            }
    default_database: '%env(resolve:MONGODB_DB)%' # default db name for all connections
# ...
```
**Note:** If **options.db** in the connection does not exist, database name is considered as **connections.default_database**  
**Note:** If both **options.db** and **connections.default_database** don't exist, an exception will be thrown

####  Create the package routes `config/routes/alex_geno_phone_verification.yaml`
```yaml
phone_verification_initiate:
        path: /phone-verification/initiate/{to}
        controller: AlexGeno\PhoneVerificationBundle\Controller\PhoneVerificationController::initiate
        methods: POST
phone_verification_complete:
        path: /phone-verification/complete/{to}/{otp}
        controller: AlexGeno\PhoneVerificationBundle\Controller\PhoneVerificationController::complete
        methods: POST
```