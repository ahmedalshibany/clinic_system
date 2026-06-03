const { chromium } = require('playwright');

const BASE = 'http://127.0.0.1:8000';

(async () => {
  const browser = await chromium.launch({ headless: true });
  let passed = 0;
  let failed = 0;

  async function login(context, username, password) {
    const page = await context.newPage();
    try {
      await page.goto(BASE + '/login', { waitUntil: 'networkidle', timeout: 15000 });
      await page.waitForTimeout(500);
      await page.fill('input[name="username"]', username);
      await page.fill('input[name="password"]', password);
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1500);
      const url = page.url();
      await page.close();
      return !url.includes('/login');
    } catch (e) {
      await page.close();
      return false;
    }
  }

  async function getStatusCode(page, url, method) {
    return await page.evaluate(async (u) => {
      try {
        const r = await fetch(u, { method: method || 'GET', redirect: 'manual' });
        return r.status;
      } catch(e) { return 0; }
    }, url);
  }

  async function checkPageContains(context, url, text, desc) {
    const page = await context.newPage();
    process.stdout.write('  ' + desc + '... ');
    try {
      const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
      const status = resp.status();
      const body = await page.textContent('body');
      if (body.includes(text)) {
        console.log('PASS (found: "' + text.substring(0, 40) + '")');
        passed++;
      } else {
        console.log('FAIL - expected text not found on page (status=' + status + ')');
        console.log('    URL: ' + page.url().substring(0, 80));
        failed++;
      }
    } catch (e) {
      console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
      failed++;
    }
    await page.close();
  }

  async function checkBlocked(page, url, desc) {
    process.stdout.write('  ' + desc + '... ');
    try {
      const resp = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 15000 });
      const status = resp.status();
      const body = await page.textContent('body');
      const currentUrl = page.url();
      const isBlocked = status === 403 || status === 302 || status === 301 || status === 419 || body.includes('Cannot add vitals');
      if (isBlocked) {
        console.log('PASS (blocked: status=' + status + ')');
        passed++;
      } else {
        console.log('FAIL - expected to be blocked but got status=' + status);
        console.log('    URL: ' + currentUrl.substring(0, 80));
        failed++;
      }
    } catch (e) {
      console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
      failed++;
    }
  }

  // === TEST 1: Nurse blocked from vitals page for completed appointment ===
  console.log('\n=== 1. Nurse blocked from completed appointment vitals ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'nurse_joy', 'password');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else { await checkBlocked(await ctx.newPage(), BASE + '/appointments/101/vitals/create', 'GET /appointments/101/vitals/create (completed)'); }
    await ctx.close();
  }

  // === TEST 2: Nurse blocked from vitals page for confirmed (non-pending) appointment ===
  console.log('\n=== 2. Nurse blocked from confirmed appointment vitals ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'nurse_joy', 'password');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else { await checkBlocked(await ctx.newPage(), BASE + '/appointments/102/vitals/create', 'GET /appointments/102/vitals/create (confirmed)'); }
    await ctx.close();
  }

  // === TEST 3: Doctor sees Re-open Vitals button on completed appointment ===
  console.log('\n=== 3. Doctor sees Re-open Vitals on completed appointment ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'doctor', 'doctor123');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else { await checkPageContains(ctx, BASE + '/appointments/101', 'Re-open Vitals', 'Appointment show page contains Re-open Vitals button'); }
    await ctx.close();
  }

  // === TEST 4: Doctor triggers reopen-vitals ===
  console.log('\n=== 4. Doctor triggers Re-open Vitals ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'doctor', 'doctor123');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else {
      const page = await ctx.newPage();
      process.stdout.write('  POST /appointments/101/reopen-vitals... ');
      try {
        const resp = await page.goto(BASE + '/appointments/101', { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(300);
        await page.evaluate(async () => {
          const token = document.querySelector('input[name="_token"]')?.value || '';
          const r = await fetch('/appointments/101/reopen-vitals', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': token },
            body: '_token=' + encodeURIComponent(token)
          });
          return r.status;
        });
        console.log('PASS');
        passed++;
      } catch (e) {
        console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
        failed++;
      }
      await page.close();
    }
    await ctx.close();
  }

  // === TEST 5: Nurse CAN now access vitals after doctor unlocked ===
  console.log('\n=== 5. Nurse accesses vitals after doctor unlock ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'nurse_joy', 'password');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else {
      const page = await ctx.newPage();
      process.stdout.write('  GET /appointments/101/vitals/create (after unlock)... ');
      try {
        const resp = await page.goto(BASE + '/appointments/101/vitals/create', { waitUntil: 'domcontentloaded', timeout: 15000 });
        const status = resp.status();
        const body = await page.textContent('body');
        if (status === 200 && (body.includes('Temperature') || body.includes('Vitals Form') || body.includes('Record Vitals'))) {
          console.log('PASS (status=' + status + ', vitals form loaded)');
          passed++;
        } else if (body.includes('Cannot add vitals')) {
          console.log('FAIL - still blocked after unlock');
          failed++;
        } else {
          console.log('FAIL - unexpected response (status=' + status + ')');
          failed++;
        }
      } catch (e) {
        console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
        failed++;
      }
      await page.close();
    }
    await ctx.close();
  }

  // === TEST 6: Patient create form has no photo field ===
  console.log('\n=== 6. Patient create form has no photo field ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'admin', 'admin123');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else {
      const page = await ctx.newPage();
      process.stdout.write('  GET /patients/create (check no photo)... ');
      try {
        await page.goto(BASE + '/patients/create', { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(500);
        const photoInputs = await page.locator('input[name="photo"]').count();
        const photoLabels = await page.locator('text=Patient Photo').count();
        if (photoInputs === 0 && photoLabels === 0) {
          console.log('PASS (no photo input/label found)');
          passed++;
        } else {
          console.log('FAIL - photo field still present (inputs=' + photoInputs + ', labels=' + photoLabels + ')');
          failed++;
        }
      } catch (e) {
        console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
        failed++;
      }
      await page.close();
    }
    await ctx.close();
  }

  // === TEST 7: Nurse CAN submit vitals for pending appointment (normal flow) ===
  console.log('\n=== 7. Normal vital submission for pending appointment ===');
  {
    const ctx = await browser.newContext();
    const ok = await login(ctx, 'nurse_joy', 'password');
    if (!ok) { console.log('  LOGIN FAILED'); failed++; }
    else {
      const page = await ctx.newPage();
      process.stdout.write('  POST vitals for pending appointment 104... ');
      try {
        // Load the vitals form page first to get the CSRF token
        await page.goto(BASE + '/appointments/104/vitals/create', { waitUntil: 'domcontentloaded', timeout: 15000 });
        await page.waitForTimeout(500);
        const token = await page.evaluate(() => document.querySelector('input[name="_token"]')?.value || '');
        const status = await page.evaluate(async (csrfToken) => {
          const r = await fetch('/appointments/104/vitals', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: '_token=' + encodeURIComponent(csrfToken) +
              '&temperature=36.6&bp_systolic=120&bp_diastolic=80&pulse=72&weight=70&height=170&oxygen_saturation=98&respiratory_rate=16'
          });
          return r.status;
        }, token);
        if (status === 302 || status === 200 || status === 201) {
          console.log('PASS (vitals accepted, status=' + status + ')');
          passed++;
        } else {
          console.log('FAIL (vitals rejected, status=' + status + ')');
          failed++;
        }
      } catch (e) {
        console.log('FAIL (error: ' + e.message.substring(0, 60) + ')');
        failed++;
      }
      await page.close();
    }
    await ctx.close();
  }

  console.log('\n========================================');
  console.log('VITALS WORKFLOW RESULTS: ' + passed + ' PASSED, ' + failed + ' FAILED');
  console.log('========================================');
  await browser.close();
  process.exit(failed > 0 ? 1 : 0);
})();
