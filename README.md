# Grooming Chimps API

## Generate JWT pem keys
$ mkdir -p config/jwt
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

and set in .env the passphrase used in the previous commands
JWT_PASSPHRASE=

## Services

### Mailcatcher

Access the web interface on http://localhost:8095/

### API

Access the docs on http://localhost:8090/docs

### SQS

Access on http://localhost:9325/