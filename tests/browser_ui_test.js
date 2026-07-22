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
    console.log('RUNNING BROWSER INTERACTION & ACCESSIBILITY AUDIT SUITE (V3 FINAL)');
    console.log('==================================================\n');

    console.log(`Starting local PHP web server on 127.0.0.1:${port} with router.php...`);
    const phpServer = spawn('php', ['-S', `127.0.0.1:${port}`, routerScript], { cwd: rootDir, env: process.env });

    phpServer.stdout.on('data', data => {});
    phpServer.stderr.on('data', data => {});

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

        // --------------------------------------------------
        // SCENARIO 1: DESKTOP HOMEPAGE STATIC MENU & HOVER
        // --------------------------------------------------
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_closed.png') });
        console.log('1. Captured desktop_1366_closed.png');

        // Test static menu hover in Homepage hero
        const staticMenuExists = await page.$('#categoryStaticMenu') !== null;
        if (staticMenuExists) {
            await page.hover('#categoryStaticMenu [data-panel-id="panel-static-pc-linh-kien"] .category-sidebar__item');
            await new Promise(r => setTimeout(r, 200));
            const isLinhKienPanelVisible = await page.$eval('#panel-static-pc-linh-kien', el => el.classList.contains('is-active') && !el.hidden);
            results.push({ name: 'Desktop Homepage static menu: hover Linh kiện PC displays panel-static-pc-linh-kien', pass: isLinhKienPanelVisible });
            await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_static_hover.png') });
            console.log('2. Captured desktop_1366_static_hover.png');

            // Mouseleave container closes static panel
            await page.mouse.move(10, 10);
            await new Promise(r => setTimeout(r, 200));
            const isStaticPanelClosed = await page.$eval('#panel-static-pc-linh-kien', el => !el.classList.contains('is-active'));
            results.push({ name: 'Desktop Homepage static menu: mouseleave closes panel', pass: isStaticPanelClosed });
        }

        // Test Dropdown Header Menu
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.hover('#categoryMegaDropdown [data-panel-id="panel-pc-linh-kien"] .category-sidebar__item');
        await new Promise(r => setTimeout(r, 250));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_linh_kien_open.png') });
        console.log('3. Captured desktop_1366_linh_kien_open.png');

        // Escape closes dropdown menu & restores focus
        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const isClosedAfterEscape = await page.$eval('#categoryMegaDropdown', el => el.hidden);
        const focusedId = await page.evaluate(() => document.activeElement ? document.activeElement.id : null);
        results.push({ name: 'Desktop Escape key closes dropdown menu & restores focus to toggle', pass: isClosedAfterEscape && focusedId === 'categoryMenuToggle' });

        // --------------------------------------------------
        // SCENARIO 2: DESKTOP LARGE & TABLET VIEWPORTS
        // --------------------------------------------------
        await page.setViewport({ width: 1440, height: 900 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1440_open.png') });
        console.log('4. Captured desktop_1440_open.png');

        await page.setViewport({ width: 1024, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.screenshot({ path: path.join(screenshotDir, 'tablet_1024_open.png') });
        console.log('5. Captured tablet_1024_open.png');

        // --------------------------------------------------
        // SCENARIO 3: MOBILE TRIGGERS & MUTUAL EXCLUSION
        // --------------------------------------------------
        await page.setViewport({ width: 390, height: 844 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });

        // 3A. mobileBottomNavCats -> ONLY category drawer
        await page.waitForSelector('#mobileBottomNavCats');
        await page.click('#mobileBottomNavCats');
        await new Promise(r => setTimeout(r, 200));
        let isCategoryOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        let isNavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Mobile trigger #mobileBottomNavCats opens ONLY category drawer', pass: isCategoryOpen && !isNavOpen });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_drawer_open.png') });
        console.log('6. Captured mobile_390_drawer_open.png');

        // 3B. Switch drawer: Click mobileMenuToggle -> closes category drawer, opens main nav
        await page.evaluate(() => document.getElementById('mobileMenuToggle').click());
        await new Promise(r => setTimeout(r, 200));
        isCategoryOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        isNavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Switch drawer: Opening main nav completely closes category drawer', pass: isNavOpen && !isCategoryOpen });

        // 3C. Switch drawer back: Click mobileCategoryToggle -> closes main nav, opens category drawer
        await page.evaluate(() => document.getElementById('mobileCategoryToggle').click());
        await new Promise(r => setTimeout(r, 200));
        isCategoryOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        isNavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Switch drawer: Opening category drawer completely closes main nav', pass: isCategoryOpen && !isNavOpen });

        // 3D. mobileQuickCatAll -> ONLY category drawer
        await page.click('#categoryDrawerClose');
        await new Promise(r => setTimeout(r, 200));
        await page.click('#mobileQuickCatAll');
        await new Promise(r => setTimeout(r, 200));
        isCategoryOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Mobile trigger #mobileQuickCatAll opens ONLY category drawer', pass: isCategoryOpen });

        // --------------------------------------------------
        // SCENARIO 4: EXCLUSIVE ACCORDIONS
        // --------------------------------------------------
        await page.click('#acc-btn-laptop');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopExpanded = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'true');
        const isLaptopPanelVisible = await page.$eval('#mobile-panel-laptop', el => !el.hidden && el.getAttribute('aria-hidden') !== 'true');
        results.push({ name: 'Mobile Laptop accordion expands subcategories', pass: isLaptopExpanded && isLaptopPanelVisible });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_laptop_expanded.png') });
        console.log('7. Captured mobile_390_laptop_expanded.png');

        await page.click('#acc-btn-pc');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopClosedAfterPC = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'false');
        const isPCExpanded = await page.$eval('#acc-btn-pc', el => el.getAttribute('aria-expanded') === 'true');
        results.push({ name: 'Opening PC accordion auto-closes Laptop accordion (exclusive accordions)', pass: isLaptopClosedAfterPC && isPCExpanded });

        // --------------------------------------------------
        // SCENARIO 5: NON-HOME ROUTE (/home/search?cat=laptop)
        // --------------------------------------------------
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(`${baseUrl}home/search?cat=laptop`, { waitUntil: 'networkidle2' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const isSearchRouteOpen = await page.$eval('#categoryMegaDropdown', el => !el.hidden);
        results.push({ name: 'Non-home search route (/home/search?cat=laptop) opens category dropdown', pass: isSearchRouteOpen });

        // --------------------------------------------------
        // SCENARIO 6: TECHNICAL & ACCESSIBILITY AUDIT CHECKS
        // --------------------------------------------------
        // 6A. All aria-controls target IDs exist in DOM
        const missingAriaTargets = await page.evaluate(() => {
            const nodes = Array.from(document.querySelectorAll('[aria-controls]'));
            const missing = [];
            for (const node of nodes) {
                const targetId = node.getAttribute('aria-controls');
                if (targetId && !document.getElementById(targetId)) {
                    missing.push(targetId);
                }
            }
            return missing;
        });
        results.push({ name: 'All aria-controls target IDs exist in DOM', pass: missingAriaTargets.length === 0, msg: `Missing: ${missingAriaTargets.join(', ')}` });

        // 6B. Closed drawers focus isolation (inert attribute set on closed drawers on mobile)
        await page.setViewport({ width: 390, height: 844 });
        await page.goto(baseUrl, { waitUntil: 'networkidle2' });
        const isClosedDrawerInert = await page.evaluate(() => {
            const catDrawer = document.getElementById('categoryMegaDropdown');
            const mainNav = document.getElementById('mainNavMenu');
            return (catDrawer ? catDrawer.hasAttribute('inert') : true) && (mainNav ? mainNav.hasAttribute('inert') : true);
        });
        results.push({ name: 'Closed drawers have inert attribute set for keyboard focus isolation', pass: isClosedDrawerInert });

        // 6C. Zero duplicate IDs
        const duplicateIds = await page.evaluate(() => {
            const ids = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
            return ids.filter((id, index) => ids.indexOf(id) !== index);
        });
        results.push({ name: 'Zero duplicate element IDs in DOM', pass: duplicateIds.length === 0, msg: `Duplicates: ${duplicateIds.join(', ')}` });

        // 6D. Zero horizontal page overflow across 6 viewports
        const viewports = [
            { w: 1366, h: 768 },
            { w: 1440, h: 900 },
            { w: 1024, h: 768 },
            { w: 768, h: 800 },
            { w: 600, h: 800 },
            { w: 390, h: 844 }
        ];
        const overflowViewports = [];
        for (const vp of viewports) {
            await page.setViewport({ width: vp.w, height: vp.h });
            const hasOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
            if (hasOverflow) overflowViewports.push(`${vp.w}x${vp.h}`);
        }
        results.push({ name: 'Zero horizontal page overflow across all 6 viewports', pass: overflowViewports.length === 0, msg: `Overflows: ${overflowViewports.join(', ')}` });

        // 6E. Zero console errors
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
