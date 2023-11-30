import { test, expect } from "@playwright/test";
import { finishOnBoarding, goToModuleConfigurationPage } from "./utils";

test.describe("Test Dashboard page", () => {
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await finishOnBoarding(page);
  });

  test.beforeEach(async ({ page }) => {
    await goToModuleConfigurationPage(page);
  });

  test("Test that disconnect button exists", async ({ page }) => {
    await page.waitForSelector('button:has-text("Disconnetti")');
  });
});
