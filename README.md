# Grooming Chimps API

## Generate JWT pem keys
$ mkdir -p config/jwt
$ openssl genrsa -out config/jwt/private.pem -aes256 4096
$ openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

and set in .env the passphrase used in the previous commands
JWT_PASSPHRASE=
