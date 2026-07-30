/**
 * brandLogos.js
 * -------------------------------------------------------------
 * Fallback 3 lớp lấy logo CHÍNH CHỦ cho TechPilot (HTML5 / Vanilla JS):
 *   1) Local SVG asset (ưu tiên hàng đầu)
 *   2) Wikipedia pageimages API -> lấy đúng logo trong infobox trang Wikipedia chính thức của hãng
 *   3) Fallback cuối: Text logo badge chuẩn thương hiệu khi cả 2 fail
 * -------------------------------------------------------------
 */

export const BRANDS = {
  // ===== Vi xử lý & Linh kiện chính =====
  intel:        { name: 'Intel',        domain: 'intel.com',        wiki: 'Intel',                      confidence: 'high' },
  amd:          { name: 'AMD',          domain: 'amd.com',          wiki: 'AMD',                         confidence: 'high' },
  asus:         { name: 'ASUS',         domain: 'asus.com',         wiki: 'Asus',                        confidence: 'high' },
  msi:          { name: 'MSI',          domain: 'msi.com',          wiki: 'MSI (company)',               confidence: 'high' },
  gigabyte:     { name: 'GIGABYTE',     domain: 'gigabyte.com',     wiki: 'Gigabyte Technology',         confidence: 'high' },
  asrock:       { name: 'ASRock',       domain: 'asrock.com',       wiki: 'ASRock',                      confidence: 'high' },
  zotac:        { name: 'Zotac',        domain: 'zotac.com',        wiki: 'Zotac',                       confidence: 'medium' },
  colorful:     { name: 'Colorful',     domain: 'colorful.cn',      wiki: null,                          confidence: 'low' },

  // ===== Laptop & PC =====
  dell:         { name: 'DELL',         domain: 'dell.com',         wiki: 'Dell',                        confidence: 'high' },
  hp:           { name: 'HP',           domain: 'hp.com',           wiki: 'HP Inc.',                     confidence: 'high' },
  lenovo:       { name: 'Lenovo',       domain: 'lenovo.com',       wiki: 'Lenovo',                      confidence: 'high' },
  acer:         { name: 'Acer',         domain: 'acer.com',         wiki: 'Acer Inc.',                   confidence: 'high' },
  lg:           { name: 'LG',           domain: 'lg.com',           wiki: 'LG Electronics',              confidence: 'high' },
  samsung:      { name: 'Samsung',      domain: 'samsung.com',      wiki: 'Samsung Electronics',         confidence: 'high' },
  razer:        { name: 'Razer',        domain: 'razer.com',        wiki: 'Razer Inc.',                  confidence: 'high' },

  // ===== Bộ nhớ & Lưu trữ =====
  corsair:      { name: 'Corsair',      domain: 'corsair.com',      wiki: 'Corsair Gaming',              confidence: 'high' },
  kingston:     { name: 'Kingston',     domain: 'kingston.com',     wiki: 'Kingston Technology',         confidence: 'high' },
  wd:           { name: 'WD',           domain: 'westerndigital.com', wiki: 'Western Digital',           confidence: 'high' },
  lexar:        { name: 'Lexar',        domain: 'lexar.com',        wiki: 'Lexar',                       confidence: 'medium' },
  crucial:      { name: 'Crucial',      domain: 'crucial.com',      wiki: 'Crucial (brand)',             confidence: 'medium' },
  gskill:       { name: 'G.Skill',      domain: 'gskill.com',       wiki: 'G.Skill',                     confidence: 'medium' },
  'g-skill':    { name: 'G.Skill',      domain: 'gskill.com',       wiki: 'G.Skill',                     confidence: 'medium' },
  teamgroup:    { name: 'TeamGroup',    domain: 'teamgroupinc.com', wiki: null,                          confidence: 'low' },

  // ===== Tản nhiệt, Nguồn & Case =====
  deepcool:     { name: 'DeepCool',     domain: 'deepcool.com',     wiki: null,                          confidence: 'low' },
  thermalright: { name: 'Thermalright', domain: 'thermalright.com', wiki: null,                          confidence: 'low' },
  montech:      { name: 'Montech',      domain: 'montech.tw',       wiki: null,                          confidence: 'low' },
  nzxt:         { name: 'NZXT',         domain: 'nzxt.com',         wiki: 'NZXT',                        confidence: 'high' },
  lianli:       { name: 'Lian Li',      domain: 'lian-li.com',      wiki: 'Lian Li',                     confidence: 'medium' },
  'lian-li':    { name: 'Lian Li',      domain: 'lian-li.com',      wiki: 'Lian Li',                     confidence: 'medium' },
  coolermaster: { name: 'Cooler Master',domain: 'coolermaster.com', wiki: 'Cooler Master',               confidence: 'high' },
  'cooler-master': { name: 'Cooler Master',domain: 'coolermaster.com', wiki: 'Cooler Master',            confidence: 'high' },
  xigmatek:     { name: 'Xigmatek',     domain: 'xigmatek.com',     wiki: null,                          confidence: 'low' },
  logitech:     { name: 'Logitech',     domain: 'logitech.com',     wiki: 'Logitech',                    confidence: 'high' },
};

