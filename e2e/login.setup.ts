import { test as setup } from "@playwright/test";
import { login } from "./utils";
import { storageStatePath } from "../playwright.config";

setup("Login", async ({ page }) => {
  await login(page);
  await page.context().storageState({
    path: storageStatePath,
  });
});
