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
    results.push('\n=== ARABIC LOCALIZATION VALIDATION TEST ===\n');

    // 1. Login
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load' });
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await Promise.all([
      page.waitForNavigation({ url: '**/dashboard', timeout: 10000 }),
      page.click('button[type="submit"]'),
    ]);
    results.push('Logged in, URL: ' + page.url());

    // 2. Switch to Arabic
    const langToggle = await page.locator('#langToggle');
    if (await langToggle.count() > 0) {
      // Check current lang toggle text and click if needed
      const toggleText = await langToggle.textContent();
      if (toggleText.trim() === 'Ar') {
        await langToggle.click();
        await page.waitForTimeout(500);
        ok('Language toggled to Arabic');
      } else {
        ok('Language already set to Arabic');
      }
    } else {
      fail('Language toggle button not found - setting lang via localStorage');
      await page.evaluate(() => {
        localStorage.setItem('clinic_lang', 'ar');
        document.documentElement.lang = 'ar';
        document.documentElement.dir = 'rtl';
      });
      await page.reload({ waitUntil: 'load' });
      await page.waitForTimeout(2000);
    }

    // Verify Arabic is active
    const htmlLang = await page.evaluate(() => document.documentElement.lang);
    const htmlDir = await page.evaluate(() => document.documentElement.dir);
    results.push('HTML lang: ' + htmlLang + ', dir: ' + htmlDir);
    if (htmlLang === 'ar') ok('HTML lang attribute set to ar');
    if (htmlDir === 'rtl') ok('HTML dir attribute set to rtl');

    // 3. Check sidebar navigation is in Arabic
    const sidebarLinks = await page.locator('.sidebar .nav-link span[data-i18n]').allTextContents();
    const sidebarText = sidebarLinks.join(' | ');
    results.push('Sidebar text: ' + sidebarText);
    if (sidebarText.includes('لوحة التحكم')) ok('Sidebar shows Arabic dashboard label');
    if (sidebarText.includes('المرضى')) ok('Sidebar shows Arabic patients label');
    if (sidebarText.includes('المواعيد')) ok('Sidebar shows Arabic appointments label');
    if (sidebarText.includes('الأطباء')) ok('Sidebar shows Arabic doctors label');

    // 4. Check dashboard greeting
    const greeting = await page.locator('#dashboard-greeting').textContent().catch(() => '');
    results.push('Dashboard greeting: ' + greeting);
    if (greeting && (greeting.includes('صباح') || greeting.includes('مساء'))) ok('Greeting is in Arabic');

    // 5. Take screenshot of Arabic dashboard
    await page.screenshot({ path: 'tests/arabic_dashboard.png', fullPage: true });
    results.push('Screenshot: tests/arabic_dashboard.png');

    // 6. Check Chart.js canvas exists and has rendered dimensions
    const statusChart = await page.evaluate(() => {
      const el = document.getElementById('statusChart');
      return el ? { exists: true, width: el.width, height: el.height } : { exists: false };
    });
    const revenueChart = await page.evaluate(() => {
      const el = document.getElementById('revenueChart');
      return el ? { exists: true, width: el.width, height: el.height } : { exists: false };
    });
    results.push('Status chart: ' + JSON.stringify(statusChart));
    results.push('Revenue chart: ' + JSON.stringify(revenueChart));
    if (statusChart.exists && statusChart.width > 0) ok('Status chart canvas rendered');
    if (revenueChart.exists && revenueChart.width > 0) ok('Revenue chart canvas rendered');

    // 7. Extract chart labels/legend data via Chart.js API
    const chartLabels = await page.evaluate(() => {
      const labels = {};
      // Try status chart - check translated data labels
      const sc = document.getElementById('statusChart');
      if (sc && Chart.getChart) {
        try {
          const chart = Chart.getChart(sc);
          if (chart && chart.data && chart.data.labels) {
            labels.statusLabels = chart.data.labels;
          }
        } catch(e) { labels.statusError = e.message; }
      }
      // Try revenue chart
      const rc = document.getElementById('revenueChart');
      if (rc && Chart.getChart) {
        try {
          const chart = Chart.getChart(rc);
          if (chart && chart.data && chart.data.labels) {
            labels.revenueLabels = chart.data.labels;
          }
          if (chart && chart.data && chart.data.datasets && chart.data.datasets[0]) {
            labels.revenueDatasetLabel = chart.data.datasets[0].label;
          }
        } catch(e) { labels.revenueError = e.message; }
      }
      return labels;
    });
    results.push('Chart data: ' + JSON.stringify(chartLabels));

    if (chartLabels.statusLabels) {
      const statusText = chartLabels.statusLabels.join(' ');
      results.push('Status chart labels: ' + statusText);
      if (statusText.includes('مستحق') || statusText.includes('مؤكد') || statusText.includes('مكتمل') || statusText.includes('ملغي')) {
        ok('Status chart labels are in Arabic');
      } else {
        fail('Status chart labels are NOT in Arabic: ' + statusText);
      }
    } else {
      results.push('Status chart labels could not be extracted (Chart.getChart may not be available)');
    }

    if (chartLabels.revenueLabels && chartLabels.revenueLabels.length > 0) {
      const revLabel = chartLabels.revenueLabels[0];
      results.push('Revenue chart first label: ' + revLabel);
      // Check if Arabic month names appear
      if (revLabel && (revLabel.includes('يناير') || revLabel.includes('فبراير') || revLabel.includes('مارس') || revLabel.includes('أبريل') || revLabel.includes('مايو') || revLabel.includes('يونيو') || revLabel.includes('يوليو') || revLabel.includes('أغسطس') || revLabel.includes('سبتمبر') || revLabel.includes('أكتوبر') || revLabel.includes('نوفمبر') || revLabel.includes('ديسمبر'))) {
        ok('Revenue chart month labels are in Arabic');
      } else {
        fail('Revenue chart month labels are NOT in Arabic: ' + revLabel);
      }
    } else {
      results.push('Revenue chart labels could not be extracted');
    }

    if (chartLabels.revenueDatasetLabel) {
      if (chartLabels.revenueDatasetLabel === 'الإيرادات') {
        ok('Revenue chart dataset label is in Arabic');
      }
    }

    // 8. Navigate to appointments page and check Arabic text
    await page.goto('http://127.0.0.1:8000/appointments', { waitUntil: 'load' });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: 'tests/arabic_appointments.png', fullPage: true });
    results.push('Screenshot: tests/arabic_appointments.png');

    // Check page title
    const pageTitle = await page.locator('.header-title').textContent().catch(() => '');
    results.push('Page title: ' + pageTitle);
    if (pageTitle.includes('المواعيد')) ok('Appointments page title in Arabic');
    else fail('Appointments page title NOT in Arabic: ' + pageTitle);

    // Check table headers
    const headers = await page.locator('table th[data-i18n]').allTextContents().catch(async () => []);
    results.push('Table headers: ' + headers.join(' | '));
    const headerText = headers.join(' ');
    if (headerText.includes('المريض') || headerText.includes('الطبيب') || headerText.includes('التاريخ') || headerText.includes('الوقت') || headerText.includes('الحالة')) {
      ok('Table headers are in Arabic');
    } else {
      fail('Table headers NOT in Arabic: ' + headerText);
    }

    // 9. Navigate to patients page
    await page.goto('http://127.0.0.1:8000/patients', { waitUntil: 'load' });
    await page.waitForTimeout(1000);
    await page.screenshot({ path: 'tests/arabic_patients.png', fullPage: true });
    results.push('Screenshot: tests/arabic_patients.png');

    const patientsTitle = await page.locator('.header-title').textContent().catch(() => '');
    if (patientsTitle.includes('المرضى')) ok('Patients page title in Arabic');
    else fail('Patients page title NOT in Arabic: ' + patientsTitle);

    // 10. Check lang toggle shows correct text (should show "English" when in Arabic mode)
    const langBtnText = await page.locator('#langToggleText').textContent().catch(() => '');
    results.push('Lang toggle text: ' + langBtnText);
    if (langBtnText === 'English') ok('Language toggle shows "English" when in Arabic mode');

    // Summary
    results.push('\n========================================');
    results.push('Passed: ' + passed + ' / Failed: ' + failed + ' / Total: ' + (passed + failed));
    results.push('========================================');

    await browser.close();
    console.log(results.join('\n'));
    process.exit(failed > 0 ? 1 : 0);

  } catch (e) {
    console.error('ERROR:', e.message);
    results.push('\nCRASHED: ' + e.message);
    try {
      await page.screenshot({ path: 'tests/arabic_error.png' });
    } catch (_) {}
    await browser.close();
    console.log(results.join('\n'));
    process.exit(1);
  }
})();
