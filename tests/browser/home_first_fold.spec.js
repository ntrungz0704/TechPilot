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

async function runCountdownScenario(options) {
  const name = options.name;
  const endTime = options.endTime;
  const expectedHours = options.expectedHours;
  const expectedMinutes = options.expectedMinutes;
  const expectedSeconds = options.expectedSeconds;
  const expectIntervalCalls = 0;

  console.log('=== COUNTDOWN SCENARIO: ' + name + ' ===');
  const browser = await puppeteer.launch({ headless: 'new', args: ['--no-sandbox'] });
  const page = await browser.newPage();
  const consoleErrors = [];
  const pageErrors = [];

  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
  page.on('pageerror', err => { pageErrors.push(err.message); });

  let passed = true;
  try {
    await page.setRequestInterception(true);
    page.on('request', req => {
      const url = new URL(req.url());
      if (url.pathname === '/__cp03-countdown-test') {
        req.respond({
          status: 200,
          contentType: 'text/html',
          body: '<!DOCTYPE html><html><head><meta charset="utf-8"><link rel="icon" href="data:,"></head><body>' +
            '<div id="flashCountdown" data-end-time="' + endTime + '">' +
            '<div id="cd-h">--</div><div id="cd-m">--</div><div id="cd-s">--</div>' +
            '</div>' +
            '<script>window.__countdownIntervalCalls = 0;' +
            'var __origSI = window.setInterval;' +
            'window.setInterval = function(fn, ms) {' +
            '  window.__countdownIntervalCalls++; return __origSI(fn, ms);' +
            '};</script>' +
            '</body></html>'
        });
      } else { req.continue(); }
    });

    const mainJs = fs.readFileSync(
      path.resolve(__dirname, '../../public/assets/js/main.js'), 'utf8'
    );

    const testUrl = new URL('/__cp03-countdown-test', BASE_URL).toString();
    await page.goto(testUrl, { waitUntil: 'domcontentloaded' });
    await page.addScriptTag({ content: mainJs });
    await page.evaluate(() => { document.dispatchEvent(new Event('DOMContentLoaded')); });
    await new Promise(r => setTimeout(r, 200));

    const state = await page.evaluate(() => {
      const h = document.getElementById('cd-h');
      const m = document.getElementById('cd-m');
      const s = document.getElementById('cd-s');
      return {
        hours: h ? h.textContent : '',
        minutes: m ? m.textContent : '',
        seconds: s ? s.textContent : '',
        intervalCalls: window.__countdownIntervalCalls || 0
      };
    });

    console.log(name + '_HOURS=' + state.hours);
    console.log(name + '_MINUTES=' + state.minutes);
    console.log(name + '_SECONDS=' + state.seconds);
    console.log(name + '_INTERVAL_CALLS=' + state.intervalCalls);
    console.log(name + '_CONSOLE_ERRORS=' + consoleErrors.length);
    console.log(name + '_PAGE_ERRORS=' + pageErrors.length);

    if (state.intervalCalls > 0) { console.log('FAIL: ' + name + ' created interval'); passed = false; }
    if (state.hours !== expectedHours || state.minutes !== expectedMinutes || state.seconds !== expectedSeconds) {
      console.log('FAIL: ' + name + ' expected ' + expectedHours + ':' + expectedMinutes + ':' + expectedSeconds + ' got ' + state.hours + ':' + state.minutes + ':' + state.seconds);
      passed = false;
    }
    if (consoleErrors.length > 0) { console.log('FAIL: ' + name + ' console errors'); passed = false; }
    if (pageErrors.length > 0) { console.log('FAIL: ' + name + ' page errors'); passed = false; }

    if (passed) console.log('PASS: ' + name);

    return {
      name: name,
      passed: passed,
      hours: state.hours,
      minutes: state.minutes,
      seconds: state.seconds,
      intervalCalls: state.intervalCalls,
      consoleErrors: consoleErrors.length,
      pageErrors: pageErrors.length
    };
  } finally {
    await browser.close();
  }
}

async function runExpiredCountdownTest() {
  const expiredResult = await runCountdownScenario({
    name: 'EXPIRED_COUNTDOWN',
    endTime: '2020-01-01 00:00:00',
    expectedHours: '00', expectedMinutes: '00', expectedSeconds: '00'
  });

  const invalidResult = await runCountdownScenario({
    name: 'INVALID_COUNTDOWN',
    endTime: 'not-a-valid-date',
    expectedHours: '00', expectedMinutes: '00', expectedSeconds: '00'
  });

  return {
    expired: expiredResult,
    invalid: invalidResult,
    overallPass: expiredResult.passed && invalidResult.passed
  };
}

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

  // Run expired countdown regression test
  let countdownPass = true;
  let countdownResults = null;
  try {
    countdownResults = await runExpiredCountdownTest();
    countdownPass = countdownResults.overallPass;
    console.log('EXPIRED_COUNTDOWN_TEST=' + (countdownResults.expired.passed ? 'PASS' : 'FAIL'));
    console.log('INVALID_COUNTDOWN_TEST=' + (countdownResults.invalid.passed ? 'PASS' : 'FAIL'));
  } catch (e) {
    console.log('FAIL: expired countdown test exception: ' + e.message);
    countdownPass = false;
  }
  if (!countdownPass) failed = true;

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
  if (countdownResults) {
    evidence.measurements.countdownRegression = countdownResults;
  }
  fs.writeFileSync(
    path.join(EVIDENCE_DIR, 'geometry-gate.json'),
    JSON.stringify(evidence, null, 2)
  );
  console.log('OK: Evidence written to geometry-gate.json');

  console.log(failed ? 'OVERALL: FAIL' : 'OVERALL: PASS');
  process.exit(failed ? 1 : 0);
}

run();
