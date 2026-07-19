const fs = require('fs');
let css = fs.readFileSync('public/assets/css/style.css', 'utf8');

const startStr = "/* 5. Mobile Mega Menu Drawer - Slide từ trái vào */";
let startIndex = css.indexOf(startStr);
if (startIndex !== -1) {
    let endStr = "/* 6. Banner Hero slider */";
    let endIndex = css.indexOf(endStr, startIndex);
    if (endIndex !== -1) {
        let drawerCss = css.substring(startIndex, endIndex);
        
        css = css.substring(0, startIndex) + css.substring(endIndex);
        
        css += "\n\n@media (max-width: 1024px) {\n    " + drawerCss.trim().replace(/\n/g, "\n    ") + "\n}\n";
        
        fs.writeFileSync('public/assets/css/style.css', css, 'utf8');
        console.log("Moved drawer CSS");
    } else {
        console.log("Could not find endStr");
    }
} else {
    console.log("Could not find startStr");
}
