const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const results = [];
  let passed = 0;
  let failed = 0;

  function ok(msg) { results.push('  PASS: ' + msg); passed++; }
  function fail(msg) { results.push('  FAIL: ' + msg); failed++; }

  try {
    results.push('\n=== ANALYTICS DASHBOARD TEST ===');

    // Login
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');

    // Click submit and WAIT for navigation to complete to dashboard
    await Promise.all([
      page.waitForNavigation({ url: '**/dashboard', timeout: 10000 }),
      page.click('button[type="submit"]'),
    ]);
    results.push('Logged in, URL: ' + page.url());

    // Let page stabilize
    await page.waitForTimeout(3000);

    // Take a screenshot to see what's happening
    await page.screenshot({ path: 'tests/dashboard_state.png' });
    results.push('State screenshot saved');

    // Check for stat numbers
    const hasStats = await page.locator('.stat-number').count();
    results.push('Stat elements found: ' + hasStats);

    if (hasStats > 0) {
      const stats = await page.locator('.stat-number').allTextContents();
      stats.forEach((s, i) => results.push('  [' + i + '] ' + s.trim()));
      ok('Stat cards render with values');
    } else {
      fail('No stat cards found');
    }

    // Check for chart canvases
    const statusCanvas = await page.locator('#statusChart').count();
    const revenueCanvas = await page.locator('#revenueChart').count();
    results.push('Status chart (#statusChart): ' + statusCanvas);
    results.push('Revenue chart (#revenueChart): ' + revenueCanvas);
    if (statusCanvas > 0 && revenueCanvas > 0) ok('Both chart canvases present');

    // Check if canvases have dimensions (rendered by Chart.js)
    const chartSize = await page.evaluate(() => {
      const s = document.getElementById('statusChart');
      const r = document.getElementById('revenueChart');
      return {
        statusOk: s && s.width > 0 && s.height > 0,
        revenueOk: r && r.width > 0 && r.height > 0
      };
    });
    results.push('Status chart rendered: ' + chartSize.statusOk);
    results.push('Revenue chart rendered: ' + chartSize.revenueOk);
    if (chartSize.statusOk && chartSize.revenueOk) ok('Charts rendered with valid dimensions');

    // Check status grid items
    const statusItems = await page.locator('.status-item').count();
    results.push('Status grid items: ' + statusItems);
    if (statusItems === 4) ok('Status grid shows 4 appointment statuses');

    // Check revenue panel title
    const titleExists = await page.locator('.trend-panel h3').count();
    results.push('Revenue panel title exists: ' + titleExists);
    if (titleExists > 0) {
      const title = await page.textContent('.trend-panel h3');
      results.push('  Title text: "' + title.trim() + '"');
      if (title.includes('Monthly Revenue')) ok('Revenue panel has correct title');
    }

    // Screenshot light mode
    await page.screenshot({ path: 'tests/dashboard_light.png', fullPage: true });
    results.push('Screenshot: tests/dashboard_light.png');

    // Dark mode toggle
    const toggleCount = await page.locator('#themeToggle').count();
    results.push('\nTheme toggle found: ' + toggleCount);
    if (toggleCount > 0) {
      await page.click('#themeToggle');
      await page.waitForTimeout(300);

      // Check theme attribute changed
      const theme = await page.evaluate(() =>
        document.documentElement.getAttribute('data-theme')
      );
      results.push('Theme after toggle: ' + theme);

      // Reload and check charts re-render with dark colors
      await page.reload({ waitUntil: 'load' });
      await page.waitForTimeout(3000);

      const themeAgain = await page.evaluate(() =>
        document.documentElement.getAttribute('data-theme')
      );
      results.push('Theme persisted after reload: ' + themeAgain);

      const darkCharts = await page.evaluate(() => {
        const r = document.getElementById('revenueChart');
        return r && r.width > 0 && r.height > 0;
      });
      results.push('Dark mode charts rendered: ' + darkCharts);
      if (themeAgain === 'dark' && darkCharts) ok('Dark mode charts render correctly');

      await page.screenshot({ path: 'tests/dashboard_dark.png', fullPage: true });
      results.push('Screenshot: tests/dashboard_dark.png');
    } else {
      results.push('  (theme toggle not found, skipping dark mode test)');
    }

    // Summary
    results.push('\n========================================');
    results.push('Passed: ' + passed + ' / Failed: ' + failed + ' / Total: ' + (passed + failed));
    results.push('========================================');

    await browser.close();
    console.log(results.join('\n'));
    process.exit(failed > 0 ? 1 : 0);

  } catch (e) {
    console.error('ERROR:', e.message);
    try {
      await page.screenshot({ path: 'tests/error_screenshot.png' });
      results.push('Error screenshot saved');
    } catch (_) {}
    await browser.close();
    process.exit(1);
  }
})();
