const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  const results = [];
  let passed = 0;
  let failed = 0;

  function ok(msg) { results.push('  PASS: ' + msg); passed++; }
  function fail(msg) { results.push('  FAIL: ' + msg); failed++; }

  try {
    // 1. LOGIN AS RECEPTIONIST
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    await page.fill('input[name="username"]', 'receptionist');
    await page.fill('input[name="password"]', 'reception123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    results.push('\n[LOGIN] Receptionist logged in');
    ok('Receptionist can log in');

    // Grab CSRF token from page
    const csrfToken = await page.evaluate(() =>
      document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    );

    // Use Playwright's APIRequestContext attached to the browser context (shares cookies)
    const api = await context.request;

    // 2. TEST 1: POST /medical-records -> expect 403
    results.push('\n[TEST 1] Receptionist POST /medical-records -> expect 403');
    const resp1 = await api.fetch('http://127.0.0.1:8000/medical-records', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      data: JSON.stringify({
        patient_id: 1,
        doctor_id: 1,
        appointment_id: 1,
        visit_date: '2026-06-02',
        diagnosis: 'Test diagnosis',
      }),
    });
    if (resp1.status() === 403) {
      ok('POST /medical-records returned 403 Forbidden');
    } else {
      fail('POST /medical-records returned ' + resp1.status() + ' instead of 403');
    }

    // 3. TEST 2: DELETE /patients/1 -> expect 403 (destroy guard)
    results.push('\n[TEST 2] Receptionist DELETE /patients/1 -> expect 403');
    const resp2 = await api.fetch('http://127.0.0.1:8000/patients/1', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    if (resp2.status() === 403) {
      ok('DELETE /patients/1 returned 403 Forbidden (destroy guard active)');
    } else {
      fail('DELETE /patients/1 returned ' + resp2.status() + ' instead of 403');
    }

    // 4. TEST 3: Navigate to /medical-records -> expect 403 page
    results.push('\n[TEST 3] Receptionist navigates to /medical-records -> expect 403');
    await page.goto('http://127.0.0.1:8000/medical-records', { waitUntil: 'networkidle', timeout: 10000 });
    const body3 = await page.content();
    if (body3.includes('403') || body3.includes('Forbidden') || page.url().includes('403')) {
      ok('GET /medical-records shows 403 Forbidden page');
    } else {
      fail('GET /medical-records did not return 403 (URL: ' + page.url() + ')');
    }

    // 5. TEST 4: Navigate to /patients -> expect 200 (legitimate access)
    results.push('\n[TEST 4] Receptionist navigates to /patients -> expect 200');
    await page.goto('http://127.0.0.1:8000/patients', { waitUntil: 'networkidle', timeout: 10000 });
    const url4 = page.url();
    if (url4.includes('patients') && !url4.includes('login') && !url4.includes('403')) {
      ok('Receptionist can access patients list');
    } else {
      fail('Receptionist blocked from patients (URL: ' + url4 + ')');
    }

    // 6. TEST 5: Navigate to /invoices -> expect 200 (legitimate access)
    results.push('\n[TEST 5] Receptionist navigates to /invoices -> expect 200');
    await page.goto('http://127.0.0.1:8000/invoices', { waitUntil: 'networkidle', timeout: 10000 });
    const url5 = page.url();
    if (url5.includes('invoices') && !url5.includes('login') && !url5.includes('403')) {
      ok('Receptionist can access invoices list');
    } else {
      fail('Receptionist blocked from invoices (URL: ' + url5 + ')');
    }

    // 7. TEST 6: DELETE /appointments/1 -> expect 403 (admin-only destroy)
    results.push('\n[TEST 6] Receptionist DELETE /appointments/1 -> expect 403');
    const resp6 = await api.fetch('http://127.0.0.1:8000/appointments/1', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
    });
    if (resp6.status() === 403) {
      ok('DELETE /appointments/1 returned 403 Forbidden');
    } else {
      fail('DELETE /appointments/1 returned ' + resp6.status() + ' instead of 403');
    }

    // Summary
    results.push('\n========================================');
    results.push('SECURITY RBAC TEST RESULTS');
    results.push('========================================');
    results.push('Passed: ' + passed + ' / Failed: ' + failed + ' / Total: ' + (passed + failed));

    await browser.close();
    console.log(results.join('\n'));
    process.exit(failed > 0 ? 1 : 0);

  } catch (e) {
    console.error('FATAL ERROR:', e.message);
    await browser.close();
    process.exit(1);
  }
})();
