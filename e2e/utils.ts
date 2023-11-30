import { Page, expect } from "@playwright/test";

declare global {
  interface Window {
    accelasearch_controller_url: string;
    token: string;
  }
}

async function login(page: Page) {
  await page.goto("http://localhost:8199/adminPS/");
  await page.getByRole("textbox", { name: "Indirizzo email" }).click();
  await page
    .getByRole("textbox", { name: "Indirizzo email" })
    .fill("demo@prestashop.com");
  await page.getByPlaceholder(" Password").fill("prestashop_demo");
  await page.getByRole("button", { name: "Entra" }).click();
  await expect(page.locator("#calendar")).toBeVisible({ timeout: 10000 });
}

async function goToBackoffice(page: Page) {
  await page.goto("http://localhost:8199/adminPS/");
}

async function goToModuleConfigurationPage(page: Page) {
  await page.goto(
    "http://localhost:8199/adminPS/index.php?controller=AdminModules&configure=accelasearch"
  );
}

async function clearAllData(page: Page) {
  const controller_url =
    "http://localhost:8199/adminPS/index.php?controller=accelasearchAdmin&ajax=1&action=clearalldata";
  await page.goto(controller_url);
}

async function setOnBoarding(page: Page, onBoarding: number) {
  const controller_url =
    "http://localhost:8199/adminPS/index.php?controller=accelasearchAdmin&ajax=1&action=setOnBoarding&onBoarding=" +
    onBoarding;
  await page.goto(controller_url);
}

async function finishOnBoarding(page: Page) {
  const controller_url =
    "http://localhost:8199/adminPS/index.php?controller=accelasearchAdmin&ajax=1&action=finishOnBoarding";
  await page.goto(controller_url);
}

export {
  login,
  clearAllData,
  setOnBoarding,
  goToBackoffice,
  goToModuleConfigurationPage,
  finishOnBoarding,
};
