## Migrations
Unfortunately [mongodb-migrations-bundle](https://github.com/doesntmattr/mongodb-migrations-bundle) doesn't support [Symfony 6](https://symfony.com/doc/6.0/index.html) by the moment this bundle had been created.
However, there are `UP` and `DOWN` for making it in a hand-made manner via [mongosh](https://www.mongodb.com/docs/mongodb-shell/)
#### UP
```javascript
db.phone_verification.session.createIndex({"id":1}, {unique:true, name:"id_unique_index"});
db.phone_verification.session.createIndex({"updated":1}, {expireAfterSeconds:300, name:"updated_expiration_index"});
db.phone_verification.session_counter.createIndex({"id":1}, {unique:true, name:"id_unique_index"});
db.phone_verification.session_counter.createIndex({"created":1}, {expireAfterSeconds:86400, name:"created_expiration_index"});
```
#### DOWN
```javascript
db.phone_verification.session.dropIndex("id_unique_index");
db.phone_verification.session.dropIndex("updated_expiration_index");
```
**Note:** Because MongoDB creates a collection implicitly when the collection is first referenced in a command, it's enough to take care only about indexes  
**Note:** Collection names `session` and `session_counter` are what the configuration has by default at the `storage.mongodb.settings`  
```yaml
# config/packages/alex_geno_phone_verification.yaml
alex_geno_phone_verification:
    storage:
        driver: mongodb # redis || mongodb
        mongodb:
            connection: default
        settings:
          collection_session: session
          collection_session_counter: session_counter
# ...
```
**Note:** Values `300` and `86400` in index options are what the configuration has by default in the following `.env` vars
```dotenv
PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_PERIOD_SECS=300
PHONE_VERIFICATION_RATE_LIMIT_INITIATE_PERIOD_SECS=86400
```