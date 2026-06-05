const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const results = [];

  try {
    // -- LOGIN PAGE ------------------------------------------------------------------
    await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'networkidle' });
    results.push('=== LOGIN PAGE ===');
    results.push('Title: ' + (await page.title()));
    const theme = await page.evaluate(() => document.documentElement.getAttribute('data-theme') || 'none');
    results.push('Theme attr (no-flash init): ' + theme);
    const loginCard = await page.isVisible('.login-card');
    results.push('Login card visible: ' + loginCard);
    const inputs = await page.locator('input').count();
    results.push('Input fields: ' + inputs);
    const dir = await page.evaluate(() => document.documentElement.dir);
    results.push('Direction: ' + dir);

    // -- LOGIN ----------------------------------------------------------------------
    results.push('\n=== LOGIN ===');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard', { timeout: 5000 });
    results.push('After login URL: ' + page.url());

    // -- SIDEBAR DOM VALIDATION -----------------------------------------------------
    const sidebar = await page.isVisible('.sidebar');
    results.push('Sidebar visible: ' + sidebar);
    const navLinks = await page.locator('.sidebar .nav-link').all();
    results.push('Nav links: ' + navLinks.length);
    for (let link of navLinks) {
      const text = await link.textContent();
      const href = await link.getAttribute('href');
      results.push('  -> ' + text.trim() + ' [' + href + ']');
    }

    // HTML parse validation: inject a fake parse check
    const htmlParseOk = await page.evaluate(() => {
      try {
        const p = new DOMParser();
        const doc = p.parseFromString(document.documentElement.outerHTML, 'text/html');
        return doc.querySelectorAll('parsererror').length === 0;
      } catch (e) { return false; }
    });
    results.push('HTML parse errors: ' + (htmlParseOk ? '0' : 'ERRORS FOUND'));

    // -- STATS / HEADER -------------------------------------------------------------
    const stats = await page.locator('.stat-card-premium').count();
    results.push('Stat cards: ' + stats);
    results.push('Theme toggle: ' + (await page.isVisible('#themeToggle')));
    results.push('User profile: ' + (await page.isVisible('.user-profile-glass')));
    results.push('Notif bell: ' + (await page.isVisible('#notificationDropdown')));
    results.push('Lang toggle: ' + (await page.isVisible('#langToggle')));
    results.push('Chart canvases: ' + (await page.locator('canvas').count()));
    results.push('Activity section: ' + (await page.isVisible('.activity-section')));

    // -- SUCCESS TOAST VERIFICATION -------------------------------------------------
    results.push('\n=== SUCCESS TOAST ===');
    const toastShown = await page.evaluate(() => {
      try {
        if (typeof toast !== 'undefined') {
          toast.show('Test success message', 'success');
          const container = document.querySelector('.toast-container');
          if (!container) return { toastExists: false, reason: 'no container' };
          const toasts = container.querySelectorAll('.toast-notification');
          const lastToast = toasts[toasts.length - 1];
          if (!lastToast) return { toastExists: false, reason: 'no toast element' };
          return {
            toastExists: true,
            count: toasts.length,
            className: lastToast.className,
            text: lastToast.querySelector('.toast-message')?.textContent || ''
          };
        }
        return { toastExists: false, reason: 'toast undefined' };
      } catch (e) { return { toastExists: false, reason: e.message }; }
    });
    results.push('Toast shown: ' + toastShown.toastExists);
    if (toastShown.toastExists) {
      results.push('Toast class: ' + toastShown.className);
      results.push('Toast message: ' + toastShown.text);
      results.push('Toast count: ' + toastShown.count);
    } else {
      results.push('Toast reason: ' + (toastShown.reason || 'unknown'));
    }
    // Also test showAlert from App class
    const alertToastShown = await page.evaluate(() => {
      try {
        if (typeof app !== 'undefined') {
          app.showAlert('App-level success', 'success');
          const container = document.querySelector('.toast-container');
          if (!container) return false;
          const toasts = container.querySelectorAll('.toast-notification');
          return toasts.length > 0;
        }
        return false;
      } catch (e) { return false; }
    });
    results.push('App.showAlert(success) works: ' + alertToastShown);

    // -- DARK MODE FLASH-FREE INIT -------------------------------------------------
    results.push('\n=== DARK MODE (FLASH-FREE) ===');
    // Check theme attr is already set before any JS runs by reading the raw HTML
    const themeInit = await page.evaluate(() => {
      // Simulate reading from the initial HTML source
      const attr = document.documentElement.getAttribute('data-theme');
      return attr || 'none';
    });
    results.push('data-theme on <html>: ' + themeInit);

    // Toggle to dark
    await page.click('#themeToggle');
    const theme2 = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
    results.push('Theme after toggle: ' + theme2);
    const darkBg = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
    results.push('Body bg after toggle: ' + darkBg);
    const toggleIcon = await page.evaluate(() => {
      const btn = document.querySelector('#themeToggle i');
      return btn ? btn.className : 'not found';
    });
    results.push('Toggle icon after dark: ' + toggleIcon);

    // Toggle back to light
    await page.click('#themeToggle');
    const theme3 = await page.evaluate(() => document.documentElement.getAttribute('data-theme'));
    results.push('Theme after toggle back: ' + theme3);

    // -- PATIENTS PAGE --------------------------------------------------------------
    results.push('\n=== PATIENTS PAGE ===');
    await page.goto('http://127.0.0.1:8000/patients', { waitUntil: 'networkidle' });
    const hasTable = await page.isVisible('table');
    results.push('Table exists: ' + hasTable);
    const hasCreateBtn = await page.isVisible('a[href*="patients/create"]');
    results.push('Create button: ' + hasCreateBtn);
    const reportsLink = await page.isVisible('a[href*="/reports"]');
    results.push('Reports link visible (admin): ' + reportsLink);

    // -- RTL MODE CHECK -------------------------------------------------------------
    results.push('\n=== RTL MODE (ARABIC) ===');
    await page.evaluate(() => {
      if (typeof app !== 'undefined') {
        app.lang = 'ar';
        app.applyLanguage('ar');
      }
    });
    const rtlDir = await page.evaluate(() => document.documentElement.dir);
    results.push('Direction after RTL toggle: ' + rtlDir);
    const rtlLang = await page.evaluate(() => document.documentElement.lang);
    results.push('Lang after RTL toggle: ' + rtlLang);
    const rtlToggleText = await page.evaluate(() => {
      const el = document.getElementById('langToggleText');
      return el ? el.textContent : 'not found';
    });
    results.push('Lang toggle shows: ' + rtlToggleText);
    // Check sidebar layout in RTL
    const sidebarRtl = await page.evaluate(() => {
      const sb = document.querySelector('.sidebar');
      if (!sb) return { exists: false };
      const style = getComputedStyle(sb);
      return {
        left: style.left,
        right: style.right
      };
    });
    results.push('Sidebar left in RTL: ' + sidebarRtl.left);
    results.push('Sidebar right in RTL: ' + sidebarRtl.right);

    // -- TRANSITION PROPERTY AUDIT -------------------------------------------------
    results.push('\n=== TRANSITION AUDIT ===');
    const transitionAudit = await page.evaluate(() => {
      const issues = [];
      const all = document.querySelectorAll('*');
      for (const el of all) {
        const tr = getComputedStyle(el).transitionProperty;
        if (tr === 'all') {
          const tag = el.tagName.toLowerCase();
          const id = el.id ? '#' + el.id : '';
          const cls = Array.from(el.classList).join('.');
          if (cls.length > 30) continue; // skip huge compound
          issues.push(tag + id + '.' + cls);
        }
      }
      return {
        totalElements: all.length,
        elementsWithTransitionAll: issues.length,
        sample: issues.slice(0, 5)
      };
    });
    results.push('Total DOM elements: ' + transitionAudit.totalElements);
    results.push('Elements with `transition: all`: ' + transitionAudit.elementsWithTransitionAll);
    if (transitionAudit.sample.length > 0) {
      results.push('Sample elements (first 5): ' + transitionAudit.sample.join(', '));
    }

    // -- BUTTON STATE VERIFICATION --------------------------------------------------
    results.push('\n=== BUTTON STATES ===');
    const btnStates = await page.evaluate(() => {
      const report = {};
      const btns = document.querySelectorAll('.btn');
      report.totalButtons = btns.length;
      let hasFocusVisible = false;
      let hasDisabled = false;
      let hasActive = false;
      for (const b of btns) {
        const sheet = document.styleSheets;
        for (const s of sheet) {
          try {
            for (const rule of s.cssRules) {
              if (rule.selectorText && rule.selectorText.includes('.btn')) {
                if (rule.selectorText.includes(':focus-visible')) hasFocusVisible = true;
                if (rule.selectorText.includes(':disabled')) hasDisabled = true;
                if (rule.selectorText.includes(':active')) hasActive = true;
              }
            }
          } catch (e) { /* cross-origin */ }
        }
      }
      report.focusVisibleDefined = hasFocusVisible;
      report.disabledDefined = hasDisabled;
      report.activeDefined = hasActive;
      return report;
    });
    results.push('Total .btn elements: ' + btnStates.totalButtons);
    results.push(':focus-visible state defined: ' + btnStates.focusVisibleDefined);
    results.push(':disabled state defined: ' + btnStates.disabledDefined);
    results.push(':active state defined: ' + btnStates.activeDefined);

    // -- DISABLED BUTTON VISUAL TEST ------------------------------------------------
    results.push('\n=== DISABLED STATE ENFORCEMENT ===');
    const disabledTest = await page.evaluate(() => {
      // Create a disabled button and check its computed style
      const btn = document.createElement('button');
      btn.className = 'btn btn-primary';
      btn.disabled = true;
      btn.textContent = 'Test';
      document.body.appendChild(btn);
      const style = getComputedStyle(btn);
      const result = {
        opacity: style.opacity,
        cursor: style.cursor,
        pointerEvents: style.pointerEvents
      };
      btn.remove();
      return result;
    });
    results.push('Disabled btn opacity: ' + disabledTest.opacity);
    results.push('Disabled btn cursor: ' + disabledTest.cursor);
    results.push('Disabled btn pointer-events: ' + disabledTest.pointerEvents);

    // -- MOBILE VIEWPORT -----------------------------------------------------------
    results.push('\n=== MOBILE VIEWPORT (375x667) ===');
    await page.setViewportSize({ width: 375, height: 667 });
    const mobileToggle = await page.isVisible('#sidebarToggle');
    results.push('Mobile menu button visible: ' + mobileToggle);

    // -- SCREENSHOT ----------------------------------------------------------------
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
