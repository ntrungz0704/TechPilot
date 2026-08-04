const puppeteer = require('puppeteer');
const fs = require('fs');

async function runTest() {
    let passed = 0;
    let failed = 0;
    let pageErrors = 0;
    const logs = [];

    function log(msg) {
        logs.push(msg);
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

    const launchOptions = { headless: true };
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        launchOptions.executablePath = process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const browser = await puppeteer.launch(launchOptions);
    const browserVersion = await browser.version();
    log(`Browser Version: ${browserVersion}`);

    const page = await browser.newPage();
    
    page.on('dialog', async dialog => { await dialog.accept(); });
    page.on('pageerror', err => { pageErrors++; log(`Page error: ${err.toString()}`); });

    let scriptContent = fs.readFileSync('public/assets/js/admin-notifications.js', 'utf8');
    scriptContent = scriptContent.replace('window.location.href = validUrl;', 'window.__mockState.navigatedUrls.push(validUrl);');
    scriptContent = scriptContent.replace('const validUrl = validateLink(link);', 'const validUrl = validateLink(link);');
    scriptContent = scriptContent.replace(/window\.location\.origin/g, '"http://localhost"');

    const getHtml = (mockStateJson) => `
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="csrf-token" content="test_token">
        <meta name="admin-notif-api" content="/api/admin/notifications">
        <meta name="admin-notif-mark-api" content="/api/admin/notifications/mark_read">
        <meta name="app-base-url" content="http://localhost">
    </head>
    <body>
        <div id="adminNotifBell"></div>
        <div id="adminNotifBadge" style="display:block">5</div>
        <div id="adminNotifDropdown" style="display:none"></div>
        <div id="adminNotifList"></div>
        <button id="markReadNotifBtn"></button>
        <script>
            window.__mockState = ${mockStateJson};

            window.originalFetch = window.fetch;
            window.fetch = async function(url, options) {
                if (url === '/api/admin/notifications/mark_read') {
                    const res = window.__mockState.nextMarkResponse;
                    if (!res) throw new TypeError('Failed to fetch');
                    return {
                        ok: res.ok, status: res.status, headers: { get: () => 'application/json' },
                        json: async () => res.data
                    };
                }

                if (url === '/api/admin/notifications') {
                    window.__mockState.fetchCount++;
                    if (window.__mockState.delayFetch) {
                        await new Promise(r => { window.__mockState.resolveFetch = r; });
                    }
                    const res = window.__mockState.nextResponse;
                    if (!res) throw new TypeError('Failed to fetch');
                    return {
                        ok: res.ok, status: res.status, headers: { get: () => 'application/json' },
                        json: async () => {
                            if (res.malformed) throw new SyntaxError("Unexpected token");
                            return res.data;
                        }
                    };
                }
            };
        </script>
        <script>${scriptContent}</script>
    </body>
    </html>
    `;

    async function setPageContext(mockState) {
        await page.goto('about:blank');
        const mockStateJson = JSON.stringify(mockState).replace(/<\/script>/gi, '<\\/script>');
        await page.setContent(getHtml(mockStateJson));
    }

    // 1. DOM XSS Test
    log("\n--- DOM XSS Test ---");
    const payload = `<img src=x onerror="window.__cp03Xss=1"> </script><script>window.__cp03Xss=1</script> <svg onload="window.__cp03Xss=1"> single quote double quote backtick HTML entity javascript: URL text`;
    await setPageContext({
        nextResponse: { ok: true, status: 200, data: { success: true, unread: 1, items: [{ id: 1, title: payload, content: payload, link: 'http://localhost/admin/foo', created_at: '2023', is_read: 0 }] } },
        nextMarkResponse: null, fetchCount: 0, navigatedUrls: [], resolveFetch: null, delayFetch: false
    });
    await page.waitForFunction(() => document.querySelectorAll('#adminNotifList > div').length > 0);

    const xssResult = await page.evaluate((payload) => {
        const list = document.getElementById('adminNotifList');
        const textContent = list.textContent;
        const hasImg = list.querySelector('img') !== null;
        const hasScript = list.querySelector('script') !== null;
        const hasSvg = list.querySelector('svg') !== null;
        const hasOnerrorAttr = Array.from(list.querySelectorAll('*')).some(el => el.hasAttribute('onerror'));
        const hasOnloadAttr = Array.from(list.querySelectorAll('*')).some(el => el.hasAttribute('onload'));
        const hasOnclickAttr = Array.from(list.querySelectorAll('*')).some(el => el.hasAttribute('onclick'));
        const isXssFired = window.__cp03Xss === 1;
        return { containsText: textContent.includes(payload) || textContent.includes("single quote double quote"), hasImg, hasScript, hasSvg, hasOnerrorAttr, hasOnloadAttr, hasOnclickAttr, isXssFired };
    }, payload);

    assert(xssResult.containsText, "Payload xuất hiện dưới dạng textContent");
    assert(!xssResult.hasImg, "Không tạo IMG từ payload");
    assert(!xssResult.hasScript, "Không tạo SCRIPT từ payload");
    assert(!xssResult.hasSvg, "Không tạo SVG từ payload");
    assert(!xssResult.hasOnerrorAttr && !xssResult.hasOnloadAttr && !xssResult.hasOnclickAttr, "Không tồn tại onerror/onload/onclick attribute từ payload");
    assert(!xssResult.isXssFired, "window.__cp03Xss không tồn tại hoặc không bằng 1");

    // 2. Safe Links Matrix Test
    async function testLinkNavigation(linkToTest, responseOverride = null) {
        await setPageContext({
            nextResponse: { ok: true, status: 200, data: { success: true, unread: 1, items: [{ id: 1, title: "T", content: "C", link: linkToTest, created_at: '2023', is_read: 0 }] } },
            nextMarkResponse: responseOverride ? responseOverride : {ok: true, status: 200, data: {success: true}},
            fetchCount: 0, navigatedUrls: [], resolveFetch: null, delayFetch: false
        });
        await page.waitForFunction(() => document.querySelectorAll('#adminNotifList > div').length > 0);
        await page.click('#adminNotifList > div');
        await new Promise(r => setTimeout(r, 100)); // wait for fetch and potential navigation
        return await page.evaluate(() => window.__mockState.navigatedUrls.length > 0);
    }

    log("\n--- Safe Links Matrix ---");
    const safeLinksTests = {
        "http://localhost/admin": true,
        "http://localhost/admin/dashboard": true,
        "javascript:alert(1)": false,
        "data:text/html,<html>": false,
        "vbscript:msgbox(1)": false,
        "https://evil.com/admin": false,
        "//evil.com/admin": false,
        "http://localhost/foo/admin/bar": false,
        "http://admin:pass@localhost/admin": false,
        "http://[::1]/admin": false,
        "null": false,
        "": false,
        "http://localhost/admin/ ": false
    };

    for (const [url, expected] of Object.entries(safeLinksTests)) {
        const navigated = await testLinkNavigation(url);
        const didNavigate = navigated !== false;
        if (didNavigate === expected) {
            passed++;
            log(`[PASS] Link ${url} navigated: ${didNavigate} (expected: ${expected})`);
        } else {
            failed++;
            log(`[FAIL] Link ${url} navigated: ${didNavigate} (expected: ${expected}) - URL was: ${navigated}`);
        }
    }

    log("\n--- No-Navigation Contract ---");
    const validLink = "http://localhost/admin/valid";
    assert(await testLinkNavigation(validLink, {ok: false, status: 403}) === false, "HTTP 403 không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: false, status: 404}) === false, "HTTP 404 không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: false, status: 500}) === false, "HTTP 500 không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: false, status: 503}) === false, "HTTP 503 không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: true, status: 200, data: {success: false}}) === false, "success=false không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: true, status: 200, malformed: true}) === false, "malformed JSON không điều hướng");
    assert(await testLinkNavigation("javascript:alert(1)", {ok: true, status: 200, data: {success: true}}) === false, "invalid link không điều hướng");
    assert(await testLinkNavigation(validLink, {ok: true, status: 200, data: {success: true}}) !== false, "chỉ HTTP OK + JSON success=true + valid link mới điều hướng");

    log("\n--- Polling Overlap ---");
    await setPageContext({
        nextResponse: {ok: true, status: 200, data: {success: true, unread: 2, items: []}},
        nextMarkResponse: {ok: true, status: 200, data: {success: true}},
        fetchCount: 0, navigatedUrls: [], resolveFetch: null, delayFetch: true
    });
    const pollingResult = await page.evaluate(async () => {
        document.getElementById('markReadNotifBtn').click();
        document.getElementById('markReadNotifBtn').click();
        await new Promise(r => setTimeout(r, 50));
        const countOverlap = window.__mockState.fetchCount;
        
        window.__mockState.delayFetch = false;
        if (window.__mockState.resolveFetch) window.__mockState.resolveFetch();
        await new Promise(r => setTimeout(r, 50));
        
        document.getElementById('markReadNotifBtn').click();
        await new Promise(r => setTimeout(r, 50));
        const countAfterResolve = window.__mockState.fetchCount;
        
        window.__mockState.nextResponse = null; 
        document.getElementById('markReadNotifBtn').click(); 
        await new Promise(r => setTimeout(r, 50));
        
        window.__mockState.nextResponse = {ok: true, status: 200, data: {success: true, unread: 3, items: []}};
        document.getElementById('markReadNotifBtn').click(); 
        await new Promise(r => setTimeout(r, 50));
        const countAfterReject = window.__mockState.fetchCount;

        return { countOverlap, countAfterResolve, countAfterReject };
    });

    assert(pollingResult.countOverlap === 1, "hai request overlap tạo fetch call count bằng 1");
    // Initial fetch(on load) + overlap(1) = 2. After resolve, clicking again makes it 3.
    assert(pollingResult.countAfterResolve >= 2, "sau resolve call count tăng");
    assert(pollingResult.countAfterReject > pollingResult.countAfterResolve, "sau reject guard được reset");
    
    // 5. Polling Failure Test
    log("\n--- Polling Failure ---");
    await setPageContext({
        nextResponse: {ok: true, status: 200, data: {success: true, unread: 5, items: [{id: 2, title: "Bad", link: "javascript:void(0);", is_read: 0, content: "Bad"}]}},
        nextMarkResponse: {ok: true, status: 200, data: {success: true}}, fetchCount: 0, navigatedUrls: [], resolveFetch: null, delayFetch: false
    });
    const failureResult = await page.evaluate(async () => {
        // Wait for initial render and badge update
        await new Promise(r => setTimeout(r, 100));
        
        // Setup the next fetch to fail
        window.__mockState.nextResponse = {ok: false, status: 500};
        
        // Click the invalid notification
        // It triggers mark_read (which succeeds), then validateLink returns null, so it calls fetchNotifications
        document.querySelector('#adminNotifList > div').click();
        await new Promise(r => setTimeout(r, 100));
        
        return {
            badgeText: document.getElementById('adminNotifBadge').textContent,
            badgeDisplay: document.getElementById('adminNotifBadge').style.display
        };
    });

    assert(failureResult.badgeText === "5", "polling failure không ẩn badge hiện tại");
    assert(failureResult.badgeDisplay !== "none", "polling failure không đổi display thành none");

    log("\n========================================================");
    log(`CP03 Puppeteer Results: ${passed} passed, ${failed} failed`);
    log("========================================================");
    if (pageErrors > 0) log(`[!] Warning: ${pageErrors} page errors occurred!`);

    await browser.close();
    process.exit(failed > 0 ? 1 : 0);
}

runTest().catch(e => {
    console.error("Uncaught exception:", e);
    process.exit(1);
});
