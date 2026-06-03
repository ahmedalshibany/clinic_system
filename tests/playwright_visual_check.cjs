const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const results = [];

  try {
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    results.push('=== LOGIN PAGE ===');
    results.push('Title: ' + (await page.title()));
    const theme = await page.evaluate(() => document.documentElement.getAttribute('data-theme') || 'none');
    results.push('Theme attr: ' + theme);
    const loginCard = await page.isVisible('.login-card');
    results.push('Login card visible: ' + loginCard);
    const inputs = await page.locator('input').count();
    results.push('Input fields: ' + inputs);
    const dir = await page.evaluate(() => document.documentElement.dir);
    results.push('Direction: ' + dir);

    // Login
    results.push('\n=== LOGIN ===');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    results.push('After login URL: ' + page.url());

    // Sidebar
    const sidebar = await page.isVisible('.sidebar');
    results.push('Sidebar visible: ' + sidebar);
    const navLinks = await page.locator('.sidebar .nav-link').all();
    results.push('Nav links: ' + navLinks.length);
    for (let link of navLinks) {
      const text = await link.textContent();
      const href = await link.getAttribute('href');
      results.push('  -> ' + text.trim() + ' [' + href + ']');
    }

    // Stats
    const stats = await page.locator('.stat-card-premium').count();
    results.push('Stat cards: ' + stats);

    // Header controls
    results.push('Theme toggle: ' + (await page.isVisible('#themeToggle')));
    results.push('User profile: ' + (await page.isVisible('.user-profile-glass')));
    results.push('Notif bell: ' + (await page.isVisible('#notificationDropdown')));
    results.push('Lang toggle: ' + (await page.isVisible('#langToggle')));

    // Charts
    results.push('Chart canvases: ' + (await page.locator('canvas').count()));
    results.push('Activity section: ' + (await page.isVisible('.activity-section')));

    // Dark mode toggle
    results.push('\n=== DARK MODE ===');
    await page.click('#themeToggle');
    const theme2 = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
    results.push('Theme after toggle: ' + theme2);
    const darkBg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    results.push('Body bg after toggle: ' + darkBg);
    const moonIcon = await page.evaluate(() => {
      const btn = document.querySelector('#themeToggle i');
      return btn ? btn.className : 'not found';
    });
    results.push('Toggle icon after dark: ' + moonIcon);

    // Check a page for spacing/symmetry issues
    results.push('\n=== PATIENTS PAGE ===');
    await page.goto('http://127.0.0.1:8000/patients', { waitUntil: 'networkidle' });
    const hasTable = await page.isVisible('table');
    results.push('Table exists: ' + hasTable);
    const hasCreateBtn = await page.isVisible('a[href*="patients/create"]');
    results.push('Create button: ' + hasCreateBtn);
    
    // Check if sidebar reflects role
    const reportsLink = await page.isVisible('a[href*="/reports"]');
    results.push('Reports link visible (admin): ' + reportsLink);
    
    // Mobile
    results.push('\n=== MOBILE VIEWPORT (375x667) ===');
    await page.setViewportSize({ width: 375, height: 667 });
    const mobileToggle = await page.isVisible('#sidebarToggle');
    results.push('Mobile menu button visible: ' + mobileToggle);

    // Take full-page screenshot
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.screenshot({ path: 'tests/screenshot_visual_audit.png', fullPage: true });

    await browser.close();
    results.push('\n=== PLAYWRIGHT CHECK COMPLETE ===');
    console.log(results.join('\n'));
  } catch (e) {
    console.error('Error:', e.message);
    await browser.close();
  }
})();
