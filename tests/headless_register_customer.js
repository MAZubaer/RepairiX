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
    await page.goto('http://127.0.0.1:8000/register/customer', {waitUntil: 'networkidle2'});
    const cookies = await page.cookies();
    const xsrfCookie = cookies.find(c => c.name === 'XSRF-TOKEN');
    const xsrf = xsrfCookie ? decodeURIComponent(xsrfCookie.value) : null;
    console.log('XSRF cookie present:', !!xsrfCookie);

    const rand = Math.floor(Math.random()*100000);
    // wait for the SPA to render inputs
    await page.waitForSelector('#app input, #app textarea, #app select', { timeout: 10000 });
    // collect inputs inside app and fill by expected order if explicit names are absent
    const inputs = await page.$$eval('#app input, #app textarea', els => els.map(e => ({ name: e.getAttribute('name'), placeholder: e.getAttribute('placeholder') })));
    console.log('Found inputs:', inputs.map(i => i.placeholder || i.name));
    // prefer named selectors, otherwise fill by order
    const typeIfExists = async (selector, value, indexFallback) => {
      try {
        if (selector) {
          await page.type(selector, value);
          return true;
        }
      } catch (e) {}
      // fallback by index
      const els = await page.$$('#app input, #app textarea');
      if (els[indexFallback]) {
        await els[indexFallback].focus();
        await els[indexFallback].type(value);
        return true;
      }
      return false;
    };

    // Attempt to type by name attribute where possible, otherwise by the common field order
    await typeIfExists('input[name="name"]', 'PuppeteerCustomer', 0);
    await typeIfExists('input[name="email"]', 'puppeteerc'+rand+'@example.com', 1);
    await typeIfExists('input[name="phone"]', '0192'+(600000+rand), 2);
    await typeIfExists('input[name="location"]', 'Somewhere', 3);
    await typeIfExists('input[name="primary_device"]', 'Phone', 4);
    await typeIfExists('input[name="password"]', 'password123', 5);
    await typeIfExists('input[name="password_confirmation"]', 'password123', 6);

    await Promise.all([
      page.click('button[type="submit"]'),
      page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => {})
    ]);
    const after = await page.content();
    console.log('after submit content length', after.length);
    console.log('after submit preview:\n' + after.slice(0,800));
    await new Promise(r => setTimeout(r, 1000));
  } catch (e) {
    console.error(e);
  } finally {
    await browser.close();
  }
})();
