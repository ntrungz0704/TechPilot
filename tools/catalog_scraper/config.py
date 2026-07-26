"""
TechPilot Scraper Tool Configuration
"""

SOURCES = {
    'gearvn': 'https://gearvn.com',
    'phongvu': 'https://phongvu.vn',
    'tinhocngoisao': 'https://tinhocngoisao.com',
    'hoanglong': 'https://hoanglongcomputer.vn',
    'nguyenkim': 'https://www.nguyenkim.com'
}

CATEGORIES = [
    'laptop', 'pc', 'monitor', 'mainboard', 'cpu', 'vga', 'ram',
    'storage', 'case', 'cooling', 'psu', 'keyboard', 'mouse', 'chair',
    'headset', 'speaker', 'console', 'accessories', 'office-equipment', 'power-bank'
]

RATE_LIMIT_DELAY = 1.5  # seconds
TIMEOUT_MS = 30000
MAX_RETRIES = 3
