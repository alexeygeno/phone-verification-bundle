# Phone Verification via [Symfony Notification SMS Channel](https://symfony.com/doc/current/notifier.html#sms-channel)

Signing in or signing up on a modern website or mobile app typically follows these steps:
- A user initiates verification by submitting a phone number
- The user receives an SMS or a call with a one-time password [(OTP)](https://en.wikipedia.org/wiki/One-time_password)
- The user completes verification by submitting the [OTP](https://en.wikipedia.org/wiki/One-time_password)

This library is built on top of [ alexeygeno/phone-verification-php ](https://github.com/alexeygeno/phone-verification-php) and allows to set this up

## Supported features
- [Easy](#different-storages-and-sms-services) switching between different storages and sms services
- Configurable length and expiration time for [OTP](https://en.wikipedia.org/wiki/One-time_password)
- Configurable rate limits
- Localization
- Usage with dependency injection [dependency injection](https://symfony.com/doc/current/service_container.html#injecting-services-config-into-a-service)
- Usage with [console commands](https://symfony.com/doc/current/console.html)
- Out-of-the-box routes for quick start

## Requirements
- [Symfony 6.x](https://symfony.com/doc/6.0/index.html)
- Any of SMS channel service: [vonage](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/Vonage/README.md), [twilio](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/Twilio/README.md), [messagebird](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/MessageBird/README.md)  and [many more ](https://github.com/symfony/symfony/tree/6.0/src/Symfony/Component/Notifier)
- Any of supported storages: [snc/redis-bundle](https://github.com/snc/SncRedisBundle), [doctrine/mongodb-odm-bundle](https://github.com/doctrine/DoctrineMongoDBBundle)
## Installation
```shell
composer require alexgeno/phone-verification-bundle snc/redis-bundle predis/predis symfony/vonage-notifier
```
**Note:** Redis as a storage and Vonage as a notification channel are defaults in the configuration 

## Usage
#### Dependency injection
```php
public function initiate(\AlexGeno\PhoneVerification\Manager\Initiator $manager)
{
    $manager->initiate('+15417543010');
}
```
```php
public function complete(\AlexGeno\PhoneVerification\Manager\Completer $manager)
{
    $manager->complete('+15417543010', 1234);
}
```
#### Console commands
```shell
bin/console phone-verification:initiate --to=+15417543010
```
```shell
bin/console phone-verification:complete --to=+15417543010 --otp=1234
```
#### Routes
```shell
curl localhost/phone-verification/initiate/+15417543010
{"ok":true,"message":"Sms has been sent. Check your Phone!"}
```
```shell
curl -d "to=+15417543010&otp=1234" localhost/phone-verification/complete/+15417543010/1234
{"ok":true,"message":"The verification is done!"}