import { defineConfig, devices } from "@playwright/test";
import dotenv from "dotenv";

// ! IMPORTANT !
// ! REMEMBER TO DISABLE BACKOFFICE TOKEN BEFORE RUN TESTS WITH "SetEnv _TOKEN_ disabled" in .htaccess

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
dotenv.config();

export const storageStatePath = "storage-state/storageState.json";

/**
 * See https://playwright.dev/docs/test-configuration.
 */
export default defineConfig({
  testDir: "./e2e",
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: "html",
  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    // baseURL: 'http://127.0.0.1:3000',

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: "on-first-retry",
  },

  /* Configure projects for major browsers */
  projects: [
    {
      name: "setup",
      testMatch: "login.setup.ts",
    },
    {
      name: "chromium welcome",
      use: { ...devices["Desktop Chrome"], storageState: storageStatePath },
      dependencies: ["setup"],
      testMatch: /welcome.*/,
    },
    {
      name: "chromium onboarding 1",
      use: { ...devices["Desktop Chrome"], storageState: storageStatePath },
      dependencies: ["setup", "chromium welcome"],
      testMatch: "onboarding.part1.test.ts",
    },
    {
      name: "chromium onboarding 2",
      use: { ...devices["Desktop Chrome"], storageState: storageStatePath },
      dependencies: ["setup", "chromium welcome", "chromium onboarding 1"],
      testMatch: "onboarding.part2.test.ts",
    },
    {
      name: "chromium onboarding 3",
      use: { ...devices["Desktop Chrome"], storageState: storageStatePath },
      dependencies: [
        "setup",
        "chromium welcome",
        "chromium onboarding 1",
        "chromium onboarding 2",
      ],
      testMatch: "onboarding.part3.test.ts",
    },
    {
      name: "chromium dashboard",
      use: { ...devices["Desktop Chrome"], storageState: storageStatePath },
      dependencies: [
        "setup",
        "chromium welcome",
        "chromium onboarding 1",
        "chromium onboarding 2",
        "chromium onboarding 3",
      ],
      testMatch: /dashboard.*/,
    },
  ],

  /* Run your local dev server before starting the tests */
  // webServer: {
  //   command: 'npm run start',
  //   url: 'http://127.0.0.1:3000',
  //   reuseExistingServer: !process.env.CI,
  // },
});
