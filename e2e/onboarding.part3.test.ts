import { test, expect } from "@playwright/test";
import {
  goToModuleConfigurationPage,
  setOnBoarding,
  finishOnBoarding,
} from "./utils";

test.describe("Test Onboarding step 3", () => {
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setOnBoarding(page, 2);
  });

  test.beforeEach(async ({ page }) => {
    await goToModuleConfigurationPage(page);
  });

  test("Test that cronjob command is not empty", async ({ page }) => {
    await page.waitForSelector("#cronjob_command");
    const cronjob_command = await page.$("#cronjob_command");
    expect(await cronjob_command?.getAttribute("value")).not.toBe("");
  });

  test("Test that cronjob execution automatically reloads the page", async ({
    page,
  }) => {
    const new_page = await page.context().newPage();
    await finishOnBoarding(new_page);
    await page.waitForSelector('button:has-text("Disconnetti")');
  });
});
