# Phone Verification via [Symfony Notifier SMS Channel](https://symfony.com/doc/current/notifier.html#sms-channel)

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
- Usage with [dependency injection](https://symfony.com/doc/current/service_container.html#injecting-services-config-into-a-service) and [console commands](https://symfony.com/doc/current/console.html)
- Out-of-the-box routes for quick start

## Requirements
- [Symfony 6.x](https://symfony.com/doc/6.0/index.html)
- Any of SMS services: [vonage](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/Vonage/README.md), [twilio](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/Twilio/README.md), [messagebird](https://github.com/symfony/symfony/blob/6.0/src/Symfony/Component/Notifier/Bridge/MessageBird/README.md)  and [many more ](https://github.com/symfony/symfony/tree/6.0/src/Symfony/Component/Notifier)
- Any of supported storages: [snc/redis-bundle](https://github.com/snc/SncRedisBundle), [doctrine/mongodb-odm-bundle](https://github.com/doctrine/DoctrineMongoDBBundle)
## Installation
```shell
composer require alexgeno/phone-verification-bundle snc/redis-bundle predis/predis symfony/vonage-notifier
```
**Note:** Redis as a storage and Vonage as an SMS service are defaults in the configuration 

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
curl -X POST localhost/phone-verification/initiate/+15417543010
{"ok":true,"message":"Sms has been sent. Check your Phone!"}
```
```shell
curl -X POST localhost/phone-verification/complete/+15417543010/1234
{"ok":true,"message":"The verification is done!"}
```
## Configuration
The bundle will be automatically enabled and configured by a [Flex](https://symfony.com/doc/current/quick_tour/flex_recipes.html#flex-recipes-and-aliases) recipe.
In case you don't use [Flex](https://symfony.com/doc/current/quick_tour/flex_recipes.html#flex-recipes-and-aliases), see [docs/CONFIGURATION.md](docs/CONFIGURATION.md) on how to manually do it

## Different storages and sms services
To switch between [available](#requirements) storages and SMS services, install a respective package and update the configuration

For example, to use **Mongodb** as a storage and **Twilio** as an SMS service:
```shell
composer require doctrine/mongodb-odm-bundle symfony/twilio-notifier
```
```yaml
alex_geno_phone_verification:
    storage:
        driver: mongodb
        redis:
            connection: default
        mongodb:
            connection: default
    sender:
        transport: twilio
# ...
```
If the available options are not sufficient, you can add a custom storage (implementing **\AlexGeno\PhoneVerification\Storage\I**) or/and a sender (implementing **\AlexGeno\PhoneVerification\Sender\I**), and 
[decorate](https://symfony.com/doc/current/service_container/service_decoration.html) the respective services (**alex_geno_phone_verification.sender**, **alex_geno_phone_verification.storage**) with them