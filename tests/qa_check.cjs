const { chromium } = require('playwright');

(async () => {
  console.log('Starting QA Check...');
  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    // 1. Visit Login
    console.log('Navigating to Login...');
    await page.goto('http://127.0.0.1:8000/login');
    
    // 2. Login
    console.log('Attempting Login...');
    await page.fill('input[name="username"]', 'admin');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');

    // 3. Verify Dashboard
    await page.waitForTimeout(2000); // Wait for redirect
    console.log('Checking Dashboard...');
    const url = page.url();
    if (!url.includes('/dashboard')) {
      throw new Error(`Login failed. Current URL: ${url}`);
    }
    console.log('Login Successful!');

    // 4. Check Navigation Links
    const checkPage = async (name, path) => {
        console.log(`Checking ${name} page...`);
        await page.goto(`http://127.0.0.1:8000${path}`);
        await page.waitForLoadState('domcontentloaded');
        const title = await page.title();
        console.log(`- ${name}: Loaded (Title: ${title})`);
        
        // Screenshot for verification
        await page.screenshot({ path: `tests/screenshot_${name.toLowerCase()}.png` });
        console.log(`- ${name}: Screenshot saved.`);
        
        // Basic element check
        if(name === 'Patients') {
            const table = await page.$('.table');
            if(!table) console.error('! Patterns table NOT found');
            else console.log('- Patients table found.');
        }
    };

    await checkPage('Patients', '/patients');
    await checkPage('Doctors', '/doctors');
    await checkPage('Services', '/services');
    await checkPage('Settings', '/settings');

    console.log('QA Check Completed Successfully.');

  } catch (error) {
    console.error('QA Check Failed:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