/** Gọi Wikipedia pageimages API để lấy đúng ảnh infobox (logo chính chủ) */
export async function wikiLogoUrl(title) {
  if (!title) return null;
  const api = `https://en.wikipedia.org/w/api.php?action=query&titles=${encodeURIComponent(
    title
  )}&prop=pageimages&format=json&pithumbsize=300&origin=*`;
  try {
    const res = await fetch(api);
    const data = await res.json();
    const pages = data?.query?.pages;
    const page = pages ? Object.values(pages)[0] : null;
    return page?.thumbnail?.source || null;
  } catch {
    return null;
  }
}

/** Kiểm tra 1 URL ảnh có load được không (client-side) */
export function imageLoads(url) {
  return new Promise((resolve) => {
    if (!url) return resolve(false);
    const img = new Image();
    img.onload = () => resolve(true);
    img.onerror = () => resolve(false);
    img.src = url;
  });
}

/**
 * Lấy URL logo tốt nhất cho 1 brand key.
 * Thứ tự ưu tiên: local asset -> wikipedia pageimage -> null (text fallback)
 */
export async function getLogoUrl(brandKey, localPath = null) {
  if (localPath && (await imageLoads(localPath))) {
    return localPath;
  }

  const brand = BRANDS[brandKey] || BRANDS[brandKey.replace(/-/g, '')];
  if (!brand) return null;

  if (brand.wiki) {
    const wiki = await wikiLogoUrl(brand.wiki);
    if (wiki && (await imageLoads(wiki))) return wiki;
  }

  return null;
}

/** Preload toàn bộ 30 logo 1 lần */
export async function preloadAllLogos() {
  const keys = Object.keys(BRANDS);
  const entries = await Promise.all(
    keys.map(async (key) => [key, await getLogoUrl(key)])
  );
  return Object.fromEntries(entries);
}

/** Auto Error Handler cho thẻ <img> trên giao diện */
if (typeof window !== 'undefined') {
  window.handleBrandLogoError = async function(imgElement, brandKey) {
    imgElement.onerror = null;
    const key = (brandKey || '').toLowerCase();
    const brand = BRANDS[key] || BRANDS[key.replace(/-/g, '')];
    
    if (brand && brand.wiki) {
      const wikiUrl = await wikiLogoUrl(brand.wiki);
      if (wikiUrl) {
        imgElement.src = wikiUrl;
        return;
      }
    }

    imgElement.style.display = 'none';
    const parent = imgElement.parentElement;
    if (parent) {
      const textFallback = parent.querySelector('.brand-logo-text-fallback');
      if (textFallback) {
        textFallback.style.display = 'block';
      }
    }
  };
}
