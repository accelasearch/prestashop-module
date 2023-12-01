import { test, expect } from "@playwright/test";
import {
  createExpiredLock,
  finishOnBoarding,
  goToModuleConfigurationPage,
} from "./utils";

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

  test("Test that notice appear after lock expired and it works", async ({
    page,
  }) => {
    // create lock and refresh
    const new_page = await page.context().newPage();
    await createExpiredLock(new_page);
    await page.reload();

    // check that notice is visible
    await page.waitForSelector(".text-orange-700");

    // click on unlock button
    await page.click("button.bg-orange-600");

    // wait for loading
    await expect(
      page.getByText("Unlocking and sending report...")
    ).toBeVisible();

    // wait for success
    await expect(
      page.getByText("Cronjob unlocked and report sent successfully.")
    ).toBeVisible({ timeout: 20000 });

    // check that notice is not visible anymore
    await expect(page.locator(".text-orange-700")).not.toBeVisible();
  });
});
