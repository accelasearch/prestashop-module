import { test, expect } from "@playwright/test";
import { clearAllData, goToModuleConfigurationPage } from "./utils";

test.describe.configure({ mode: "parallel" });

test.describe("Test Welcome page", () => {
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    const page = await browser.newPage();
    await clearAllData(page);
  });

  test.beforeEach(async ({ page }) => {
    await goToModuleConfigurationPage(page);
  });

  test("Test view switch on click start now", async ({ page }) => {
    await expect(page.locator(".start_now ").first()).toBeVisible();
    await page.locator(".start_now ").first().click();
    await expect(
      page.getByRole("heading", { name: "Collega il tuo account" })
    ).toBeVisible();
  });

  test("Test apikey error with an empty field", async ({ page }) => {
    await expect(page.locator(".start_now ").first()).toBeVisible();
    await page.locator(".start_now ").first().click();
    await page.getByPlaceholder("Api Key").fill("");
    await page
      .getByRole("button", { name: "Collegati a Accelasearch" })
      .click();
    await expect(page.getByText("Inserisci una ApiKey valida")).toBeVisible();
  });

  test("Test apikey error with an 123 field", async ({ page }) => {
    await expect(page.locator(".start_now ").first()).toBeVisible();
    await page.locator(".start_now ").first().click();
    await page.getByPlaceholder("Api Key").fill("123");
    await page
      .getByRole("button", { name: "Collegati a Accelasearch" })
      .click();
    await expect(page.getByText("Inserisci una ApiKey valida")).toBeVisible({
      timeout: 10000,
    });
  });

  test("Test a valid apikey", async ({ page }) => {
    const apikey = "fycTY16AtSMF2BthV4gW7BNmrj5TCaWh";
    await expect(page.locator(".start_now ").first()).toBeVisible();
    await page.locator(".start_now ").first().click();
    await page.getByPlaceholder("Api Key").fill(apikey);
    await page
      .getByRole("button", { name: "Collegati a Accelasearch" })
      .click();
    await expect(page.getByText("Verifica ApiKey...")).toBeVisible();
    await expect(
      page.getByText(/^La tua ApiKey è valida! Redirezionamento\.\.\.$/)
    ).toBeVisible({ timeout: 20000 });
  });
});
