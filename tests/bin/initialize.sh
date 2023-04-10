#!/bin/bash
npm run env run tests-wordpress "chmod -c ugo+w /var/www/html"

npm run env run tests-cli "wp core multisite-convert --title='RSA Multisite'"
npm run env run tests-cli "wp post create --post_type=page --post_title='One' --post_status='publish'"
npm run env run tests-cli "wp post create --post_type=page --post_title='Two' --post_status='publish'"
npm run env run tests-cli "wp plugin activate rsa-seeder --network"
npm run env run tests-cli wp rewrite structure '/%postname%/' --hard
