const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const screenshotDir = path.join(__dirname, '../docs/reviews/catalog/screenshots');
if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

const rootDir = path.join(__dirname, '..');
const port = 8099;
const baseUrl = `http://127.0.0.1:${port}/`;
const routerScript = path.join(__dirname, 'router.php');

(async () => {
    console.log('==================================================');
    console.log('RUNNING BROWSER INTERACTION & ACCESSIBILITY AUDIT SUITE');
    console.log('==================================================\n');

    console.log(`Starting local PHP web server on 127.0.0.1:${port} with router.php...`);
    const phpServer = spawn('php', ['-S', `127.0.0.1:${port}`, routerScript], { cwd: rootDir, env: process.env });

    phpServer.stdout.on('data', data => console.log(`[PHP STDOUT] ${data.toString().trim()}`));
    phpServer.stderr.on('data', data => console.log(`[PHP STDERR] ${data.toString().trim()}`));

    await new Promise(resolve => setTimeout(resolve, 2000));

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            executablePath: 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    } catch (err) {
        console.log('Falling back to default puppeteer chromium...');
        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
    }

    const results = [];
    const consoleErrors = [];

    try {
        const page = await browser.newPage();
        page.on('console', msg => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        // TEST 1: Desktop 1366x768 - Closed Menu
        await page.setViewport({ width: 1366, height: 768 });
        try {
            await page.goto(baseUrl, { waitUntil: 'networkidle2' });
            await page.waitForSelector('#categoryMenuToggle', { timeout: 10000 });
        } catch (e) {
            console.log('FAIL navigating to home page: ' + e.message);
            const content = await page.content();
            console.log('HTML Content on Failure:\n' + content.slice(0, 1500));
            throw e;
        }
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_closed.png') });
        console.log('1. Captured desktop_1366_closed.png');

        // TEST 2: Desktop 1366x768 - Open Linh Kiện PC Mega Panel
        await page.click('#categoryMenuToggle');
        await page.waitForSelector('#categoryMegaDropdown:not([hidden])');
        await page.hover('[data-panel-id="panel-pc-linh-kien"]');
        await new Promise(r => setTimeout(r, 250));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_linh_kien_open.png') });
        console.log('2. Captured desktop_1366_linh_kien_open.png');

        // TEST 3: Escape key closes menu and restores focus
        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const isClosedAfterEscape = await page.$eval('#categoryMegaDropdown', el => el.hidden);
        const focusedId = await page.evaluate(() => document.activeElement ? document.activeElement.id : null);
        results.push({ name: 'Desktop Escape key closes menu & restores focus to toggle', pass: isClosedAfterEscape && focusedId === 'categoryMenuToggle' });

        // TEST 4: Desktop 1440x900 - Open Menu
        await page.setViewport({ width: 1440, height: 900 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1440_open.png') });
        console.log('3. Captured desktop_1440_open.png');

        // TEST 5: Tablet 1024x768 - Open Menu
        await page.setViewport({ width: 1024, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.screenshot({ path: path.join(screenshotDir, 'tablet_1024_open.png') });
        console.log('4. Captured tablet_1024_open.png');

        // TEST 6: Mobile 390x844 - Open Category Drawer
        await page.setViewport({ width: 390, height: 844 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.waitForSelector('#mobileCategoryToggle', { timeout: 10000 });
        await page.click('#mobileCategoryToggle');
        await new Promise(r => setTimeout(r, 200));
        const isMobileDrawerOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        const isBodyScrollLocked = await page.$eval('body', el => el.classList.contains('category-scroll-locked'));
        const isMainNavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Mobile category trigger opens category drawer ONLY (main nav closed, body scroll locked)', pass: isMobileDrawerOpen && isBodyScrollLocked && !isMainNavOpen });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_drawer_open.png') });
        console.log('5. Captured mobile_390_drawer_open.png');

        // TEST 7: Mobile 390x844 - Expand Laptop Accordion
        await page.click('#acc-btn-laptop');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopExpanded = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'true');
        const isLaptopPanelVisible = await page.$eval('#mobile-panel-laptop', el => !el.hidden && el.getAttribute('aria-hidden') !== 'true');
        results.push({ name: 'Mobile Laptop accordion expands subcategories', pass: isLaptopExpanded && isLaptopPanelVisible });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_laptop_expanded.png') });
        console.log('6. Captured mobile_390_laptop_expanded.png');

        // TEST 8: Expand PC Accordion auto-closes Laptop Accordion
        await page.click('#acc-btn-pc');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopClosedAfterPC = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'false');
        const isPCExpanded = await page.$eval('#acc-btn-pc', el => el.getAttribute('aria-expanded') === 'true');
        results.push({ name: 'Opening PC accordion auto-closes Laptop accordion (exclusive accordions)', pass: isLaptopClosedAfterPC && isPCExpanded });

        // TEST 9: Mobile main nav hamburger trigger opens main nav ONLY
        await page.click('#categoryDrawerClose');
        await new Promise(r => setTimeout(r, 200));
        await page.click('#mobileMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const isNavOpenOnly = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        const isCatClosed = await page.$eval('#categoryMegaDropdown', el => !el.classList.contains('is-mobile-open'));
        results.push({ name: 'Mobile hamburger trigger opens main nav ONLY (category drawer closed)', pass: isNavOpenOnly && isCatClosed });

        // TEST 10: Technical Checks - Duplicate IDs, Console Errors, Horizontal Overflow
        const duplicateIds = await page.evaluate(() => {
            const ids = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
            return ids.filter((id, index) => ids.indexOf(id) !== index);
        });
        results.push({ name: 'Zero duplicate element IDs in DOM', pass: duplicateIds.length === 0, msg: `Duplicates: ${duplicateIds.join(', ')}` });

        const overflowViewports = [];
        for (const vp of [{ w: 1366, h: 768 }, { w: 1440, h: 900 }, { w: 1024, h: 768 }, { w: 390, h: 844 }]) {
            await page.setViewport({ width: vp.w, height: vp.h });
            const hasOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
            if (hasOverflow) overflowViewports.push(`${vp.w}x${vp.h}`);
        }
        results.push({ name: 'Zero horizontal page overflow across all 4 viewports', pass: overflowViewports.length === 0, msg: `Overflows: ${overflowViewports.join(', ')}` });

        results.push({ name: 'Zero browser console errors during interaction', pass: consoleErrors.length === 0, msg: `Errors: ${consoleErrors.join('; ')}` });

    } finally {
        if (browser) await browser.close();
        phpServer.kill();
    }

    console.log('\n--------------------------------------------------');
    let allPassed = true;
    for (const res of results) {
        const status = res.pass ? '[PASS]' : '[FAIL]';
        console.log(`${res.name.padEnd(68)} ${status}`);
        if (!res.pass) {
            allPassed = false;
            if (res.msg) console.log(`   -> Error: ${res.msg}`);
        }
    }
    console.log('--------------------------------------------------');

    process.exit(allPassed ? 0 : 1);
})();
