const fs = require('fs');
let css = fs.readFileSync('public/assets/css/style.css', 'utf8');

// 1. site-header relative
css = css.replace('.site-header {\r\n    background-color: var(--bg-white);\r\n    box-shadow: var(--shadow-card);\r\n    position: sticky;\r\n    top: 0;', '.site-header {\r\n    background-color: var(--bg-white);\r\n    box-shadow: var(--shadow-card);\r\n    position: relative;\r\n    top: auto;');
css = css.replace('.site-header {\n    background-color: var(--bg-white);\n    box-shadow: var(--shadow-card);\n    position: sticky;\n    top: 0;', '.site-header {\n    background-color: var(--bg-white);\n    box-shadow: var(--shadow-card);\n    position: relative;\n    top: auto;');

// 2. main-nav sticky
css = css.replace('.main-nav {\r\n    background-color: var(--primary);\r\n    height: 50px;\r\n}', '.main-nav {\r\n    background-color: var(--primary);\r\n    height: 50px;\r\n    position: sticky;\r\n    top: 0;\r\n    z-index: 1000;\r\n    transition: box-shadow 180ms ease, background-color 180ms ease;\r\n}\r\n\r\n.main-nav.is-stuck {\r\n    box-shadow: 0 8px 24px rgba(14, 19, 32, 0.16);\r\n}');
css = css.replace('.main-nav {\n    background-color: var(--primary);\n    height: 50px;\n}', '.main-nav {\n    background-color: var(--primary);\n    height: 50px;\n    position: sticky;\n    top: 0;\n    z-index: 1000;\n    transition: box-shadow 180ms ease, background-color 180ms ease;\n}\n\n.main-nav.is-stuck {\n    box-shadow: 0 8px 24px rgba(14, 19, 32, 0.16);\n}');

// 3. Remove display: none at 1024px
css = css.replace('@media (max-width: 1024px) {\r\n    .main-nav {\r\n        display: none;\r\n    }\r\n}', '');
css = css.replace('@media (max-width: 1024px) {\n    .main-nav {\n        display: none;\n    }\n}', '');

// 4. Extract drawer CSS from 575px and put into 1024px
const startComment = "/* 5. Mobile Mega Menu Drawer - Slide từ trái vào */";
let startIndex = css.indexOf(startComment);
if (startIndex !== -1) {
    // Find the end by looking for another known comment or the end of the block
    let endIndex = css.indexOf("/* Scroll Helper */", startIndex);
    if (endIndex === -1) endIndex = css.indexOf("main.container.section", startIndex);

    if (endIndex !== -1) {
        let drawerCss = css.substring(startIndex, endIndex);
        css = css.substring(0, startIndex) + css.substring(endIndex);
        
        css += "\n\n@media (max-width: 1024px) {\n" + drawerCss + "\n}\n";
    }
}

fs.writeFileSync('public/assets/css/style.css', css, 'utf8');
console.log("Done");
