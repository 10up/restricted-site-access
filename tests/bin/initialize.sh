#!/bin/bash
set -e

wp-env run tests-wordpress chmod -c ugo+w /var/www/html
wp-env run tests-cli wp rewrite structure '/%postname%/' --hard

# Convert tests env to multisite. wp-env has issues with multisite:true from scratch
# (database credentials get lost in tests-cli). Starting single-site then converting
# avoids this.
status=0
wp-env run tests-cli wp site list || status=$?
if [ $status -ne 0 ]; then
	wp-env run tests-cli wp core multisite-convert --title='RSA Multisite'
fi

wp-env run tests-cli wp post create --post_type=page --post_title='One' --post_status='publish'
wp-env run tests-cli wp post create --post_type=page --post_title='Two' --post_status='publish'
wp-env run tests-cli wp plugin activate rsa-seeder --network
