const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  // Track all navigations
  page.on('load', () => console.log('[NAV] load event:', page.url()));
  page.on('domcontentloaded', () => console.log('[NAV] DOMContentLoaded:', page.url()));

  // Use API request context to login via API directly
  const api = context.request;

  // GET login page to get CSRF token
  await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
  const csrf = await page.evaluate(() =>
    document.querySelector('input[name="_token"]')?.value || ''
  );
  console.log('CSRF:', csrf);

  // Login via form POST
  await page.fill('input[name="username"]', 'admin');
  await page.fill('input[name="password"]', 'admin123');
  await page.click('button[type="submit"]');

  // Wait for navigation to complete
  await page.waitForTimeout(5000);
  console.log('=== Final URL:', page.url(), '===');

  // NOW take a stable screenshot and analyze
  await page.screenshot({ path: 'tests/debug_state.png' });

  // Check what's in the body
  const bodyExists = await page.evaluate(() => !!document.body).catch(() => 'context destroyed');
  console.log('Body exists:', bodyExists);

  if (bodyExists === true) {
    const info = await page.evaluate(() => ({
      classes: document.body.className,
      sidebar: !!document.querySelector('.sidebar'),
      dashboard: !!document.querySelector('.stats-grid'),
      bodyLen: document.body.innerHTML.length,
    }));
    console.log('Body info:', JSON.stringify(info, null, 2));

    const bodyHtml = await page.evaluate(() => document.body.innerHTML.substring(0, 2000));
    console.log('Body HTML:', bodyHtml.substring(0, 1000));
  }

  await browser.close();
})();
