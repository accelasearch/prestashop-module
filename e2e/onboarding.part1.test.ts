import { test, expect } from "@playwright/test";
import { goToModuleConfigurationPage, setOnBoarding } from "./utils";

test.describe("Test Onboarding step 1", () => {
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setOnBoarding(page, 0);
  });

  test.beforeEach(async ({ page }) => {
    await goToModuleConfigurationPage(page);
  });

  test("Test that at least 1 shop is rendered", async ({ page }) => {
    await page.waitForSelector("#shop-list");
    const shop_list = await page.$$("#shop-list > li");
    expect(shop_list.length).toBeGreaterThan(0);
  });

  test("Test that sync button is disabled", async ({ page }) => {
    await page.waitForSelector("#shop-list");
    const shop_list = await page.$$("#shop-list > li");
    expect(shop_list.length).toBeGreaterThan(0);
    await page.waitForSelector(".as-btn-primary");
    const sync_button = await page.$(".as-btn-primary");
    expect(await sync_button?.isEnabled()).toBe(false);
  });

  test("Test that sync button is enabled after click on a shop", async ({
    page,
  }) => {
    await page.waitForSelector("#shop-list");
    const shop_list = await page.$$("#shop-list > li");
    expect(shop_list.length).toBeGreaterThan(0);
    await page.waitForSelector(".as-btn-primary");
    const sync_button = await page.$(".as-btn-primary");
    expect(await sync_button?.isEnabled()).toBe(false);
    await shop_list[0].click();
    expect(await sync_button?.isEnabled()).toBe(true);
  });
});
