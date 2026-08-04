const puppeteer = require('puppeteer');

(async () => {
    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            args: ['--no-sandbox', '--disable-setuid-sandbox']
        });
        const page = await browser.newPage();
        
        let failures = 0;
        const assert = (condition, msg) => {
            if (!condition) {
                console.error(`[FAIL] ${msg}`);
                failures++;
            } else {
                console.log(`[PASS] ${msg}`);
            }
        };

        const baseUrl = 'http://techpilot.test/TechPilot/public';
        const p28Slug = 'pc-techpilot-gaming-p28-intel-core-i5-14400f-rtx-4060-8gb-16gb';
        
        // 1. Test Detail Page
        await page.goto(`${baseUrl}/product/detail/${p28Slug}`);
        
        // Main detail image
        const mainDetailImgSrc = await page.$eval('.product-detail__main-image-src', img => img.src);
        assert(mainDetailImgSrc !== '', 'Found P28 main detail image');
        assert(!mainDetailImgSrc.includes('laptop.png'), 'Main detail image is not laptop placeholder');
        assert(!mainDetailImgSrc.includes('cpu.png'), 'Main detail image is not CPU placeholder');
        assert(mainDetailImgSrc.includes('case.png') || mainDetailImgSrc.includes('placeholder-case-1.png'), 'Main detail image is case placeholder');
        
        // Check for thumbnail strip
        const thumbs = await page.$$('.product-detail__thumb-image');
        
        if (thumbs.length === 0) {
            assert(true, 'Thumbnail strip is not rendered (1 valid image)');
        } else {
            assert(thumbs.length > 1, 'If thumbnail strip is rendered, it must have > 1 thumbnail');
            
            const thumbSrcs = [];
            for (let thumb of thumbs) {
                thumbSrcs.push(await thumb.evaluate(el => el.src));
            }
            
            // Unique URLs
            const uniqueSrcs = [...new Set(thumbSrcs)];
            assert(uniqueSrcs.length === thumbSrcs.length, 'Resolved gallery URLs are unique');
            
            // Thumbnail đầu tiên phải giống ảnh chính
            assert(thumbSrcs[0] === mainDetailImgSrc, 'First thumbnail matches main detail image');
            
            // Không có CPU placeholder trong gallery của PC
            const hasCpuPlaceholder = thumbSrcs.some(src => src.includes('cpu.png'));
            assert(!hasCpuPlaceholder, 'No CPU placeholder in gallery');
            
            // Không external network image
            const hasExternal = thumbSrcs.some(src => !src.startsWith(baseUrl) && !src.startsWith('data:'));
            assert(!hasExternal, 'No external network images in gallery');
            
            // Gallery không có 3 thumbnail giống nhau
            let maxIdentical = 0;
            const srcCounts = {};
            for (let src of thumbSrcs) {
                srcCounts[src] = (srcCounts[src] || 0) + 1;
                if (srcCounts[src] > maxIdentical) maxIdentical = srcCounts[src];
            }
            assert(maxIdentical < 3, 'Gallery does not have 3 identical thumbnails');
        }
        
        // Other Categories Tests
        const otherSlugs = [
            'pc-techpilot-gaming-p1-amd-ryzen-5-7600x-rtx-4070-super-12gb-16gb',
            'laptop-asus-model-l1-intel-core-i5-13420h-16gb-ssd-1tb',
            'man-hinh-asus-model-m1-27-inch-2560x1440-2k-144hz',
            'cpu-intel-core-i5-14400f-c1-box'
        ];
        
        for (let slug of otherSlugs) {
            await page.goto(`${baseUrl}/product/detail/${slug}`);
            const mainImg = await page.$eval('.product-detail__main-image-src', img => img.src);
            assert(mainImg !== '', `Found main image for ${slug}`);
            const tbs = await page.$$('.product-detail__thumb-image');
            if (tbs.length > 1) {
                const src1 = await tbs[0].evaluate(el => el.src);
                assert(src1 === mainImg, `First thumb matches main image for ${slug}`);
            }
        }

        if (failures > 0) {
            console.error(`\n[RESULT] Tests failed: ${failures}`);
            process.exit(1);
        } else {
            console.log(`\n[SUCCESS] All P28 gallery tests passed!`);
            process.exit(0);
        }
        
    } catch (err) {
        console.error('[ERROR] Exception occurred:', err);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
