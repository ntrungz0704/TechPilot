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
    console.log('RUNNING BROWSER INTERACTION & ACCESSIBILITY AUDIT SUITE (V4 FINAL)');
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
        // SECTION 1: VIEWPORT AUDITS & BREAKPOINT CONTRACTS
        // --------------------------------------------------

        // 1A. Viewport 1366x768 (Desktop mode > 1024px)
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_closed.png') });

        const d1366MainNavVisible = await page.evaluate(() => {
            const el = document.getElementById('mainNavMenu');
            if (!el) return false;
            const style = window.getComputedStyle(el);
            return style.display !== 'none' && style.visibility !== 'hidden';
        });
        const d1366MainNavAriaHidden = await page.$eval('#mainNavMenu', el => el.getAttribute('aria-hidden'));
        const d1366MainNavInert = await page.$eval('#mainNavMenu', el => el.hasAttribute('inert'));
        const d1366ToggleHidden = await page.evaluate(() => {
            const btn = document.getElementById('mobileMenuToggle');
            if (!btn) return true;
            const style = window.getComputedStyle(btn);
            return style.display === 'none';
        });

        results.push({
            name: '1366x768 Desktop: mainNavMenu visible, no aria-hidden, no inert, mobileMenuToggle hidden',
            pass: d1366MainNavVisible && (d1366MainNavAriaHidden === null || d1366MainNavAriaHidden === 'false') && !d1366MainNavInert && d1366ToggleHidden
        });

        // 1B. Viewport 1024x768 (Drawer mode <= 1024px)
        await page.setViewport({ width: 1024, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
        await page.screenshot({ path: path.join(screenshotDir, 'tablet_1024_open.png') });

        const v1024ToggleVisible = await page.evaluate(() => {
            const btn = document.getElementById('mobileMenuToggle');
            if (!btn) return false;
            const style = window.getComputedStyle(btn);
            return style.display !== 'none';
        });
        const v1024MainNavClosed = await page.evaluate(() => {
            const el = document.getElementById('mainNavMenu');
            if (!el) return false;
            return el.getAttribute('aria-hidden') === 'true' && el.hasAttribute('inert') && !el.classList.contains('is-mobile-open');
        });

        // Click hamburger at 1024x768
        await page.click('#mobileMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const v1024NavOpen = await page.evaluate(() => {
            const el = document.getElementById('mainNavMenu');
            return el.classList.contains('is-mobile-open') && el.getAttribute('aria-hidden') === 'false' && !el.hasAttribute('inert');
        });

        // Escape closes hamburger & restores focus
        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const v1024NavClosedEscape = await page.$eval('#mainNavMenu', el => el.getAttribute('aria-hidden') === 'true' && el.hasAttribute('inert'));
        const v1024FocusRestored = await page.evaluate(() => document.activeElement ? document.activeElement.id : null);

        results.push({
            name: '1024x768 Breakpoint: mobileMenuToggle visible, mainNavMenu closed with aria-hidden & inert, hamburger opens nav, Escape closes & restores focus',
            pass: v1024ToggleVisible && v1024MainNavClosed && v1024NavOpen && v1024NavClosedEscape && v1024FocusRestored === 'mobileMenuToggle'
        });

        // 1C. Viewport 768x800 (Drawer mode <= 1024px)
        await page.setViewport({ width: 768, height: 800 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

        const v768ToggleVisible = await page.evaluate(() => {
            const btn = document.getElementById('mobileMenuToggle');
            return btn && window.getComputedStyle(btn).display !== 'none';
        });
        const v768NavClosed = await page.evaluate(() => {
            const el = document.getElementById('mainNavMenu');
            return el && el.getAttribute('aria-hidden') === 'true' && el.hasAttribute('inert');
        });
        await page.click('#mobileMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const v768NavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open') && el.getAttribute('aria-hidden') === 'false');
        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const v768NavEscape = await page.$eval('#mainNavMenu', el => el.getAttribute('aria-hidden') === 'true');

        results.push({
            name: '768x800 Breakpoint: same drawer contract as 1024px (toggle visible, closed with aria-hidden/inert, opens on click, Escape closes)',
            pass: v768ToggleVisible && v768NavClosed && v768NavOpen && v768NavEscape
        });

        // 1D. Viewport 600x800 (Drawer mode <= 1024px)
        await page.setViewport({ width: 600, height: 800 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

        const v600ToggleVisible = await page.evaluate(() => {
            const btn = document.getElementById('mobileMenuToggle');
            return btn && window.getComputedStyle(btn).display !== 'none';
        });
        const v600NavClosed = await page.evaluate(() => {
            const el = document.getElementById('mainNavMenu');
            return el && el.getAttribute('aria-hidden') === 'true' && el.hasAttribute('inert');
        });
        await page.click('#mobileMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const v600NavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open') && el.getAttribute('aria-hidden') === 'false');
        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const v600NavEscape = await page.$eval('#mainNavMenu', el => el.getAttribute('aria-hidden') === 'true');

        results.push({
            name: '600x800 Breakpoint: same drawer contract as 1024px',
            pass: v600ToggleVisible && v600NavClosed && v600NavOpen && v600NavEscape
        });

        // 1E. Viewport 1440x900 (Large Desktop)
        await page.setViewport({ width: 1440, height: 900 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1440_open.png') });

        // --------------------------------------------------
        // SECTION 2: INTERACTION TESTS (DESKTOP & STATIC MENU)
        // --------------------------------------------------
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

        // 2A. Keyboard focus on static menu row opens static panel
        const staticMenuExists = await page.$('#categoryStaticMenu') !== null;
        if (staticMenuExists) {
            await page.focus('#categoryStaticMenu [data-panel-id="panel-static-pc-linh-kien"] .category-sidebar__item');
            await new Promise(r => setTimeout(r, 200));
            const isLinhKienPanelVisibleOnFocus = await page.$eval('#panel-static-pc-linh-kien', el => el.classList.contains('is-active') && !el.hidden);
            results.push({ name: 'Keyboard focus on static menu row opens panel-static-pc-linh-kien', pass: isLinhKienPanelVisibleOnFocus });

            // 2B. Hover from static sidebar item onto static mega panel keeps panel open
            await page.hover('#categoryStaticMenu [data-panel-id="panel-static-pc-linh-kien"] .category-sidebar__item');
            await new Promise(r => setTimeout(r, 150));
            await page.hover('#panel-static-pc-linh-kien');
            await new Promise(r => setTimeout(r, 150));
            const isPanelStillOpen = await page.$eval('#panel-static-pc-linh-kien', el => el.classList.contains('is-active') && !el.hidden);
            results.push({ name: 'Hovering from static sidebar onto mega panel area keeps panel open', pass: isPanelStillOpen });
            await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_static_hover.png') });

            // Mouseleave container closes static panel
            await page.mouse.move(10, 10);
            await new Promise(r => setTimeout(r, 200));
            const isStaticPanelClosed = await page.$eval('#panel-static-pc-linh-kien', el => !el.classList.contains('is-active'));
            results.push({ name: 'Mouseleave static menu container closes panel', pass: isStaticPanelClosed });
        }

        // 2C. Category Dropdown open & Escape restores trigger focus
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        await page.hover('#categoryMegaDropdown [data-panel-id="panel-pc-linh-kien"] .category-sidebar__item');
        await new Promise(r => setTimeout(r, 250));
        await page.screenshot({ path: path.join(screenshotDir, 'desktop_1366_linh_kien_open.png') });

        await page.keyboard.press('Escape');
        await new Promise(r => setTimeout(r, 150));
        const isCatDropdownClosedEscape = await page.$eval('#categoryMegaDropdown', el => el.hidden);
        const focusedIdCatEscape = await page.evaluate(() => document.activeElement ? document.activeElement.id : null);
        results.push({ name: 'Escape key closes category dropdown & restores focus to categoryMenuToggle', pass: isCatDropdownClosedEscape && focusedIdCatEscape === 'categoryMenuToggle' });

        // --------------------------------------------------
        // SECTION 3: MOBILE INTERACTION & ACCORDION TOGGLE
        // --------------------------------------------------
        await page.setViewport({ width: 390, height: 844 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

        // 3A. Trigger #mobileBottomNavCats opens ONLY category drawer
        await page.waitForSelector('#mobileBottomNavCats');
        await page.click('#mobileBottomNavCats');
        await new Promise(r => setTimeout(r, 200));
        let isCatDrawerOpen = await page.$eval('#categoryMegaDropdown', el => el.classList.contains('is-mobile-open'));
        let isMainNavOpen = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        results.push({ name: 'Mobile trigger #mobileBottomNavCats opens ONLY category drawer', pass: isCatDrawerOpen && !isMainNavOpen });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_drawer_open.png') });

        // 3B. Overlay click closes drawer
        await page.evaluate(() => document.querySelector('.category-overlay').click());
        await new Promise(r => setTimeout(r, 200));
        const isCatDrawerClosedOverlay = await page.$eval('#categoryMegaDropdown', el => !el.classList.contains('is-mobile-open') && el.hidden);
        results.push({ name: 'Overlay click closes category drawer', pass: isCatDrawerClosedOverlay });

        // 3C. Re-open category drawer & test Accordions
        await page.click('#mobileCategoryToggle');
        await new Promise(r => setTimeout(r, 200));

        // Click Laptop accordion 1st time -> expands
        await page.click('#acc-btn-laptop');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopExpanded1 = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'true');
        const isLaptopPanelVisible1 = await page.$eval('#mobile-panel-laptop', el => !el.hidden && el.getAttribute('aria-hidden') !== 'true');
        results.push({ name: 'Clicking Laptop accordion 1st time expands it', pass: isLaptopExpanded1 && isLaptopPanelVisible1 });
        await page.screenshot({ path: path.join(screenshotDir, 'mobile_390_laptop_expanded.png') });

        // Click Laptop accordion 2nd time -> collapses (toggle behavior)
        await page.click('#acc-btn-laptop');
        await new Promise(r => setTimeout(r, 200));
        const isLaptopCollapsed2 = await page.$eval('#acc-btn-laptop', el => el.getAttribute('aria-expanded') === 'false');
        const isLaptopPanelHidden2 = await page.$eval('#mobile-panel-laptop', el => el.hidden || el.getAttribute('aria-hidden') === 'true');
        results.push({ name: 'Clicking Laptop accordion 2nd time collapses it (toggle behavior)', pass: isLaptopCollapsed2 && isLaptopPanelHidden2 });

        // 3D. "Xem tất cả Laptop" link has cat=laptop
        await page.click('#acc-btn-laptop');
        await new Promise(r => setTimeout(r, 200));
        const viewAllLaptopHref = await page.$eval('#mobile-panel-laptop .mobile-panel__view-all', el => el.getAttribute('href'));
        results.push({ name: '"Xem tất cả Laptop" link contains cat=laptop', pass: viewAllLaptopHref && viewAllLaptopHref.includes('cat=laptop') });

        // --------------------------------------------------
        // SECTION 4: RESIZING FROM MOBILE/TABLET TO DESKTOP
        // --------------------------------------------------
        // Open main nav at 1024x768
        await page.setViewport({ width: 1024, height: 768 });
        await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });
        await page.click('#mobileMenuToggle');
        await new Promise(r => setTimeout(r, 200));

        const isNavOpenBeforeResize = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        const isScrollLockedBeforeResize = await page.$eval('body', el => el.classList.contains('category-scroll-locked'));

        // Resize to Desktop 1366x768
        await page.setViewport({ width: 1366, height: 768 });
        await new Promise(r => setTimeout(r, 300));

        const isNavOpenAfterResize = await page.$eval('#mainNavMenu', el => el.classList.contains('is-mobile-open'));
        const isScrollLockedAfterResize = await page.$eval('body', el => el.classList.contains('category-scroll-locked'));
        const isNavAriaHiddenAfterResize = await page.$eval('#mainNavMenu', el => el.hasAttribute('aria-hidden'));
        const isNavInertAfterResize = await page.$eval('#mainNavMenu', el => el.hasAttribute('inert'));

        results.push({
            name: 'Resizing from drawer mode (1024px) to desktop (1366px) resets body scroll lock, removes is-mobile-open, removes aria-hidden & inert',
            pass: isNavOpenBeforeResize && isScrollLockedBeforeResize && !isNavOpenAfterResize && !isScrollLockedAfterResize && !isNavAriaHiddenAfterResize && !isNavInertAfterResize
        });

        // --------------------------------------------------
        // SECTION 5: NON-HOME ROUTE & TECHNICAL AUDITS
        // --------------------------------------------------
        await page.setViewport({ width: 1366, height: 768 });
        await page.goto(`${baseUrl}home/search?cat=laptop`, { waitUntil: 'domcontentloaded' });
        await page.click('#categoryMenuToggle');
        await new Promise(r => setTimeout(r, 200));
        const isSearchRouteOpen = await page.$eval('#categoryMegaDropdown', el => !el.hidden);
        results.push({ name: 'Non-home search route (/home/search?cat=laptop) opens category dropdown', pass: isSearchRouteOpen });

        // Technical Audit: aria-controls targets exist
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

        // Zero duplicate element IDs
        const duplicateIds = await page.evaluate(() => {
            const ids = Array.from(document.querySelectorAll('[id]')).map(el => el.id);
            return ids.filter((id, index) => ids.indexOf(id) !== index);
        });
        results.push({ name: 'Zero duplicate element IDs in DOM', pass: duplicateIds.length === 0, msg: `Duplicates: ${duplicateIds.join(', ')}` });

        // Zero horizontal page overflow across all viewports
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
        results.push({ name: 'Zero horizontal page overflow across all viewports', pass: overflowViewports.length === 0, msg: `Overflows: ${overflowViewports.join(', ')}` });

        // Zero console errors
        results.push({ name: 'Zero browser console errors during interaction', pass: consoleErrors.length === 0, msg: `Errors: ${consoleErrors.join('; ')}` });

    } finally {
        if (browser) await browser.close();
        phpServer.kill();
    }

    console.log('\n--------------------------------------------------');
    let allPassed = true;
    for (const res of results) {
        const status = res.pass ? '[PASS]' : '[FAIL]';
        console.log(`${res.name.padEnd(80)} ${status}`);
        if (!res.pass) {
            allPassed = false;
            if (res.msg) console.log(`   -> Error: ${res.msg}`);
        }
    }
    console.log('--------------------------------------------------');

    process.exit(allPassed ? 0 : 1);
})();
