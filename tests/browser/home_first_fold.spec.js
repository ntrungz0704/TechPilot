/**
 * CHECKPOINT 3 — First-fold layout geometry test
 * Viewport: 1366x768, scrollY=0
 * Gate: featuresBar.getBoundingClientRect().bottom <= 764
 *
 * EXECUTION CONTRACT:
 *   - MUST be invoked via serve-and-test.sh (server lifecycle wrapper).
 *   - MUST NOT start or kill any server.
 *   - Reads TEST_URL environment variable only.
 *   - Writes evidence to checkpoints/CP03/evidence/geometry-gate.json
 *     and checkpoints/CP03/evidence/homepage-1366x768.png
 */
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const VIEWPORT = { width: 1366, height: 768 };
const BASE_URL = process.env.TEST_URL;

if (!BASE_URL) {
  console.error('FAIL: TEST_URL is not set — this test must be run via serve-and-test.sh');
  process.exit(1);
}

const EVIDENCE_DIR = path.resolve(__dirname, '../../checkpoints/CP03/evidence');

async function run() {
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const consoleErrors = [];
  const pageErrors = [];

  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
  page.on('pageerror', err => pageErrors.push(err.message));

  let failed = false;
  const measurements = {};

  try {
    // Ensure evidence directory exists
    fs.mkdirSync(EVIDENCE_DIR, { recursive: true });

    await page.setViewport(VIEWPORT);
    await page.goto(BASE_URL, { waitUntil: 'networkidle2', timeout: 30000 });
    await page.evaluate(() => window.scrollTo(0, 0));
    await new Promise(r => setTimeout(r, 1000));

    // Take screenshot
    await page.screenshot({ path: path.join(EVIDENCE_DIR, 'homepage-1366x768.png'), fullPage: false });
    console.log('OK: Screenshot captured');

    // 1. Verify scrollY === 0
    measurements.scrollY = await page.evaluate(() => window.scrollY);
    console.log(`window.scrollY: ${measurements.scrollY}`);
    if (measurements.scrollY !== 0) { console.log('FAIL: scrollY !== 0'); failed = true; }
    else { console.log('PASS: scrollY === 0'); }

    // 2. Verify visible sections using exact DOM selectors
    measurements.sections = await page.evaluate(() => {
      const selectors = {
        Topbar: '.techpilot-topbar',
        MainHeader: 'header.site-header',
        MainNavigation: 'nav.main-nav',
        HeroContainer: 'section.container.hero-section',
        HeroLeftColumn: '.hero-section__left',
        HeroCenterColumn: '.hero-section__center',
        HeroRightColumn: '.hero-section__right',
        FeaturesBar: '.features-bar'
      };
      const results = {};
      for (const [name, sel] of Object.entries(selectors)) {
        const el = document.querySelector(sel);
        if (!el) { results[name] = { found: false, selector: sel }; continue; }
        const rect = el.getBoundingClientRect();
        results[name] = {
          found: true,
          selector: sel,
          top: Math.round(rect.top),
          bottom: Math.round(rect.bottom),
          width: Math.round(rect.width),
          height: Math.round(rect.height),
          fullyVisible: rect.top >= -1 && rect.bottom <= 770
        };
      }
      return results;
    });

    for (const [name, info] of Object.entries(measurements.sections)) {
      if (!info.found) {
        console.log(`FAIL: ${name} not found (selector: ${info.selector})`);
        failed = true;
      } else if (!info.fullyVisible) {
        console.log(`FAIL: ${name} not fully visible (top=${info.top}, bottom=${info.bottom})`);
        failed = true;
      } else {
        console.log(`PASS: ${name} fully visible (top=${info.top}, bottom=${info.bottom})`);
      }
    }

    // 3. Verify FeaturesBar gate
    if (measurements.sections.FeaturesBar && measurements.sections.FeaturesBar.found) {
      measurements.featuresBarBottom = measurements.sections.FeaturesBar.bottom;
      console.log(`FeaturesBar bottom: ${measurements.featuresBarBottom}, gate: <= 764`);
      if (measurements.featuresBarBottom > 764) {
        console.log('FAIL: FeaturesBar exceeds gate');
        failed = true;
      } else {
        console.log('PASS: FeaturesBar within gate');
      }
    }

    // 4. Horizontal overflow
    measurements.horizontalOverflow = await page.evaluate(() => {
      return (document.documentElement || document.body).scrollWidth -
             (document.documentElement || document.body).clientWidth;
    });
    console.log(`Horizontal overflow: ${measurements.horizontalOverflow}px`);
    if (measurements.horizontalOverflow > 0) {
      console.log('FAIL: horizontal overflow detected');
      failed = true;
    } else {
      console.log('PASS: zero horizontal overflow');
    }

    // 5. Console and page errors
    measurements.consoleErrorCount = consoleErrors.length;
    measurements.pageErrorCount = pageErrors.length;
    if (measurements.consoleErrorCount > 0 || measurements.pageErrorCount > 0) {
      console.log('FAIL: errors detected');
      consoleErrors.forEach(e => console.log(`  CONSOLE: ${e}`));
      pageErrors.forEach(e => console.log(`  PAGE: ${e}`));
      failed = true;
    } else {
      console.log('PASS: zero errors');
    }

  } catch (e) {
    console.log(`FAIL: test exception: ${e.message}`);
    failed = true;
  } finally {
    await browser.close();
  }

  // Write measurements evidence
  const evidence = {
    timestamp: new Date().toISOString(),
    viewport: VIEWPORT,
    url: BASE_URL,
    overallPass: !failed,
    measurements: measurements,
    consoleErrors: consoleErrors,
    pageErrors: pageErrors
  };
  fs.writeFileSync(
    path.join(EVIDENCE_DIR, 'geometry-gate.json'),
    JSON.stringify(evidence, null, 2)
  );
  console.log('OK: Evidence written to geometry-gate.json');

  console.log(failed ? 'OVERALL: FAIL' : 'OVERALL: PASS');
  process.exit(failed ? 1 : 0);
}

run();
