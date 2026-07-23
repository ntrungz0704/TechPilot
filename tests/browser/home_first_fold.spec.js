/**
 * CHECKPOINT 3 — First-fold layout geometry test
 * Viewport: 1366x768, scrollY=0
 * Gate: featuresBar.getBoundingClientRect().bottom <= 764
 */
const puppeteer = require('puppeteer');

const VIEWPORT = { width: 1366, height: 768 };
const BASE_URL = process.env.TEST_URL || 'http://localhost:8000';

async function run() {
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const errors = [];
  
  page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  page.on('pageerror', err => errors.push(err.message));

  let failed = false;

  try {
    await page.setViewport(VIEWPORT);
    await page.goto(BASE_URL, { waitUntil: 'networkidle2', timeout: 30000 });
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise(r => setTimeout(r, 1000));

    // 1. Verify scrollY === 0
    const scrollY = await page.evaluate(() => window.scrollY);
    console.log(`window.scrollY: ${scrollY}`);
    if (scrollY !== 0) { console.log('FAIL: scrollY !== 0'); failed = true; }
    else { console.log('PASS: scrollY === 0'); }

    // 2. Verify visible sections
    const sections = await page.evaluate(() => {
      const selectors = {
        Topbar: 'header .topbar, .topbar, [class*=topbar]',
        MainHeader: 'header, .main-header, [class*=header]',
        MainNavigation: 'nav, .main-nav, [class*=nav], #categoryMenu',
        Hero: '.hero, [class*=hero], #hero, .home-hero',
        FeaturesBar: '.features, [class*=features], #features, .features-bar'
      };
      const results = {};
      for (const [name, sel] of Object.entries(selectors)) {
        const el = document.querySelector(sel);
        if (!el) { results[name] = { found: false }; continue; }
        const rect = el.getBoundingClientRect();
        const isVisible = rect.top < 768 && rect.bottom > 0 && rect.width > 0 && rect.height > 0;
        results[name] = { found: true, top: rect.top, bottom: rect.bottom, width: rect.width, height: rect.height, fullyVisible: rect.top >= -1 && rect.bottom <= 770 };
      }
      return results;
    });

    for (const [name, info] of Object.entries(sections)) {
      if (!info.found) { console.log(`FAIL: ${name} not found`); failed = true; }
      else if (!info.fullyVisible) { 
        console.log(`FAIL: ${name} not fully visible (top=${info.top}, bottom=${info.bottom})`); 
        failed = true; 
      }
      else { console.log(`PASS: ${name} fully visible`); }
    }

    // 3. Verify FeaturesBar gate
    if (sections.FeaturesBar && sections.FeaturesBar.found) {
      const fbBottom = sections.FeaturesBar.bottom;
      console.log(`FeaturesBar bottom: ${fbBottom}, gate: <= 764`);
      if (fbBottom > 764) { console.log('FAIL: FeaturesBar exceeds gate'); failed = true; }
      else { console.log('PASS: FeaturesBar within gate'); }
    }

    // 4. Horizontal overflow
    const overflow = await page.evaluate(() => {
      const body = document.documentElement || document.body;
      return body.scrollWidth - body.clientWidth;
    });
    console.log(`Horizontal overflow: ${overflow}px`);
    if (overflow > 0) { console.log('FAIL: horizontal overflow detected'); failed = true; }
    else { console.log('PASS: zero horizontal overflow'); }

    // 5. Console errors
    if (errors.length > 0) {
      console.log('FAIL: console/page errors detected');
      errors.forEach(e => console.log(`  ERROR: ${e}`));
      failed = true;
    } else {
      console.log('PASS: zero console/page errors');
    }

  } catch (e) {
    console.log(`FAIL: test exception: ${e.message}`);
    failed = true;
  } finally {
    await browser.close();
  }

  console.log(failed ? 'OVERALL: FAIL' : 'OVERALL: PASS');
  process.exit(failed ? 1 : 0);
}

run();
