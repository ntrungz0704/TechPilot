document.addEventListener('DOMContentLoaded', function() {
    const bell = document.getElementById('adminNotifBell');
    const badge = document.getElementById('adminNotifBadge');
    const dropdown = document.getElementById('adminNotifDropdown');
    const list = document.getElementById('adminNotifList');
    const markReadBtn = document.getElementById('markReadNotifBtn');

    if (!bell || !badge || !dropdown || !list) return;

    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    
    const apiMeta = document.querySelector('meta[name="admin-notif-api"]');
    const markApiMeta = document.querySelector('meta[name="admin-notif-mark-api"]');
    const baseUrlMeta = document.querySelector('meta[name="app-base-url"]');
    
    const apiUrl = apiMeta ? apiMeta.getAttribute('content') : '';
    const markApiUrl = markApiMeta ? markApiMeta.getAttribute('content') : '';
    const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : window.location.origin;

    let adminRoot = '/admin';
    try {
        if (baseUrl) {
            const baseObj = new URL(baseUrl, window.location.origin);
            adminRoot = baseObj.pathname.replace(/\/$/, '') + '/admin';
        }
    } catch(e) {}

    function renderEmptyState(message) {
        list.replaceChildren();
        const div = document.createElement('div');
        div.style.fontSize = '12px';
        div.style.color = 'var(--text-secondary)';
        div.style.textAlign = 'center';
        div.style.padding = '15px 0';
        div.textContent = message;
        list.appendChild(div);
    }

    let isFetching = false;

    function parseApiResponse(res) {
        const contentType = res.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw { status: 500, message: 'Invalid content type' };
        }
        return res.json().then(data => {
            if (!res.ok || data.success !== true) {
                throw { status: res.status, data: data };
            }
            return data;
        }).catch(e => {
            if (e.status) throw e;
            throw { status: 500, message: 'Malformed JSON' };
        });
    }

    function handleApiError(err) {
        console.error("API Error:", err);
        let msg = 'Không thể cập nhật thông báo.';
        if (err && err.status === 403) {
            msg = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
        }
        renderEmptyState(msg);
    }

    function fetchNotifications() {
        if (!apiUrl) return;
        if (isFetching) return;
        isFetching = true;
        
        fetch(apiUrl)
            .then(parseApiResponse)
            .then(data => {
                if (data.unread > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = data.unread;
                } else {
                    badge.style.display = 'none';
                }

                if (data.items && data.items.length > 0) {
                    list.replaceChildren(); // Xóa content cũ
                    data.items.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.style.padding = '8px 10px';
                        itemDiv.style.borderRadius = '6px';
                        itemDiv.style.background = item.is_read == 0 ? 'rgba(10,91,255,0.06)' : '#F9FAFB';
                        itemDiv.style.borderLeft = '3px solid ' + (item.is_read == 0 ? '#0A5BFF' : '#CBD5E1');
                        itemDiv.style.cursor = 'pointer';
                        itemDiv.style.marginBottom = '8px';

                        const titleDiv = document.createElement('div');
                        titleDiv.style.fontSize = '12.5px';
                        titleDiv.style.fontWeight = '700';
                        titleDiv.style.color = 'var(--text-primary)';
                        titleDiv.style.marginBottom = '2px';
                        titleDiv.textContent = item.title;

                        const contentDiv = document.createElement('div');
                        contentDiv.style.fontSize = '11.5px';
                        contentDiv.style.color = 'var(--text-secondary)';
                        contentDiv.style.lineHeight = '1.4';
                        contentDiv.textContent = item.content;

                        const timeSmall = document.createElement('small');
                        timeSmall.style.fontSize = '10px';
                        timeSmall.style.color = '#94A3B8';
                        timeSmall.style.display = 'block';
                        timeSmall.style.marginTop = '4px';
                        timeSmall.textContent = item.created_at;

                        itemDiv.appendChild(titleDiv);
                        itemDiv.appendChild(contentDiv);
                        itemDiv.appendChild(timeSmall);
                        
                        itemDiv.addEventListener('click', function() {
                            handleNotificationClick(item.id, item.link);
                        });

                        list.appendChild(itemDiv);
                    });
                } else {
                    renderEmptyState('Không có thông báo mới');
                }
            })
            .catch(err => {
                console.error("Fetch error:", err);
                renderEmptyState('Lỗi tải thông báo');
            })
            .finally(() => {
                isFetching = false;
            });
    }

    bell.addEventListener('click', function(e) {
        e.stopPropagation();
        const isOpen = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
    });

    document.addEventListener('click', function(e) {
        if (!bell.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    function validateLink(link) {
        if (!link || typeof link !== 'string' || /\s/.test(link)) return null;
        if (/[\u0000-\u001F\u007F]/.test(link)) return null;
        try {
            const urlObj = new URL(link, window.location.origin);
            if (urlObj.username || urlObj.password) return null;
            if (urlObj.protocol !== 'http:' && urlObj.protocol !== 'https:') return null;
            if (urlObj.origin !== window.location.origin) return null;
            const path = urlObj.pathname;
            if (path !== adminRoot && !path.startsWith(adminRoot + '/')) return null;
            return urlObj.href;
        } catch (e) {
            return null;
        }
    }

    function handleNotificationClick(id, link) {
        if (!markApiUrl) return;
        
        const formData = new FormData();
        formData.append('id', id);
        
        fetch(markApiUrl, { 
            method: 'POST', 
            body: formData,
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(parseApiResponse)
        .then(data => {
            const validUrl = validateLink(link);
            if (validUrl) {
                window.location.href = validUrl;
            } else {
                fetchNotifications();
            }
        })
        .catch(handleApiError);
    }

    if (markReadBtn) {
        markReadBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (!markApiUrl) return;
            
            fetch(markApiUrl, { 
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(parseApiResponse)
            .then(data => {
                badge.style.display = 'none';
                fetchNotifications();
            })
            .catch(handleApiError);
        });
    }

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
});
