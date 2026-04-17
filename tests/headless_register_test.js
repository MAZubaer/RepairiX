import puppeteer from 'puppeteer';
import fs from 'fs';

(async () => {
  const chromePaths = [
    'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe'
  ];
  let executablePath;
  for (const p of chromePaths) {
    if (fs.existsSync(p)) { executablePath = p; break; }
  }
  const launchOpts = { headless: true, args: ['--no-sandbox','--disable-setuid-sandbox'] };
  if (executablePath) launchOpts.executablePath = executablePath;
  const browser = await puppeteer.launch(launchOpts);
  const page = await browser.newPage();
  page.on('console', msg => console.log('PAGE LOG:', msg.text()));

  try {
    await page.goto('http://127.0.0.1:8000/register/shop', {waitUntil: 'networkidle2'});
    // Extract CSRF cookie and send decoded token header (like axios does)
    const cookies = await page.cookies();
    const xsrfCookie = cookies.find(c => c.name === 'XSRF-TOKEN');
    const xsrf = xsrfCookie ? decodeURIComponent(xsrfCookie.value) : null;
    console.log('XSRF cookie present:', !!xsrfCookie);

    // Fill inputs as a user would and click submit so Inertia handles the request
    const rand = Math.floor(Math.random()*100000);
    await page.waitForSelector('input[placeholder="Enter your shop name"]', { timeout: 5000 });
    await page.type('input[placeholder="Enter your shop name"]', 'PuppeteerShop');
    await page.type('input[placeholder="Enter your business email"]', 'puppeteer'+rand+'@example.com');
    await page.type('input[placeholder="Enter your phone number"]', '01811'+(10000+rand));
    await page.type('input[placeholder="Enter your shop address"]', 'Addr');
    await page.type('input[placeholder="Create a password"]', 'password123');
    await page.type('input[placeholder="Confirm your password"]', 'password123');
    await Promise.all([
      page.click('button[type="submit"]'),
      page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {})
    ]);
    const after = await page.content();
    console.log('after submit content length', after.length);
    console.log('after submit preview:\n' + after.slice(0,800));

    // Wait a bit
    await new Promise(r => setTimeout(r, 1000));
  } catch (e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
