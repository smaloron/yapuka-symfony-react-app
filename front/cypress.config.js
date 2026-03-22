const { defineConfig } = require('cypress');

module.exports = defineConfig({
    e2e: {
        baseUrl: 'http://localhost:8080',
        viewportWidth: 800,
        viewportHeight: 600,
        video: false,
        defaultCommandTimeout: 10000,
        specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
        supportFile: false,

        env: {
            apiUrl: 'http://nginx:8080',
            appName: 'Yapuka',
        }

    },
});