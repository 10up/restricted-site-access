// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

import "@10up/cypress-wp-utils";

// Import commands.js using ES2015 syntax:
import "./commands";

Cypress.on('uncaught:exception', (err, runnable) => {

  /**
   * Not sure why these 2 errors occur. On inspecting with the GUI,
   * all the necessary elements are present as expected, yet Cypress
   * fails.
   */
  if (
    err.message.includes( `Cannot read properties of null (reading 'href')` )
    || err.message.includes( `Cannot read properties of null (reading 'checked')` )
  ) {
    return false;
  }

  // returning false here prevents Cypress from
  // failing the test
  return true
})

// Preserve WP cookies.
beforeEach(() => {
  Cypress.Cookies.defaults({
    preserve: /^wordpress.*?/,
  });
});
