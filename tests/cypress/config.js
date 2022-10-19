const { defineConfig } = require('cypress');
const { readConfig }   = require('@wordpress/env/lib/config');

module.exports = defineConfig({
  fixturesFolder: 'tests/cypress/fixtures',
  screenshotsFolder: 'tests/cypress/screenshots',
  videosFolder: 'tests/cypress/videos',
  downloadsFolder: 'tests/cypress/downloads',
  video: false,
  e2e: {
    setupNodeEvents(on, config) {
      return setBaseUrl(on, config);
    },
    specPattern: [
      "tests/cypress/e2e/activation-deactivation.test.js",
      "tests/cypress/e2e/add-valid-addresses.test.js",
      "tests/cypress/e2e/add-invalid-addresses.test.js",
      "tests/cypress/e2e/restrict-users.test.js",
      "tests/cypress/e2e/allow-unrestricted-users.test.js",
    ],
    supportFile: 'tests/cypress/support/e2e.js'
  },
});

/**
 * Set WP URL as baseUrl in Cypress config.
 * 
 * @param {Function} on    function that used to register listeners on various events.
 * @param {object} config  Cypress Config object.
 * @returns config Updated Cypress Config object.
 */
const setBaseUrl = async (on, config) => {
  const wpEnvConfig = await readConfig('wp-env');

  if (wpEnvConfig) {
    const port = wpEnvConfig.env.tests.port || null;

    if (port) {
      config.baseUrl = `http://${wpEnvConfig.env.tests.config.WP_TESTS_DOMAIN}:${port}`;
    }
  }

  return config;
};
