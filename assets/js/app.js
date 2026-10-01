/**
 * NovaMart storefront JavaScript.
 *
 * Progressive enhancement over server-rendered PHP: every action below has a
 * working non-JS path. Cart mutations update in place rather than reloading.
 */

document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initSearch();
    initAccountMenu();
    initPasswordToggles();
    initFilterSheet();
    initGlobalActions();
    initCartDrawer();
    initQuickView();
    initSearchModal();
    initCarousels();
    initCountdown();
    initFaqAccordion();
});

/* ==========================================================================
   Utilities
   ========================================================================== */

/* Lucide stroke icon elements, kept in sync with includes/icons.php so JS-rendered chrome
   (toasts, modals) uses the exact same family as the server-rendered UI. */
const ICONS = {
    check:   '<polyline points="20 6 9 17 4 12"/>',
    success: '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
    alert:   '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
    error:   '<circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/>',
    info:    '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
    close:   '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
    cart:    '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
    heart:   '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
    star:    '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
};

function svgIcon(name, cls = 'icon') {
    return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ''}</svg>`;
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
}

function base() {
    return window.BASE_URL || '';
}

/* ==========================================================================
   Toasts
   ========================================================================== */

function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        // Announce politely so screen readers hear cart/wishlist feedback.
        container.setAttribute('role', 'status');
        container.setAttribute('aria-live', 'polite');
        document.body.appendChild(container);
    }

    const iconName = type === 'error' ? 'alert' : type === 'warning' ? 'alert' : 'check';

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML =
        `<span class="toast-icon">${svgIcon(iconName)}</span>` +
        `<span class="toast-msg">${escapeHtml(message)}</span>` +
        `<button type="button" class="toast-close" aria-label="Dismiss">${svgIcon('close')}</button>`;

    toast.querySelector('.toast-close').addEventListener('click', () => dismissToast(toast));
    container.appendChild(toast);
    setTimeout(() => dismissToast(toast), 4000);
}

function dismissToast(toast) {
    if (!toast.isConnected) return;
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(16px)';
    toast.style.transition = 'all 180ms ease';
    setTimeout(() => toast.remove(), 180);
}

/* ==========================================================================
   Confirmation modal (replaces native confirm())
   ========================================================================== */

function confirmAction({ title, text, confirmLabel = 'Confirm', danger = false }) {
    return new Promise((resolve) => {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.innerHTML = `
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <h2 class="modal-title" id="modal-title">${escapeHtml(title)}</h2>
                <p class="modal-text">${escapeHtml(text)}</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-act="cancel">Cancel</button>
                    <button type="button" class="btn ${danger ? 'btn-danger' : 'btn-primary'}" data-act="ok">${escapeHtml(confirmLabel)}</button>
                </div>
            </div>`;

        document.body.appendChild(backdrop);
        requestAnimationFrame(() => backdrop.classList.add('is-open'));

        const okBtn = backdrop.querySelector('[data-act="ok"]');
        const previouslyFocused = document.activeElement;
        okBtn.focus();

        const close = (result) => {
            backdrop.classList.remove('is-open');
            document.removeEventListener('keydown', onKey);
            setTimeout(() => {
                backdrop.remove();
                if (previouslyFocused && previouslyFocused.focus) previouslyFocused.focus();
            }, 150);
            resolve(result);
        };

        const onKey = (e) => {
            if (e.key === 'Escape') close(false);
            if (e.key === 'Tab') {
                // Simple focus trap across the two buttons.
                const focusables = backdrop.querySelectorAll('button');
                const first = focusables[0], last = focusables[focusables.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        };

        document.addEventListener('keydown', onKey);
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) close(false);
            if (e.target.closest('[data-act="cancel"]')) close(false);
            if (e.target.closest('[data-act="ok"]')) close(true);
        });
    });
}

/* ==========================================================================
   API helper
   ========================================================================== */

async function apiFetch(url, options = {}) {
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    const token = metaCsrf ? metaCsrf.getAttribute('content') : '';

    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
        ...(options.headers || {}),
    };

    if (!(options.body instanceof FormData) && options.body && typeof options.body === 'object') {
        headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(options.body);
    }

    try {
        const response = await fetch(url, { ...options, headers });
        const data = await response.json();

        if (!response.ok || !data.success) {
            let errMsg = data.error || data.message || 'Something went wrong. Please try again.';
            if (data.data && typeof data.data === 'object' && !Array.isArray(data.data)) {
                const values = Object.values(data.data);
                if (values.length > 0 && typeof values[0] === 'string') errMsg = values[0];
            }
            // Send guests to sign-in where the endpoint demands it.
            if (data.data && data.data.require_login) {
                showToast(errMsg, 'error');
                setTimeout(() => {
                    window.location.href = `${base()}/login.php?redirect=${encodeURIComponent(window.location.href)}`;
                }, 900);
                throw new Error(errMsg);
            }
            throw new Error(errMsg);
        }
        return data;
    } catch (err) {
        showToast(err.message, 'error');
        throw err;
    }
}

function setBusy(btn, busy) {
    if (!btn) return;
    if (busy) {
        btn.classList.add('is-loading');
        btn.disabled = true;
    } else {
        btn.classList.remove('is-loading');
        btn.disabled = false;
    }
}

/* ==========================================================================
   Cart & wishlist
   ========================================================================== */

async function addToCart(productId, quantity, btn) {
    setBusy(btn, true);
    try {
        const res = await apiFetch(`${base()}/api/cart/add.php`, {
            method: 'POST',
            body: { product_id: productId, quantity: quantity },
        });
        showToast(res.message, 'success');
        if (res.data) updateCartCount(res.data.item_count);
        // Automatically open slide-out cart drawer
        if (typeof openCartDrawer === 'function') openCartDrawer();
    } catch (e) {
        /* toast shown by apiFetch */
    } finally {
        setBusy(btn, false);
    }
}

async function toggleWishlist(productId, btn) {
    const isActive = btn && btn.classList.contains('is-active');
    const endpoint = isActive ? 'remove' : 'add';
    try {
        const res = await apiFetch(`${base()}/api/wishlist/${endpoint}.php`, {
            method: 'POST',
            body: { product_id: productId },
        });
        showToast(res.message, 'success');
        if (btn) {
            btn.classList.toggle('is-active', !isActive);
            btn.setAttribute('aria-pressed', String(!isActive));
            btn.setAttribute('aria-label', !isActive ? 'Remove from wishlist' : 'Save to wishlist');
        }
    } catch (e) { /* handled */ }
}

/** Remove from the wishlist page itself — the row disappears on success. */
async function removeFromWishlist(productId, btn) {
    const ok = await confirmAction({
        title: 'Remove from wishlist?',
        text: 'This item will no longer be saved to your wishlist.',
        confirmLabel: 'Remove',
        danger: true,
    });
    if (!ok) return;

    setBusy(btn, true);
    try {
        const res = await apiFetch(`${base()}/api/wishlist/remove.php`, {
            method: 'POST',
            body: { product_id: productId },
        });
        showToast(res.message, 'success');
        const card = btn.closest('[data-wishlist-item]');
        if (card) {
            card.style.transition = 'opacity 180ms ease';
            card.style.opacity = '0';
            setTimeout(() => {
                card.remove();
                if (!document.querySelector('[data-wishlist-item]')) window.location.reload();
            }, 180);
        }
    } catch (e) {
        setBusy(btn, false);
    }
}

function updateCartCount(count) {
    document.querySelectorAll('[data-cart-count]').forEach((el) => {
        if (count > 0) {
            el.textContent = count;
            el.hidden = false;
        } else {
            el.hidden = true;
        }
    });
}

/* ==========================================================================
   Cart page — in-place updates, no full reload
   ========================================================================== */

const CartPage = {
    money(amount) {
        const sym = window.CURRENCY_SYMBOL || '';
        return sym + Number(amount).toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2,
        });
    },

    /** Repaint every derived total from a fresh cart payload. */
    render(data) {
        updateCartCount(data.item_count);

        const set = (sel, value) => {
            document.querySelectorAll(sel).forEach((el) => { el.textContent = value; });
        };

        set('[data-sum-items]', this.money(data.items_subtotal));
        set('[data-sum-total]', this.money(data.total_amount));
        set('[data-sum-count]', data.item_count);

        const offerRow = document.querySelector('[data-row-offer]');
        if (offerRow) {
            offerRow.hidden = !(data.offer_discount > 0);
            const v = offerRow.querySelector('[data-sum-offer]');
            if (v) v.textContent = '-' + this.money(data.offer_discount);
        }

        const couponRow = document.querySelector('[data-row-coupon]');
        if (couponRow) {
            couponRow.hidden = !(data.coupon_discount > 0);
            const v = couponRow.querySelector('[data-sum-coupon]');
            if (v) v.textContent = '-' + this.money(data.coupon_discount);
        }

        const ship = document.querySelector('[data-sum-shipping]');
        if (ship) {
            ship.innerHTML = data.is_free_shipping
                ? '<strong style="color:var(--success)">Free</strong>'
                : this.money(data.shipping_fee);
        }

        // Per-line totals
        (data.items || []).forEach((item) => {
            const row = document.querySelector(`[data-cart-row="${item.id}"]`);
            if (!row) return;
            const lt = row.querySelector('[data-line-total]');
            if (lt) lt.textContent = this.money(item.line_total);
            const qi = row.querySelector('.qty-input');
            if (qi) qi.value = item.quantity;
            const dec = row.querySelector('[data-qty-dec]');
            if (dec) dec.disabled = item.quantity <= 1;
            const inc = row.querySelector('[data-qty-inc]');
            if (inc) inc.disabled = item.quantity >= item.stock_quantity;
        });

        this.renderShipping(data);
    },

    renderShipping(data) {
        const box = document.querySelector('[data-ship-progress]');
        if (!box) return;
        const min = Number(data.free_shipping_min) || 0;
        const sub = Number(data.subtotal) || 0;
        const met = data.is_free_shipping;
        const remaining = Math.max(0, min - sub);

        box.classList.toggle('is-met', met);
        const txt = box.querySelector('[data-ship-text]');
        if (txt) {
            txt.textContent = met
                ? "You've qualified for free delivery"
                : `Add ${this.money(remaining)} more for free delivery`;
        }
        const fill = box.querySelector('[data-ship-fill]');
        if (fill) fill.style.width = (min > 0 ? Math.min(100, (sub / min) * 100) : 100) + '%';
    },

    async update(productId, quantity, btn) {
        if (quantity < 1) return this.remove(productId);
        setBusy(btn, true);
        try {
            const res = await apiFetch(`${base()}/api/cart/update.php`, {
                method: 'POST',
                body: { product_id: productId, quantity: quantity },
            });
            if (res.data) this.render(res.data);
        } catch (e) { /* handled */ }
        finally { setBusy(btn, false); }
    },

    async remove(productId) {
        const ok = await confirmAction({
            title: 'Remove item?',
            text: 'This product will be removed from your cart.',
            confirmLabel: 'Remove',
            danger: true,
        });
        if (!ok) return;

        try {
            const res = await apiFetch(`${base()}/api/cart/remove.php`, {
                method: 'POST',
                body: { product_id: productId },
            });
            showToast(res.message, 'success');
            const row = document.querySelector(`[data-cart-row="${productId}"]`);
            if (row) {
                row.style.transition = 'opacity 180ms ease';
                row.style.opacity = '0';
                setTimeout(() => {
                    row.remove();
                    if (!document.querySelector('[data-cart-row]')) window.location.reload();
                }, 180);
            }
            if (res.data) this.render(res.data);
        } catch (e) { /* handled */ }
    },

    async applyCoupon(e) {
        e.preventDefault();
        const input = document.getElementById('coupon-code');
        const btn = e.target.querySelector('button[type="submit"]');
        setBusy(btn, true);
        try {
            const res = await apiFetch(`${base()}/api/cart/coupon.php`, {
                method: 'POST',
                body: { code: input.value.trim() },
            });
            showToast(res.message, 'success');
            window.location.reload();
        } catch (err) { setBusy(btn, false); }
    },

    async removeCoupon(btn) {
        setBusy(btn, true);
        try {
            const res = await apiFetch(`${base()}/api/cart/coupon.php`, { method: 'DELETE' });
            showToast(res.message, 'success');
            window.location.reload();
        } catch (e) { setBusy(btn, false); }
    },
};

/* ==========================================================================
   Mobile navigation
   ========================================================================== */

function initMobileNav() {
    const toggle = document.getElementById('nav-toggle');
    const nav = document.getElementById('primary-nav');
    if (!toggle || !nav) return;

    const close = () => {
        nav.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const open = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('click', (e) => {
        if (nav.classList.contains('is-open') && !nav.contains(e.target) && !toggle.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    window.addEventListener('resize', () => { if (window.innerWidth > 860) close(); });
}

/* ==========================================================================
   Search with accessible combobox autocomplete
   ========================================================================== */

function initSearch() {
    document.querySelectorAll('[data-search]').forEach(setupSearchInstance);
}

function setupSearchInstance(wrap) {
    const input = wrap.querySelector('.search-input');
    const box = wrap.querySelector('.search-results');
    if (!input || !box) return;

    let timer = null;
    let options = [];
    let activeIndex = -1;

    const close = () => {
        box.classList.remove('is-open');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        activeIndex = -1;
    };

    const highlight = (idx) => {
        options.forEach((o, i) => o.classList.toggle('is-active', i === idx));
        if (idx >= 0 && options[idx]) {
            input.setAttribute('aria-activedescendant', options[idx].id);
            options[idx].scrollIntoView({ block: 'nearest' });
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    };

    const run = async (query) => {
        try {
            const res = await fetch(`${base()}/api/products/search.php?q=${encodeURIComponent(query)}`);
            const json = await res.json();

            if (json.success && json.data && json.data.length) {
                box.innerHTML = json.data.map((item, i) => `
                    <a href="${item.url}" class="search-option" role="option" id="search-opt-${i}" aria-selected="false">
                        <img src="${item.image_url}" alt="" loading="lazy">
                        <span class="grow">
                            <span class="search-option-name">${escapeHtml(item.name)}</span>
                            <span class="search-option-price">${escapeHtml(item.category_name || '')} · ${escapeHtml(item.formatted_price)}</span>
                        </span>
                    </a>`).join('');
            } else {
                box.innerHTML = `<div class="search-empty">No products match “${escapeHtml(query)}”</div>`;
            }

            options = Array.from(box.querySelectorAll('.search-option'));
            activeIndex = -1;
            box.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        } catch (e) {
            close();
        }
    };

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(timer);
        if (q.length < 2) { close(); return; }
        timer = setTimeout(() => run(q), 220);
    });

    input.addEventListener('keydown', (e) => {
        if (!box.classList.contains('is-open')) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = (activeIndex + 1) % options.length;
            highlight(activeIndex);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = activeIndex <= 0 ? options.length - 1 : activeIndex - 1;
            highlight(activeIndex);
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && options[activeIndex]) {
                e.preventDefault();
                window.location.href = options[activeIndex].getAttribute('href');
            }
        } else if (e.key === 'Escape') {
            close();
            input.blur();
        } else if (e.key === 'Home') {
            if (options.length) { e.preventDefault(); activeIndex = 0; highlight(0); }
        } else if (e.key === 'End') {
            if (options.length) { e.preventDefault(); activeIndex = options.length - 1; highlight(activeIndex); }
        }
    });

    document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) close(); });
}

/* ==========================================================================
   Account dropdown
   ========================================================================== */

function initAccountMenu() {
    const trigger = document.getElementById('account-trigger');
    const menu = document.getElementById('account-menu');
    if (!trigger || !menu) return;

    const close = () => {
        menu.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
    };

    trigger.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        const open = menu.classList.toggle('is-open');
        trigger.setAttribute('aria-expanded', String(open));
        if (open) {
            const first = menu.querySelector('a');
            if (first) first.focus();
        }
    });

    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) close();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) { close(); trigger.focus(); }
    });
}

/* ==========================================================================
   Password visibility
   ========================================================================== */

/* Solid eye / eye-off, matching includes/icons.php. */
const EYE = '<path d="M12 4.75c-5.6 0-9.42 4.73-10.5 6.4a1.55 1.55 0 000 1.7c1.08 1.67 4.9 6.4 10.5 6.4s9.42-4.73 10.5-6.4a1.55 1.55 0 000-1.7c-1.08-1.67-4.9-6.4-10.5-6.4zm0 3.4a3.85 3.85 0 110 7.7 3.85 3.85 0 010-7.7z"/>';
const EYE_OFF = '<path d="M3.28 2.44a1.25 1.25 0 00-1.77 1.77l3.3 3.3C3 8.9 1.97 10.4 1.5 11.15a1.55 1.55 0 000 1.7c1.08 1.67 4.9 6.4 10.5 6.4a11.6 11.6 0 004.9-1.1l3.31 3.31a1.25 1.25 0 001.77-1.77zm5.98 7.75 4.55 4.55a3.85 3.85 0 01-4.55-4.55zM12 4.75c-1.06 0-2.05.17-2.96.45l2.4 2.4A3.85 3.85 0 0115.4 12c0 .19-.1.37-.04.55l3.32 3.32c1.72-1.5 2.95-3.28 3.32-3.87a1.55 1.55 0 000-1.7c-1.08-1.67-4.9-6.4-10.5-6.4z"/>';

function initPasswordToggles() {
    document.querySelectorAll('.pw-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const field = btn.closest('.field-password').querySelector('input');
            const show = field.type === 'password';
            field.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            const svg = btn.querySelector('svg');
            if (svg) svg.innerHTML = show ? EYE_OFF : EYE;
        });
    });
}

/* ==========================================================================
   Filter sheet (mobile)
   ========================================================================== */

function initFilterSheet() {
    const panel = document.getElementById('filters');
    const openBtn = document.getElementById('filter-open');
    const closeBtn = document.getElementById('filter-close');
    if (!panel || !openBtn) return;

    let scrim = document.querySelector('.filter-scrim');
    if (!scrim) {
        scrim = document.createElement('div');
        scrim.className = 'filter-scrim';
        document.body.appendChild(scrim);
    }

    const open = () => {
        panel.classList.add('is-open');
        scrim.classList.add('is-open');
        openBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
        if (closeBtn) closeBtn.focus();
    };
    const close = () => {
        panel.classList.remove('is-open');
        scrim.classList.remove('is-open');
        openBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
        openBtn.focus();
    };

    openBtn.addEventListener('click', open);
    if (closeBtn) closeBtn.addEventListener('click', close);
    scrim.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && panel.classList.contains('is-open')) close();
    });
}

/* ==========================================================================
   Delegated actions
   ========================================================================== */

function initGlobalActions() {
    document.addEventListener('click', (e) => {
        const cartBtn = e.target.closest('[data-add-to-cart]');
        if (cartBtn) {
            e.preventDefault();
            const qtyInput = document.getElementById('product-qty');
            const qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
            addToCart(parseInt(cartBtn.dataset.addToCart, 10), qty, cartBtn);
            return;
        }

        const wishBtn = e.target.closest('[data-wishlist-toggle]');
        if (wishBtn) {
            e.preventDefault();
            toggleWishlist(parseInt(wishBtn.dataset.wishlistToggle, 10), wishBtn);
            return;
        }

        const wishRemove = e.target.closest('[data-wishlist-remove]');
        if (wishRemove) {
            e.preventDefault();
            removeFromWishlist(parseInt(wishRemove.dataset.wishlistRemove, 10), wishRemove);
            return;
        }

        const qtyBtn = e.target.closest('[data-qty-dec], [data-qty-inc]');
        if (qtyBtn) {
            e.preventDefault();
            const row = qtyBtn.closest('[data-cart-row]');
            const input = row.querySelector('.qty-input');
            const delta = qtyBtn.hasAttribute('data-qty-inc') ? 1 : -1;
            const next = (parseInt(input.value, 10) || 1) + delta;
            CartPage.update(parseInt(row.dataset.cartRow, 10), next, qtyBtn);
            return;
        }

        const removeBtn = e.target.closest('[data-cart-remove]');
        if (removeBtn) {
            e.preventDefault();
            CartPage.remove(parseInt(removeBtn.dataset.cartRemove, 10));
            return;
        }

        const couponRemove = e.target.closest('[data-coupon-remove]');
        if (couponRemove) {
            e.preventDefault();
            CartPage.removeCoupon(couponRemove);
        }
    });

    // Typed quantity on the cart page
    document.addEventListener('change', (e) => {
        const input = e.target.closest('[data-cart-row] .qty-input');
        if (!input) return;
        const row = input.closest('[data-cart-row]');
        CartPage.update(parseInt(row.dataset.cartRow, 10), parseInt(input.value, 10) || 1, null);
    });

    const couponForm = document.getElementById('coupon-form');
    if (couponForm) couponForm.addEventListener('submit', (e) => CartPage.applyCoupon(e));

    // Product detail quantity stepper
    document.querySelectorAll('[data-pdp-qty]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = document.getElementById('product-qty');
            if (!input) return;
            const delta = parseInt(btn.dataset.pdpQty, 10);
            const min = parseInt(input.min, 10) || 1;
            const max = parseInt(input.max, 10) || 99;
            input.value = Math.min(max, Math.max(min, (parseInt(input.value, 10) || 1) + delta));
        });
    });
}

/* ==========================================================================
   Product gallery
   ========================================================================== */

function switchProductImage(src, thumb) {
    const main = document.getElementById('gallery-image');
    if (main) main.src = src;
    document.querySelectorAll('.gallery-thumb').forEach((t) => {
        t.classList.remove('is-active');
        t.setAttribute('aria-selected', 'false');
    });
    thumb.classList.add('is-active');
    thumb.setAttribute('aria-selected', 'true');
}

/* ==========================================================================
   Slide-out Cart Drawer
   ========================================================================== */

function initCartDrawer() {
    const overlay = document.getElementById('cart-drawer-overlay');
    const drawer = document.getElementById('cart-drawer');
    const closeBtn = document.getElementById('cart-drawer-close');

    if (closeBtn) closeBtn.addEventListener('click', closeCartDrawer);
    if (overlay) overlay.addEventListener('click', closeCartDrawer);

    document.querySelectorAll('[data-open-cart-drawer]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openCartDrawer();
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer && drawer.classList.contains('is-open')) {
            closeCartDrawer();
        }
    });

    // Delegated controls inside drawer
    if (drawer) {
        drawer.addEventListener('click', async (e) => {
            const incBtn = e.target.closest('[data-drawer-inc]');
            const decBtn = e.target.closest('[data-drawer-dec]');
            const rmBtn = e.target.closest('[data-drawer-remove]');

            if (incBtn || decBtn) {
                e.preventDefault();
                const itemEl = (incBtn || decBtn).closest('[data-drawer-item-id]');
                const itemId = parseInt(itemEl.dataset.drawerItemId, 10);
                const input = itemEl.querySelector('.drawer-qty-val');
                let qty = parseInt(input.textContent, 10) || 1;
                qty = incBtn ? qty + 1 : Math.max(0, qty - 1);

                if (qty === 0) {
                    await removeDrawerItem(itemId);
                } else {
                    await updateDrawerItem(itemId, qty);
                }
            } else if (rmBtn) {
                e.preventDefault();
                const itemEl = rmBtn.closest('[data-drawer-item-id]');
                const itemId = parseInt(itemEl.dataset.drawerItemId, 10);
                await removeDrawerItem(itemId);
            }
        });
    }
}

async function openCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-drawer-overlay');
    if (!drawer) return;

    drawer.classList.add('is-open');
    if (overlay) overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    await refreshCartDrawer();
}

function closeCartDrawer() {
    const drawer = document.getElementById('cart-drawer');
    const overlay = document.getElementById('cart-drawer-overlay');
    if (drawer) drawer.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-open');
    document.body.style.overflow = '';
}

async function refreshCartDrawer() {
    const bodyEl = document.getElementById('cart-drawer-items-list');
    const subtotalEl = document.getElementById('drawer-subtotal');
    const totalEl = document.getElementById('drawer-total');
    const progressFill = document.getElementById('drawer-shipping-fill');
    const progressMsg = document.getElementById('drawer-shipping-msg');

    if (!bodyEl) return;

    try {
        const res = await apiFetch(`${base()}/api/cart/index.php`);
        const cart = res.data;
        updateCartCount(cart.item_count);

        if (!cart.items || cart.items.length === 0) {
            bodyEl.innerHTML = `
                <div style="text-align:center;padding:var(--space-12) var(--space-4);">
                    <div style="width:64px;height:64px;border-radius:var(--radius-full);background:var(--bg-subtle);margin:0 auto var(--space-4);display:flex;align-items:center;justify-content:center;color:var(--text-muted);">
                        ${svgIcon('info')}
                    </div>
                    <h3 style="font-size:var(--text-lg);font-weight:700;margin-bottom:var(--space-2);">Your cart is empty</h3>
                    <p style="color:var(--text-muted);font-size:var(--text-sm);margin-bottom:var(--space-6);">Looks like you haven't added anything yet.</p>
                    <a href="${base()}/products.php" class="btn btn-primary" onclick="closeCartDrawer()">Browse products</a>
                </div>
            `;
            if (subtotalEl) subtotalEl.textContent = CartPage.money(0);
            if (totalEl) totalEl.textContent = CartPage.money(0);
            if (progressFill) progressFill.style.width = '0%';
            if (progressMsg) progressMsg.textContent = 'Add ৳2,000 or more for free delivery';
            return;
        }

        // Render items
        bodyEl.innerHTML = cart.items.map(item => `
            <div class="drawer-item" data-drawer-item-id="${item.id}">
                <img src="${item.image ? (item.image.startsWith('http') ? item.image : base() + '/' + item.image.replace(/^\//, '')) : base() + '/assets/images/placeholder.svg'}"
                     alt="${escapeHtml(item.name)}" class="drawer-item-img">
                <div>
                    <div style="display:flex;justify-content:space-between;gap:8px;">
                        <div class="drawer-item-title">${escapeHtml(item.name)}</div>
                        <button type="button" class="link-danger" data-drawer-remove aria-label="Remove item" style="padding:0;color:var(--text-subtle);cursor:pointer;">
                            ${svgIcon('close', 'icon-xs')}
                        </button>
                    </div>
                    <div class="drawer-item-price">${CartPage.money(item.price)}</div>
                    <div class="drawer-item-controls">
                        <div class="qty" style="height:32px;">
                            <button type="button" class="qty-btn" data-drawer-dec style="width:28px;">&minus;</button>
                            <span class="drawer-qty-val" style="padding:0 8px;font-size:13px;font-weight:600;">${item.quantity}</span>
                            <button type="button" class="qty-btn" data-drawer-inc style="width:28px;">&plus;</button>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:var(--primary);">${CartPage.money(item.price * item.quantity)}</span>
                    </div>
                </div>
            </div>
        `).join('');

        if (subtotalEl) subtotalEl.textContent = CartPage.money(cart.subtotal || cart.items_subtotal);
        if (totalEl) totalEl.textContent = CartPage.money(cart.total_amount);

        // Shipping threshold (৳2,000)
        const threshold = 2000;
        const currentSub = parseFloat(cart.subtotal || cart.items_subtotal) || 0;
        const pct = Math.min(100, Math.round((currentSub / threshold) * 100));

        if (progressFill) progressFill.style.width = `${pct}%`;
        if (progressMsg) {
            if (currentSub >= threshold) {
                progressMsg.innerHTML = `You qualified for <strong>Free Delivery</strong>!`;
            } else {
                const diff = threshold - currentSub;
                progressMsg.innerHTML = `Add <strong>${CartPage.money(diff)}</strong> more for <strong>Free Delivery</strong>`;
            }
        }
    } catch (e) {
        console.error('Failed to load cart drawer:', e);
    }
}

async function updateDrawerItem(cartItemId, newQty) {
    try {
        await apiFetch(`${base()}/api/cart/update.php`, {
            method: 'POST',
            body: { cart_id: cartItemId, quantity: newQty },
        });
        await refreshCartDrawer();
    } catch (e) { /* handled */ }
}

async function removeDrawerItem(cartItemId) {
    try {
        await apiFetch(`${base()}/api/cart/remove.php`, {
            method: 'POST',
            body: { cart_id: cartItemId },
        });
        showToast('Item removed', 'success');
        await refreshCartDrawer();
    } catch (e) { /* handled */ }
}

/* ==========================================================================
   Quick View Modal
   ========================================================================== */

function initQuickView() {
    const modal = document.getElementById('quickview-modal');
    const closeBtn = document.getElementById('quickview-close');

    if (closeBtn) closeBtn.addEventListener('click', closeQuickView);
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeQuickView();
        });
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-quick-view]');
        if (btn) {
            e.preventDefault();
            const pid = parseInt(btn.dataset.quickView, 10);
            if (pid) openQuickView(pid);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
            closeQuickView();
        }
    });
}

async function openQuickView(productId) {
    const modal = document.getElementById('quickview-modal');
    const body = document.getElementById('quickview-content');
    if (!modal || !body) return;

    body.innerHTML = `
        <div style="text-align:center;padding:var(--space-12);">
            <div style="display:inline-block;width:36px;height:36px;border:3px solid var(--border);border-top-color:var(--accent);border-radius:50%;animation:spin 0.8s linear infinite;"></div>
            <p style="margin-top:var(--space-4);color:var(--text-muted);font-size:var(--text-sm);">Loading product...</p>
        </div>
    `;

    modal.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    try {
        const res = await apiFetch(`${base()}/api/products/index.php?id=${productId}`);
        const p = res.data;

        const isSale = p.sale_price && parseFloat(p.sale_price) < parseFloat(p.price);
        const currentPrice = isSale ? p.sale_price : p.price;
        const discountPct = isSale ? Math.round(((p.price - p.sale_price) / p.price) * 100) : 0;
        const imgSrc = p.primary_image ? (p.primary_image.startsWith('http') ? p.primary_image : base() + '/' + p.primary_image.replace(/^\//, '')) : base() + '/assets/images/placeholder.svg';

        body.innerHTML = `
            <div class="quickview-grid">
                <div style="background:var(--bg-subtle);border-radius:var(--radius-lg);padding:var(--space-6);display:flex;align-items:center;justify-content:center;">
                    <img src="${imgSrc}" alt="${escapeHtml(p.name)}" style="max-height:340px;width:auto;object-fit:contain;filter:drop-shadow(0 10px 20px rgba(0,0,0,0.06));">
                </div>
                <div>
                    <span class="badge-trending" style="margin-bottom:var(--space-2);display:inline-block;">Featured</span>
                    <h2 style="font-size:var(--text-2xl);font-weight:700;margin-bottom:var(--space-3);letter-spacing:-0.02em;">${escapeHtml(p.name)}</h2>
                    
                    <div style="display:flex;align-items:baseline;gap:var(--space-3);margin-bottom:var(--space-4);">
                        <span style="font-size:var(--text-2xl);font-weight:800;color:var(--primary);">${CartPage.money(currentPrice)}</span>
                        ${isSale ? `<span style="text-decoration:line-through;color:var(--text-muted);">${CartPage.money(p.price)}</span><span class="badge-hot">Save ${discountPct}%</span>` : ''}
                    </div>

                    <p style="color:var(--text-muted);font-size:var(--text-sm);line-height:1.5;margin-bottom:var(--space-6);">
                        ${escapeHtml(p.short_description || p.description || 'Genuine product with official warranty.')}
                    </p>

                    <div style="display:flex;gap:var(--space-4);align-items:center;margin-bottom:var(--space-6);">
                        <div class="qty" style="height:44px;">
                            <button type="button" class="qty-btn" id="qv-qty-dec">&minus;</button>
                            <input type="number" id="qv-qty-input" value="1" min="1" max="99" class="qty-input" style="width:48px;">
                            <button type="button" class="qty-btn" id="qv-qty-inc">&plus;</button>
                        </div>
                        <button type="button" class="btn btn-primary" id="qv-add-btn" style="flex:1;height:44px;">
                            ${svgIcon('cart')} Add to cart
                        </button>
                    </div>

                    <a href="${base()}/product.php?id=${p.id}" class="btn btn-secondary" style="width:100%;height:44px;justify-content:center;">
                        View product details
                    </a>
                </div>
            </div>
        `;

        const dec = document.getElementById('qv-qty-dec');
        const inc = document.getElementById('qv-qty-inc');
        const input = document.getElementById('qv-qty-input');
        const addBtn = document.getElementById('qv-add-btn');

        if (dec && inc && input) {
            dec.addEventListener('click', () => { input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1); });
            inc.addEventListener('click', () => { input.value = Math.min(99, (parseInt(input.value, 10) || 1) + 1); });
        }

        if (addBtn && input) {
            addBtn.addEventListener('click', async () => {
                const qty = parseInt(input.value, 10) || 1;
                await addToCart(p.id, qty, addBtn);
                closeQuickView();
            });
        }

    } catch (e) {
        body.innerHTML = `
            <div style="text-align:center;padding:var(--space-8);">
                <p style="color:var(--danger);">Failed to load product details. Please try again.</p>
            </div>
        `;
    }
}

function closeQuickView() {
    const modal = document.getElementById('quickview-modal');
    if (modal) modal.classList.remove('is-open');
    document.body.style.overflow = '';
}

/* ==========================================================================
   Search Modal (⌘K or trigger)
   ========================================================================== */

function initSearchModal() {
    const modal = document.getElementById('search-modal');
    const input = document.getElementById('search-modal-input');
    const results = document.getElementById('search-modal-results');
    const closeBtn = document.getElementById('search-modal-close');

    if (!modal || !input) return;

    document.querySelectorAll('[data-open-search-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openSearchModal();
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeSearchModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeSearchModal();
    });

    document.addEventListener('keydown', (e) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (modal.classList.contains('is-open')) closeSearchModal();
            else openSearchModal();
        }
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeSearchModal();
        }
    });

    let debounceTimer;
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = input.value.trim();
        if (q.length < 2) {
            if (results) results.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(async () => {
            if (results) results.innerHTML = '<p style="padding:var(--space-4);color:var(--text-muted);">Searching...</p>';
            try {
                const res = await apiFetch(`${base()}/api/products/search.php?q=${encodeURIComponent(q)}`);
                const products = res.data || [];
                if (products.length === 0) {
                    results.innerHTML = `<p style="padding:var(--space-4);color:var(--text-muted);">No products found for "${escapeHtml(q)}".</p>`;
                    return;
                }
                results.innerHTML = products.slice(0, 6).map(p => `
                    <a href="${base()}/product.php?id=${p.id}" style="display:flex;align-items:center;gap:var(--space-3);padding:var(--space-3);border-radius:var(--radius-md);transition:background var(--transition);text-decoration:none;" onmouseover="this.style.background='var(--bg-subtle)'" onmouseout="this.style.background='transparent'">
                        <img src="${p.primary_image ? (p.primary_image.startsWith('http') ? p.primary_image : base() + '/' + p.primary_image.replace(/^\//, '')) : base() + '/assets/images/placeholder.svg'}"
                             style="width:44px;height:44px;object-fit:contain;border-radius:var(--radius-sm);background:var(--bg-subtle);" alt="${escapeHtml(p.name)}">
                        <div style="flex:1;">
                            <div style="font-weight:600;font-size:var(--text-sm);color:var(--text-main);">${escapeHtml(p.name)}</div>
                            <div style="font-size:var(--text-xs);color:var(--accent);font-weight:700;">${CartPage.money(p.sale_price || p.price)}</div>
                        </div>
                        <span style="color:var(--text-subtle);font-size:var(--text-xs);">&rarr;</span>
                    </a>
                `).join('');
            } catch (e) {
                if (results) results.innerHTML = '<p style="padding:var(--space-4);color:var(--danger);">Error fetching results.</p>';
            }
        }, 200);
    });
}

function openSearchModal() {
    const modal = document.getElementById('search-modal');
    const input = document.getElementById('search-modal-input');
    if (modal) {
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        if (input) setTimeout(() => input.focus(), 50);
    }
}

function closeSearchModal() {
    const modal = document.getElementById('search-modal');
    if (modal) {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }
}

/* ==========================================================================
   Trending Carousel Slider
   ========================================================================== */

function initCarousels() {
    const tracks = document.querySelectorAll('.carousel-track');
    tracks.forEach(track => {
        const container = track.closest('.carousel-container');
        if (!container) return;

        const prevBtn = container.querySelector('.carousel-prev');
        const nextBtn = container.querySelector('.carousel-next');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                track.scrollBy({ left: -320, behavior: 'smooth' });
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({ left: 320, behavior: 'smooth' });
            });
        }
    });
}

/* ==========================================================================
   Flash Sale Countdown Timer
   ========================================================================== */

function initCountdown() {
    const hoursEl = document.getElementById('cd-hours');
    const minsEl = document.getElementById('cd-mins');
    const secsEl = document.getElementById('cd-secs');

    if (!hoursEl || !minsEl || !secsEl) return;

    function update() {
        const now = new Date();
        // Target is midnight today
        const target = new Date();
        target.setHours(23, 59, 59, 999);

        let diff = Math.floor((target - now) / 1000);
        if (diff < 0) diff = 86400 + diff;

        const h = Math.floor(diff / 3600);
        const m = Math.floor((diff % 3600) / 60);
        const s = diff % 60;

        hoursEl.textContent = String(h).padStart(2, '0');
        minsEl.textContent = String(m).padStart(2, '0');
        secsEl.textContent = String(s).padStart(2, '0');
    }

    update();
    setInterval(update, 1000);
}

/* ==========================================================================
   FAQ Accordion
   ========================================================================== */

function initFaqAccordion() {
    document.querySelectorAll('.faq-question').forEach(q => {
        q.addEventListener('click', () => {
            const item = q.closest('.faq-item');
            const wasOpen = item.classList.contains('is-open');

            // Close siblings
            const parent = item.parentElement;
            if (parent) {
                parent.querySelectorAll('.faq-item').forEach(sibling => {
                    sibling.classList.remove('is-open');
                });
            }

            if (!wasOpen) item.classList.add('is-open');
        });
    });
}

