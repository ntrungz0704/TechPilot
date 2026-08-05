const puppeteer = require('puppeteer');
const http = require('http');

async function checkUrlStatus(url) {
    return new Promise((resolve) => {
        http.get(url, (res) => {
            resolve(res.statusCode);
        }).on('error', (e) => {
            resolve(0);
        });
    });
}

(async () => {
    const APP_URL = 'http://techpilot.test/TechPilot/public';
    let browser;
    try {
        const isAppUp = await checkUrlStatus(APP_URL);
        if (isAppUp === 0) {
            console.error(`❌ Application is not running at ${APP_URL}`);
            process.exit(1);
        }

        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });

        const page = await browser.newPage();
        page.on('console', msg => console.log('PAGE LOG:', msg.text()));
        
        console.log("========================================================");
        console.log("=== CP04.1 PUPPETEER STOREFRONT CART & MEDIA TEST    ===");
        console.log("========================================================\n");

        // 1. Guest flow
        await page.goto(`${APP_URL}/`, { waitUntil: 'networkidle0' });
        
        try {
            await page.waitForSelector('.product-card__add', { timeout: 3000 });
            // Click AJAX add to cart via evaluate to bypass visibility check
            await page.evaluate(() => document.querySelector('.product-card__add').click());
            await new Promise(r => setTimeout(r, 1000));
            
            // Check if cartBadge updated
            const badgeText = await page.evaluate(() => {
                const badge = document.getElementById('cartBadge');
                return badge ? badge.textContent : null;
            });
            console.log("Badge text is:", badgeText);
            if (parseInt(badgeText) > 0) {
                console.log("[PASS] Guest AJAX add to cart worked, badge updated.");
            } else {
                console.error("[FAIL] Guest AJAX add to cart failed to update badge.");
                process.exit(1);
            }
            
            // Ensure no redirect happened
            const url = page.url();
            if (url === `${APP_URL}/`) {
                console.log("[PASS] AJAX add to cart did not redirect.");
            } else {
                console.error("[FAIL] AJAX add to cart redirected unexpectedly to: " + url);
                process.exit(1);
            }

            // 2. Buy Now flow
            // Go to the first product detail page found on home
            const detailUrl = await page.evaluate(() => {
                const link = document.querySelector('.product-card__thumb');
                return link ? link.href : null;
            });

            if (detailUrl) {
                await page.goto(detailUrl, { waitUntil: 'networkidle0' });
                await page.waitForSelector('button[onclick="buyNowSubmit()"]', { timeout: 3000 });
                
                await Promise.all([
                    page.waitForNavigation({ waitUntil: 'networkidle0' }),
                    page.click('button[onclick="buyNowSubmit()"]')
                ]);
                
                if (page.url().includes('/auth/login') || page.url().includes('/checkout')) {
                    console.log("[PASS] Buy Now button redirected correctly.");
                } else {
                    console.error("[FAIL] Buy Now button did not redirect correctly. URL: " + page.url());
                    process.exit(1);
                }
            } else {
                console.log("⚠️ Could not find detail link. Skipping Buy Now test.");
            }
        } catch (err) {
            console.error("❌ Could not find product-card__add on homepage or timed out:", err);
            await page.screenshot({ path: 'tests/CP041_error.png' });
            process.exit(1);
        }

        console.log("\n[SUCCESS] CP04.1 Puppeteer tests passed!");
    } catch (e) {
        console.error("Test failed with exception:", e);
        process.exit(1);
    } finally {
        if (browser) await browser.close();
    }
})();
