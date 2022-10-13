#!/bin/bash
npm run env run tests-wordpress "chmod -c ugo+w /var/www/html"
npm run env run tests-cli wp rewrite structure '/%postname%/' --hard

npm run env run tests-cli "wp core multisite-convert --title='RSA Multisite'"
npm run env run tests-cli "wp post create --post_type=page --post_title='Accessible page' --post_status='publish'"
npm run env run tests-cli "wp post create --post_type=page --post_title='Page to redirect' --post_status='publish'"
npm run env run tests-cli "wp rewrite flush"