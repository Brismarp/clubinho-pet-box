import { test, expect } from '@playwright/test';

test('home shows satisfaction stats band', async ({ page }) => {
  await page.goto('/');
  await expect(page.locator('#numeros')).toBeVisible();
  await expect(page.locator('[data-count-up]').first()).toBeVisible();
});

test('assinatura form reaches mock pay with csrf', async ({ page }) => {
  await page.goto('/assinatura?plan=happy&billing=monthly');
  await page.fill('#name', 'Playwright Tutor');
  await page.fill('#email', `pw-${Date.now()}@example.com`);
  await page.fill('#pet_name', 'Luna');
  await page.selectOption('#pet_type', 'cachorro');
  await page.selectOption('#plan', 'happy');
  await page.check('#terms');
  await Promise.all([
    page.waitForURL(/mock_checkout=1/),
    page.click('button[type="submit"]'),
  ]);
  await expect(page.locator('#mock-pay-panel')).toBeVisible();
  await page.click('#mock-pay-pix');
  await expect(page.locator('#mock-pay-result')).toContainText(/Pago|paid|ativa|assinatura|confirmad/i, {
    timeout: 15_000,
  });
});

test('quiz page loads enriched fields', async ({ page }) => {
  await page.goto('/quiz');
  await expect(page.locator('[data-quiz-root]')).toBeVisible();
  // advance to routine if needed — at least ensure script present
  await expect(page.locator('script[src*="quiz-v2"]')).toHaveCount(1);
});

test('enviar-foto page loads', async ({ page }) => {
  await page.goto('/enviar-foto');
  await expect(page.getByRole('heading', { name: /Envie a foto/i })).toBeVisible();
  await expect(page.locator('input[type="file"]')).toBeVisible();
});

test('admin can open happy-media moderation', async ({ page }) => {
  await page.goto('/admin/login');
  await page.fill('input[name="username"]', 'admin@clubinhopetbox.com.br');
  await page.fill('input[name="password"]', 'Carpe1936!');
  await page.click('button[type="submit"]');
  await page.waitForURL(/\/admin/);
  await page.goto('/admin/happy-media?status=pending');
  await expect(page.locator('h1')).toContainText(/Pets Felizes/i);
});
