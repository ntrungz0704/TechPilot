'use strict';

const puppeteer = require('puppeteer');
const fs = require('fs');
const http = require('http');

async function runTest() {
    let passed = 0;
    let failed = 0;
    let pageErrors = 0;

    function log(msg) {
        console.log(msg);
    }

    function assert(condition, message) {
        if (condition) {
            passed++;
            log(`[PASS] ${message}`);
        } else {
            failed++;
            log(`[FAIL] ${message}`);
        }
    }

    // Shared mock state managed server-side
    let mockState = {
        fetchCount: 0,
        navigatedUrls: [],
        resolveFetch: null,
        delayFetch: false,
        nextResponse: null,
        nextMarkResponse: null,
    };

    function resetMock(overrides) {
        mockState = {
            fetchCount: 0,
            navigatedUrls: [],
            resolveFetch: null,
            delayFetch: false,
            nextResponse: null,
            nextMarkResponse: null,
            ...overrides,
        };
    }

    // Read production JS once — never modify its content
    const productionJs = fs.readFileSync('public/assets/js/admin-notifications.js', 'utf8');

    // Local HTTP server on ephemeral port
    const server = http.createServer((req, res) => {
        // Serve production JS unmodified
        if (req.url === '/public/assets/js/admin-notifications.js') {
            res.writeHead(200, { 'Content-Type': 'application/javascript' });
            res.end(productionJs);
            return;
        }

        // Mock mark_read endpoint
        if (req.url === '/api/admin/notifications/mark_read') {
            const mr = mockState.nextMarkResponse;
            if (!mr) {
                req.socket.destroy();
                return;
            }
            const ct = mr.contentType || 'application/json';
            res.writeHead(mr.status, { 'Content-Type': ct });
            if (mr.body !== undefined) {
                res.end(mr.body);
            } else {
                res.end(JSON.stringify(mr.data));
            }
            return;
        }

        // Mock notifications list endpoint
        if (req.url === '/api/admin/notifications') {
            mockState.fetchCount++;
            const deliver = () => {
                const nr = mockState.nextResponse;
                if (!nr) {
                    req.socket.destroy();
                    return;
                }
                const ct = nr.contentType || 'application/json';
                res.writeHead(nr.status, { 'Content-Type': ct });
                if (nr.body !== undefined) {
                    res.end(nr.body);
                } else if (nr.malformed) {
                    res.end('{"success": true'); // truncated → parse error
                } else {
                    res.end(JSON.stringify(nr.data));
                }
            };
            if (mockState.delayFetch) {
                mockState.resolveFetch = deliver;
            } else {
                deliver();
            }
            return;
        }

        // Admin-path pages — tracking navigation by interception in browser
        if (req.url.startsWith('/admin')) {
            mockState.navigatedUrls.push(req.url);
            res.writeHead(200, { 'Content-Type': 'text/html' });
            res.end('<!DOCTYPE html><html><body>Admin page</body></html>');
            return;
        }

        // Fixture HTML page
        if (req.url === '/') {
            const html = `<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="test_token">
  <meta name="admin-notif-api" content="/api/admin/notifications">
  <meta name="admin-notif-mark-api" content="/api/admin/notifications/mark_read">
  <meta name="app-base-url" content="http://127.0.0.1:${server.address().port}">
</head>
<body>
  <div id="adminNotifBell"></div>
  <div id="adminNotifBadge" style="display:block">5</div>
  <div id="adminNotifDropdown" style="display:none"></div>
  <div id="adminNotifList"></div>
  <button id="markReadNotifBtn"></button>
  <script src="/public/assets/js/admin-notifications.js"></script>
</body>
</html>`;
            res.writeHead(200, { 'Content-Type': 'text/html' });
            res.end(html);
            return;
        }

        res.writeHead(404);
        res.end('Not found');
    });

    await new Promise(r => server.listen(0, '127.0.0.1', r));
    const port = server.address().port;
    const baseUrl = `http://127.0.0.1:${port}`;
    log(`Local server: ${baseUrl}`);

    const launchOptions = { headless: true };
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        launchOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const browser = await puppeteer.launch(launchOptions);
    let page;

    try {
        const browserVersion = await browser.version();
        log(`Browser Version: ${browserVersion}`);

        page = await browser.newPage();
        page.on('dialog', async dialog => { await dialog.accept(); });
        page.on('pageerror', err => {
            pageErrors++;
            log(`[PAGE ERROR] ${err.toString()}`);
        });

        // Enable request interception to track navigation without leaving the page
        await page.setRequestInterception(true);
        page.on('request', request => {
            const url = request.url();
            const isNavigation = request.isNavigationRequest() && request.frame() === page.mainFrame();
            if (isNavigation && url !== `${baseUrl}/`) {
                // Record navigation attempt and abort so we stay on fixture page
                mockState.navigatedUrls.push(url);
                request.abort();
            } else {
                request.continue();
            }
        });

        async function loadPage(newMockState) {
            resetMock(newMockState);
            await page.goto(`${baseUrl}/`, { waitUntil: 'domcontentloaded' });
        }

        // ── 1. DOM XSS ────────────────────────────────────────────────────────
        log('\n--- DOM XSS Test ---');
        const xssPayload = '<img src=x onerror="window.__cp03Xss=1"> </script><script>window.__cp03Xss=1<\/script> <svg onload="window.__cp03Xss=1"> single quote\' double quote" backtick` HTML entity &lt; javascript: URL text';
        await loadPage({
            nextResponse: { status: 200, data: { success: true, unread: 1, items: [{
                id: 1, title: xssPayload, content: xssPayload,
                link: `${baseUrl}/admin/foo`, created_at: '2023', is_read: 0
            }] } },
        });
        await page.waitForFunction(() => document.querySelectorAll('#adminNotifList > div').length > 0, { timeout: 3000 });

        const xssResult = await page.evaluate(() => {
            const list = document.getElementById('adminNotifList');
            return {
                hasImg: list.querySelector('img') !== null,
                hasScript: list.querySelector('script') !== null,
                hasSvg: list.querySelector('svg') !== null,
                hasOnerror: Array.from(list.querySelectorAll('*')).some(el => el.hasAttribute('onerror')),
                hasOnload: Array.from(list.querySelectorAll('*')).some(el => el.hasAttribute('onload')),
                xssFired: window.__cp03Xss === 1,
                hasText: list.textContent.includes('single quote'),
            };
        });

        assert(xssResult.hasText, "XSS payload rendered as textContent");
        assert(!xssResult.hasImg, "No IMG element created from payload");
        assert(!xssResult.hasScript, "No SCRIPT element created from payload");
        assert(!xssResult.hasSvg, "No SVG element created from payload");
        assert(!xssResult.hasOnerror && !xssResult.hasOnload, "No onerror/onload attributes from payload");
        assert(!xssResult.xssFired, "window.__cp03Xss not fired");

        // ── 2. Safe-Link Matrix ───────────────────────────────────────────────
        log('\n--- Safe Links Matrix (array cases) ---');

        async function testLinkNav(linkValue, markResponse) {
            const mr = markResponse || { status: 200, data: { success: true } };
            await loadPage({
                nextResponse: { status: 200, data: { success: true, unread: 1, items: [{
                    id: 1, title: 'T', content: 'C',
                    link: linkValue, created_at: '2023', is_read: 0
                }] } },
                nextMarkResponse: mr,
            });
            await page.waitForFunction(() => document.querySelectorAll('#adminNotifList > div').length > 0, { timeout: 3000 });
            await page.click('#adminNotifList > div');
            await new Promise(r => setTimeout(r, 150));
            return mockState.navigatedUrls.length > 0 ? mockState.navigatedUrls[0] : false;
        }

        const adminRoot = `${baseUrl}/admin`;
        const safeLinkCases = [
            // [label, url, expectedNavigated]
            ['exact admin root',        adminRoot,                                          true],
            ['admin child path',        `${adminRoot}/dashboard`,                           true],
            ['null value',              null,                                               false],
            ['undefined value',         undefined,                                          false],
            ['empty string',            '',                                                 false],
            ['malformed http://[',      'http://[',                                         false],
            ['\\u0000 control char',    `${adminRoot}/\u0000foo`,                           false],
            ['\\u000D control char',    `${adminRoot}/\u000Dfoo`,                           false],
            ['\\u007F control char',    `${adminRoot}/\u007Ffoo`,                           false],
            ['javascript: scheme',      'javascript:alert(1)',                              false],
            ['data: scheme',            'data:text/html,<html>',                            false],
            ['vbscript: scheme',        'vbscript:msgbox(1)',                               false],
            ['external HTTPS',          'https://evil.com/admin',                           false],
            ['external protocol-rel',   '//evil.com/admin',                                 false],
            ['credential URL',          `http://admin:pass@127.0.0.1:${port}/admin`,        false],
            ['/foo/admin/bar path',     `${baseUrl}/foo/admin/bar`,                         false],
        ];

        for (const [label, url, expected] of safeLinkCases) {
            const navUrl = await testLinkNav(url);
            const didNav = navUrl !== false;
            if (didNav === expected) {
                passed++;
                log(`[PASS] Safe-link (${label}): navigated=${didNav}`);
            } else {
                failed++;
                log(`[FAIL] Safe-link (${label}): expected navigated=${expected}, got navigated=${didNav} (url=${navUrl})`);
            }
        }

        // ── 3. API Error Parsing / UI Messages ───────────────────────────────
        log('\n--- API Error Parsing & UI Messages ---');

        const validLink = `${adminRoot}/valid`;

        async function testMarkReadError(markResponse) {
            await loadPage({
                nextResponse: { status: 200, data: { success: true, unread: 1, items: [{
                    id: 1, title: 'T', content: 'C', link: validLink, created_at: '2023', is_read: 0
                }] } },
                nextMarkResponse: markResponse,
            });
            await page.waitForFunction(() => document.querySelectorAll('#adminNotifList > div').length > 0, { timeout: 3000 });
            const badgeBefore = await page.evaluate(() => document.getElementById('adminNotifBadge').textContent);
            await page.click('#adminNotifList > div');
            await new Promise(r => setTimeout(r, 200));
            return page.evaluate(() => {
                const list = document.getElementById('adminNotifList');
                const badge = document.getElementById('adminNotifBadge');
                return {
                    listText: list.textContent.trim(),
                    badgeDisplay: badge.style.display,
                    badgeText: badge.textContent,
                    navigated: window.__navCount || 0,
                };
            });
        }

        // 403 JSON
        let r = await testMarkReadError({ status: 403, data: { success: false } });
        assert(r.listText === 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', '403 JSON → correct message');
        assert(mockState.navigatedUrls.length === 0, '403 JSON → no navigation');

        // 403 HTML (non-JSON body)
        r = await testMarkReadError({ status: 403, contentType: 'text/html', body: '<html>403 Forbidden</html>' });
        assert(r.listText === 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.', '403 HTML → correct message');
        assert(mockState.navigatedUrls.length === 0, '403 HTML → no navigation');

        // 404
        r = await testMarkReadError({ status: 404, data: { success: false } });
        assert(r.listText === 'Không thể cập nhật thông báo.', '404 → correct message');

        // 500
        r = await testMarkReadError({ status: 500, data: { success: false } });
        assert(r.listText === 'Không thể cập nhật thông báo.', '500 → correct message');

        // 503
        r = await testMarkReadError({ status: 503, data: { success: false } });
        assert(r.listText === 'Không thể cập nhật thông báo.', '503 → correct message');

        // malformed JSON
        r = await testMarkReadError({ status: 200, malformed: true });
        assert(r.listText === 'Không thể cập nhật thông báo.', 'malformed JSON → correct message');

        // success=false
        r = await testMarkReadError({ status: 200, data: { success: false } });
        assert(r.listText === 'Không thể cập nhật thông báo.', 'success=false → correct message');

        // network rejection (null = destroy socket)
        r = await testMarkReadError(null);
        assert(r.listText === 'Không thể cập nhật thông báo.', 'network rejection → correct message');

        // Badge unchanged on all errors: re-verify with 403
        r = await testMarkReadError({ status: 403, contentType: 'text/html', body: '<html>403</html>' });
        assert(r.badgeDisplay !== 'none', '403 → badge not hidden');

        // ── 4. No-Navigation Contract ─────────────────────────────────────────
        log('\n--- No-Navigation Contract ---');

        // Only success=true + valid link → navigate
        const navResult = await testLinkNav(validLink, { status: 200, data: { success: true } });
        assert(navResult !== false, 'HTTP 200 success=true + valid link → navigated');

        // Failure responses → no navigation
        assert(await testLinkNav(validLink, { status: 403, data: { success: false } }) === false, '403 → no navigation');
        assert(await testLinkNav(validLink, { status: 404, data: { success: false } }) === false, '404 → no navigation');
        assert(await testLinkNav(validLink, { status: 500, data: { success: false } }) === false, '500 → no navigation');
        assert(await testLinkNav(validLink, { status: 503, data: { success: false } }) === false, '503 → no navigation');
        assert(await testLinkNav(validLink, { status: 200, data: { success: false } }) === false, 'success=false → no navigation');
        assert(await testLinkNav(validLink, { status: 200, malformed: true }) === false, 'malformed JSON → no navigation');
        assert(await testLinkNav('javascript:alert(1)', { status: 200, data: { success: true } }) === false, 'javascript: link → no navigation even on success');

        // ── 5. Exact Polling Counts ───────────────────────────────────────────
        log('\n--- Exact Polling Counts ---');

        // Load page with delayFetch=true so initial fetch is held
        await loadPage({
            nextResponse: { status: 200, data: { success: true, unread: 2, items: [] } },
            nextMarkResponse: { status: 200, data: { success: true } },
            delayFetch: true,
        });
        // Wait a moment; initial fetch should have started
        await new Promise(r => setTimeout(r, 100));

        const c1 = mockState.fetchCount;
        assert(c1 === 1, `Pending initial request: fetchCount === 1 (actual: ${c1})`);

        // Click mark-all while initial is pending → must NOT start a second fetch
        await page.click('#markReadNotifBtn');
        await new Promise(r => setTimeout(r, 100));
        const c2 = mockState.fetchCount;
        assert(c2 === 1, `Overlapping request: fetchCount === 1 (actual: ${c2})`);

        // Resolve the pending initial fetch
        mockState.delayFetch = false;
        if (mockState.resolveFetch) mockState.resolveFetch();
        await new Promise(r => setTimeout(r, 200));

        // Click once more after resolve
        await page.click('#markReadNotifBtn');
        await new Promise(r => setTimeout(r, 200));
        const c3 = mockState.fetchCount;
        assert(c3 === 2, `Request after resolve: fetchCount === 2 (actual: ${c3})`);

        // Now set up a failing response
        mockState.nextResponse = { status: 500, data: { success: false } };
        await page.click('#markReadNotifBtn');
        await new Promise(r => setTimeout(r, 200));
        const c4 = mockState.fetchCount;
        assert(c4 === 3, `Failed request: fetchCount === 3 (actual: ${c4})`);

        // After failure, guard must reset; next click should succeed
        mockState.nextResponse = { status: 200, data: { success: true, unread: 0, items: [] } };
        await page.click('#markReadNotifBtn');
        await new Promise(r => setTimeout(r, 200));
        const c5 = mockState.fetchCount;
        assert(c5 === 4, `Request after failure: fetchCount === 4 (actual: ${c5})`);

        log(`Exact expected counts: 1, 1, 2, 3, 4`);
        log(`Actual counts:         ${c1}, ${c2}, ${c3}, ${c4}, ${c5}`);

        // ── 6. Browser Error Gate ─────────────────────────────────────────────
        assert(pageErrors === 0, `Không có browser page error (pageErrors === 0, actual: ${pageErrors})`);

    } finally {
        if (page) {
            try { await page.close(); } catch (_) {}
        }
        await browser.close();
        server.close();
    }

    log('\n========================================================');
    log(`CP03 Puppeteer Results: ${passed} passed, ${failed} failed`);
    log('========================================================');

    if (pageErrors > 0) {
        failed++;
    }

    process.exit(failed > 0 ? 1 : 0);
}

runTest().catch(e => {
    console.error('Uncaught exception:', e);
    process.exit(1);
});
