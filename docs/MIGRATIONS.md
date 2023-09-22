## Migrations
Unfortunately, [mongodb-migrations-bundle](https://github.com/doesntmattr/mongodb-migrations-bundle) does not currently support [Symfony 6](https://symfony.com/doc/6.0/index.html). This limitation existed at the time this bundle was created  

However, there are `UP` and `DOWN` for making it in a hand-made manner via [mongosh](https://www.mongodb.com/docs/mongodb-shell/)
#### UP
```javascript
use phone_verification;

db.session.createIndex({"id":1}, {unique:true, name:"id_unique_index"});
db.session.createIndex({"updated":1}, {expireAfterSeconds:300, name:"updated_expiration_index"});
db.session_counter.createIndex({"id":1}, {unique:true, name:"id_unique_index"});
db.session_counter.createIndex({"created":1}, {expireAfterSeconds:86400, name:"created_expiration_index"});
```
#### DOWN
```javascript
use phone_verification;

db.session.dropIndex("id_unique_index");
db.session.dropIndex("updated_expiration_index");
db.session_counter.dropIndex("id_unique_index");
db.session_counter.dropIndex("created_expiration_index");

db.session.drop();
db.session_counter.drop();
```

**Note:** MongoDB creates a collection implicitly when the collection is first referenced in a command  
**Note:** Collection names `session` and `session_counter` are defaults in the `storage.mongodb.settings` section of the configuration
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
**Note:** Values `300` and `86400` in the index options are defaults for the following `.env` vars
```dotenv
# .env
PHONE_VERIFICATION_RATE_LIMIT_COMPLETE_PERIOD_SECS=300
PHONE_VERIFICATION_RATE_LIMIT_INITIATE_PERIOD_SECS=86400
```