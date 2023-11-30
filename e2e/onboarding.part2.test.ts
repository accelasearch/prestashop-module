import { test, expect } from "@playwright/test";
import { goToModuleConfigurationPage, setOnBoarding } from "./utils";

test.describe("Test Onboarding step 2", () => {
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await setOnBoarding(page, 1);
  });

  test.beforeEach(async ({ page }) => {
    await goToModuleConfigurationPage(page);
  });

  test("Test that first sync type is selected by default", async ({ page }) => {
    await page.waitForSelector("div[role='radiogroup']");
    expect(
      await page.getByLabel("Sia prodotti configurabili").isChecked()
    ).toBe(true);
  });

  test("Test that color and size attribute are set to 0 at page load", async ({
    page,
  }) => {
    await page.waitForSelector("#_ACCELASEARCH_COLOR_ID");
    const color_value = await page.$eval<string, HTMLSelectElement>(
      "#_ACCELASEARCH_COLOR_ID",
      (sel) => sel.value
    );
    const size_value = await page.$eval<string, HTMLSelectElement>(
      "#_ACCELASEARCH_SIZE_ID",
      (sel) => sel.value
    );
    expect(color_value).toBe("0");
    expect(size_value).toBe("0");
  });

  test("Test that there are more than 1 option inside color attribute and size attribute", async ({
    page,
  }) => {
    await page.waitForSelector("#_ACCELASEARCH_COLOR_ID");
    const color_options = await page.$$("#_ACCELASEARCH_COLOR_ID > option");
    const size_options = await page.$$("#_ACCELASEARCH_SIZE_ID > option");
    expect(color_options.length).toBeGreaterThan(1);
    expect(size_options.length).toBeGreaterThan(1);
  });
});
