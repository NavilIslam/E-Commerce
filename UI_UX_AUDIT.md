# NovaMart e-Store — UI/UX Audit

**Audited:** 2026-08-25
**Scope:** All customer-facing pages, shared components, design system, responsive behavior, accessibility
**Method:** Source review of all 21 customer pages + 5 shared includes, plus live testing against a running instance (Apache/MySQL, seeded catalog of 15 products), authenticated as a demo customer with a populated cart, measured at 1440 / 1024 / 768 / 414 / 375px.
**Phase:** Audit only — no code changes made.

---

## 1. Executive Summary

NovaMart is a **functionally impressive, visually amateur** application. The backend is genuinely competent: 16 model classes, 18 REST endpoints, CSRF protection everywhere, prepared statements, guest→user cart merging, a real coupon/offer engine, verified-buyer review gating, and a 28-page admin panel. That engineering deserves respect and should not be touched.

The presentation layer does not match it. The site reads as a *template with data poured into it* rather than a retailer with a point of view. Three findings define the gap:

1. **The homepage advertises a catalog it does not have.** 24 product cards render only 13 unique products — the same mouse appears 3 times on one screen. 19 of 24 cards say "Featured." 24 of 24 say "on sale." Every signal that should mean *something* has been applied to *everything*, so nothing signals anything.

2. **Mobile commerce is broken, not merely ugly.** On a 375px phone the cart's Remove button renders at x=584px in a 375px viewport with horizontal scrolling disabled — **customers physically cannot remove items from their cart on a phone.** On the same viewport the product listing gives 260px to the filter sidebar and pushes the products themselves off-screen.

3. **The cart displays arithmetic that does not add up.** Subtotal ৳134,420 − Offer Discount ৳6,960 = Total ৳134,420. This appears on both cart and checkout. A customer who reads the numbers concludes the store is either broken or dishonest. This is the single most damaging item in the document.

Beneath those: 95 emoji used as production iconography, 336 inline `style=` attributes competing with a 1,100-line stylesheet, product photography spanning aspect ratios from 0.65 to 1.50 forced through one crop, no visible keyboard focus indicator anywhere, and 60 of 91 interactive elements below the 44px touch minimum.

**The good news:** almost none of this requires backend work. The data layer already returns everything a professional presentation needs — it is being displayed carelessly, not missing.

---

## 2. Current Design Diagnosis

### What the system actually is

| Layer | Reality |
|---|---|
| Pages | 21 customer-facing PHP pages, 28 admin pages |
| Shared components | Only 3 real ones: `header.php`, `footer.php`, `product-card.php` |
| Styling | One 1,100-line `assets/css/style.css` + **336 inline `style=` attributes** |
| JS | Single 214-line `app.js` (toast, apiFetch, autocomplete, cart/wishlist delegation) + page-local `<script>` blocks |
| Data | MySQL, 16 model classes, well-normalized |
| Assets | 17 product JPEGs, 3 banners, 2 avatars, 1 placeholder |

### The core structural problem

**There are only three shared components, so every page reinvents its own layout in inline styles.** The consequences compound:

- `products.php` has 31 inline styles, `product.php` 38, `checkout.php` 35, `order.php` 35.
- Card containers are re-declared per page instead of being a `.card` class — `background:#fff;border:1px solid var(--border-color);border-radius:var(--radius-md);padding:1.5rem;box-shadow:var(--shadow-sm)` is retyped in at least 9 places.
- Page titles are re-declared per page: `font-size:1.85rem;font-weight:800` appears in cart, checkout, orders, profile, categories, search, wishlist, products — eight copies of what should be one `.page-title`.
- Because layout lives in inline styles, **media queries cannot reach it.** This is the root cause of nearly every responsive failure in §18: `grid-template-columns:260px 1fr` written inline on `products.php` cannot be overridden by any breakpoint in the stylesheet.

This is the highest-leverage fix in the entire document. It is not cosmetic — it is what makes the responsive and consistency problems *fixable at all*.

### Design primitives currently in play

| Primitive | Current state | Verdict |
|---|---|---|
| Font | Inter 400/500/600/700/800 — 5 weights | One family is right; 5 weights is 2 too many |
| Radius | 6 / 10 / 16 / 9999px, 4 tokens + hardcoded `4px` in ~6 inline spots | Token set is sane, usage leaks |
| Shadow | 3 tokens (sm/md/lg), applied fairly consistently | Acceptable |
| Spacing | **No scale.** Ad-hoc `0.25/0.35/0.4/0.5/0.65/0.75/0.85/1/1.25/1.5/1.75/2/2.5/3/4rem` | Chaotic — 15+ arbitrary values |
| Color | 16 CSS vars, but `#f8fafc`/`#f1f5f9`/`#fff`/`#94a3b8` hardcoded throughout | Tokens exist and are bypassed |
| Icons | **Emoji only. No icon system.** | The most visible amateur signal |
| Buttons | 7 variants, heights drift 36–48px depending on context | Needs one spec |

---

## 3. AI-Slop Detection

The prompt asked to be brutal here. Each item: **what is wrong → why it reads artificial → what a real retailer does.**

### 3.1 The same products, over and over — **Critical / Content**

**What:** Homepage renders 24 product cards containing 13 unique products. Measured duplicates: Logitech MX Master 3S ×3, Oxford Shirt ×3, Floral Maxi Dress ×3, Sony XM5 ×2, MacBook Air ×2, Office Chair ×2, Serum ×2, Wallet ×2.

**Why it reads artificial:** The page is built from four sections — Featured, Best Sellers, New Arrivals, (+Flash Sale) — each independently querying 8 products from a 15-product catalog. No real merchandiser would show the same mouse three times on one screen. It is the visual signature of *sections generated to fill a template* rather than curated.

**What a real retailer does:** Deduplicate across sections (track shown IDs, exclude from later queries). If the catalog cannot fill four sections, **show fewer sections** — a homepage with one strong 8-product row and a good editorial block outperforms four padded ones. Section count should be a function of inventory, not layout.

### 3.2 Badges applied to everything — **Critical / Visual + Conversion**

**What:** 19 of 24 homepage cards carry "Featured." 24 of 24 carry a discount badge. Every product in the seeded catalog has a `sale_price` below `price`.

**Why it reads artificial:** A badge is a *contrast* device — it works because most items lack it. At 79% and 100% saturation these are wallpaper. Worse, a store where literally everything is discounted reads as fake-MSRP inflation, which is exactly what discount-regulation bodies target. It actively erodes trust.

**What a real retailer does:** "Featured" is an editorial slot — cap it at 4–8 SKUs and *never* render the badge inside a section already titled "Featured Products" (it's redundant there by definition). Discounts get badges only above a threshold (≥15%), and the strikethrough price carries the rest of the story.

### 3.3 Emoji as the entire icon system — **High / Visual**

**What:** 95 emoji glyphs across customer-facing markup. Load-bearing examples: `📂` for every category (3rem on `categories.php`), `⚡` as the brand logo mark, `🛒` in every Add to Cart button, `❤️` as the wishlist control, `🗑️` as cart remove, `📦⭐🔥✨` as section headers, `🚚🔒🔄💯` as product-page trust badges, `💳` as three payment-method "logos" in the footer, `👤` as the account avatar, `📍💳💵📱` in checkout.

**Why it reads artificial:** Emoji render differently on every OS (Segoe UI Emoji on Windows, Apple Color Emoji on Mac, Noto on Android) — the brand mark is literally a different picture per visitor. They cannot be recolored, sized precisely, or aligned to a grid. `📂` as a category icon is the single most recognizable "generated site" tell there is: it's a *file folder* representing "Beauty & Personal Care."

**What a real retailer does:** One outline icon set (Lucide, Phosphor, Heroicons — all free, all inline SVG), single stroke width, currentColor-inherited. Categories get **photography**, not icons — and the schema already supports it (`uploads/categories/` exists and is empty, `Category` model has an image field). Payment methods get real vector marks. That change alone moves the site further toward "real" than any other single edit.

### 3.4 Copy written by a language model — **High / Content**

**What:** Verbatim from `about.php`: *"We are dedicated to offering an elevated, transparent, and seamless online shopping experience"* / *"To empower consumers with effortless access to top-tier electronics, genuine fashion, lifestyle essentials"*. From `index.php`: *"Experience top quality, authentic brands, and incredible value right at your doorstep."* Section subtitles: *"Carefully picked premium items for you"*, *"Browse through our wide selection of departments"*, *"Freshly added products in our collection"*.

**Why it reads artificial:** Stacked abstract virtue nouns (elevated / transparent / seamless / effortless / top-tier / premium / authentic) with zero verifiable specifics. No founding date, no location beyond a generic Gulshan address, no team, no numbers, no supplier names, no story. Every sentence would survive being pasted onto any other store in any other country — which is the definition of copy that says nothing.

**What a real retailer does:** Specifics that can be checked. "Started in 2019 from a single counter in Bashundhara City. We import directly from 40 authorized distributors. 11 people, one warehouse in Tejgaon, same-day dispatch until 4pm." Replace the four generic `about.php` benefit cards with the actual return window, actual courier partners, actual warranty terms.

### 3.5 The hero is a blue rectangle with text in it — **High / Visual**

**What:** `.hero-banner-card` is `linear-gradient(135deg, #1e3a8a, #3b82f6)`, 380px min-height, text left-aligned, CTA button. The hero banner *image* (`uploads/banners/hero-electronics.jpg` — a real photograph, present on disk) is **never rendered** — the `Banner` model returns `image_path` and `index.php` ignores it entirely.

**Why it reads artificial:** The prompt named this exact pattern. A gradient rectangle is what gets produced when no one has decided what the store sells. Meanwhile the headline is the product name shouted in title case — "IMMERSE IN SOUND WITH SONY WH-1000XM5 WIRELESS NOISE CANCELLING." as an eyebrow above "Next-Gen Audio Experience" — two competing headlines saying the same thing, neither of which is an offer.

**What a real retailer does:** Show the product. The hero photograph already exists in the repo and is being thrown away. A hero is: one image, one specific claim, one price or offer, one CTA. "Sony WH-1000XM5 — ৳34,990, was ৳38,500. Free delivery." beats "Next-Gen Audio Experience" every time, because one is an offer and the other is a mood.

### 3.6 Four identical section blocks — **Medium / Visual**

**What:** Popular Categories, Featured Products, Best Sellers, New Arrivals — all use the identical `.section-header` + emoji + title + subtitle + outline "View All" button + 4-column grid. Same spacing, same rhythm, same everything, four times.

**Why it reads artificial:** Real homepages vary block *shape* to create rhythm — a wide editorial banner, then a 6-up tight grid, then a 2-up lifestyle split, then a horizontal scroll rail. Four identical rows is a `foreach` loop over section names, and it looks like one.

**What a real retailer does:** Differentiate. One section becomes a horizontal scroll rail on mobile. One becomes a 2-column editorial with a lifestyle photo. One stays a grid. The variation *is* the design.

### 3.7 Other confirmed slop

| Item | Where | Why it reads artificial |
|---|---|---|
| `📂` folder icon ×6 identical | `index.php`, `categories.php` | Same glyph for Electronics, Fashion, Beauty, Sports, Books — conveys zero information, actively wastes the strongest visual slot on the page |
| "Built with PHP 8 & MySQL" | `footer.php` | No shop on earth advertises its stack in the footer. Instantly identifies this as a student/demo project. **Delete.** |
| `e.g. WELCOME10` in coupon field | `cart.php` | Placeholder text leaking a working promo code — real stores don't hand out discounts in placeholders |
| Payment "logos" as `💳 bKash` `💳 Nagad` `💳 Visa / Mastercard` | `footer.php` | Same generic card emoji three times as a stand-in for brand marks |
| `⚡` as brand logo | `header.php`, `footer.php` | The identity mark is an OS-dependent emoji in a rounded blue square |
| Star rating as `⭐ 4.8` | `product-card.php` | A single emoji + number where retail convention is a 5-star glyph row; no half-stars possible |
| Rating input as `<select>` | `product.php` | "★★★★★ 5 Stars (Excellent)" in a dropdown — every real review UI uses clickable stars |
| Arrow suffix `&rarr;` on ~14 buttons | Sitewide | "Shop Now →", "Sign In →", "Create Account →", "View Details →" — decorative arrow applied indiscriminately, including on buttons that submit forms rather than navigate |
| Dashed border + pink gradient flash-sale box | `index.php:72` | `border:2px dashed #fca5a5` on a pink gradient is a Word-document aesthetic |

---

## 4. Global Design-System Problems

| # | Issue | Severity | Class |
|---|---|---|---|
| 4.1 | **No spacing scale.** 15+ arbitrary rem values (`0.35`, `0.65`, `0.85`, `1.75`, `2.5`…) chosen per-instance | High | Visual |
| 4.2 | **336 inline styles** override the stylesheet and block all media queries | Critical | Component |
| 4.3 | **No icon system** — emoji stand in everywhere | High | Visual |
| 4.4 | Color tokens exist but are bypassed: `#f8fafc`, `#f1f5f9`, `#94a3b8`, `#fff`, `#dcfce7`, `#166534` hardcoded across pages | High | Visual |
| 4.5 | Status-color pairs (green/amber/red backgrounds + text) re-declared inline in ≥6 places instead of `.status-success` etc. | Medium | Component |
| 4.6 | 5 font weights loaded (400–800); 800 used for nearly every heading, so "bold" carries no hierarchy | Medium | Visual |
| 4.7 | Button heights drift 36–48px by context (`btn-sm` in header vs `btn-lg` in hero vs default) | Medium | Component |
| 4.8 | Radius leaks: `4px` hardcoded in ~6 inline spots alongside the `--radius-sm: 6px` token | Low | Visual |
| 4.9 | No `:focus-visible` styling anywhere → **no visible keyboard focus indicator** (measured: `outline: none`, `box-shadow: none`) | High | Accessibility |
| 4.10 | No empty/loading/skeleton system — cart mutations do full `location.reload()` | Medium | UX |

---

## 5. Header Audit

**Verdict: three unrelated bars stacked, not one component.**

| # | Issue | Severity | Class |
|---|---|---|---|
| 5.1 | Three separate full-width bars (topbar / header / nav) consume **~150px before any content** — on a 812px phone that is 18% of the viewport spent on chrome | High | UX |
| 5.2 | Only `.header-main` is sticky; the nav bar scrolls away, so category navigation is unreachable while browsing | Medium | UX |
| 5.3 | **No active state on nav links.** `.nav-links a.active` is styled in CSS but the class is never applied by any page — the user never knows where they are | High | UX |
| 5.4 | Logo is `⚡` emoji + text; no real wordmark or SVG | High | Visual |
| 5.5 | Wishlist has no count badge while Cart does — inconsistent treatment of two peer actions | Low | Component |
| 5.6 | Announcement bar is static text, not dismissible, not rotating, repeats info also in the footer | Low | Content |
| 5.7 | Search autocomplete dropdown exists (`app.js`) but has no keyboard navigation (arrow keys / Enter / Escape) and no ARIA combobox roles | Medium | Accessibility |
| 5.8 | Account area shows first name only, no dropdown menu — `user-dropdown-wrap` div exists but contains no menu | Medium | UX |
| 5.9 | Cart icon does not open a mini-cart/drawer; every add forces a full page trip to review | Medium | Conversion |

**Fixed during pre-audit work (documented for completeness):** mobile nav previously overflowed the viewport with no hamburger; a toggle and collapse behavior were added, and the breakpoint moved 768→900px to fix a squeezed-search dead zone. See §29.7 for a side effect this introduced.

---

## 6. Homepage Audit

| # | Issue | Severity | Class |
|---|---|---|---|
| 6.1 | 24 cards / 13 unique products (§3.1) | Critical | Content |
| 6.2 | Hero image asset exists on disk but is never rendered; hero is a bare gradient (§3.5) | High | Visual |
| 6.3 | Two competing headlines in hero (eyebrow shouts the product name in caps, H1 states a vague mood) | High | Content |
| 6.4 | Hero has no price, no offer, no product imagery — nothing transactional | High | Conversion |
| 6.5 | Four structurally identical sections (§3.6) | Medium | Visual |
| 6.6 | Category cards are `📂` + name + count — no imagery, despite `uploads/categories/` and schema support | High | Visual |
| 6.7 | **No trust/service strip** (delivery, returns, authenticity, payment) anywhere on the homepage — it exists only on product pages | High | Conversion |
| 6.8 | No newsletter capture, no social proof, no review highlights, no brand logos | Medium | Conversion |
| 6.9 | Promo banner is a dark gradient with a red "LIMITED PROMOTION" pill and no imagery or deadline | Medium | Visual |
| 6.10 | Flash-sale block styled with dashed pink border (§3.7) and shows only an end *date*, no countdown — undercutting the urgency it exists to create | Medium | Conversion |
| 6.11 | Section subtitles are filler ("Carefully picked premium items for you") | Low | Content |

---

## 7. Product Listing Audit (`products.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 7.1 | **`grid-template-columns:260px 1fr` is inline** — no breakpoint can override. Measured at 375px: sidebar 260px, product column 240px, total 532px in a 375px viewport (§18) | Critical | Responsive |
| 7.2 | **Filters split across two mechanisms:** category is a set of `<a>` links, everything else is a `<form>` requiring "Apply Filters". Clicking a category **discards unsubmitted price/brand input.** | High | UX |
| 7.3 | No active-filter chips — with filters applied there is no summary and no per-filter clear | High | UX |
| 7.4 | "Showing 12 of 15 items" uses `count($products)` (page size), not a range — should read "1–12 of 15" | Medium | Content |
| 7.5 | Pagination renders **every page as a numbered button**, no prev/next, no ellipsis — fine at 2 pages, unusable at 40 | High | UX |
| 7.6 | Category heading de-slugs with `ucfirst()` only: `beauty-personal-care` → "Beauty personal care" (should use the real category name from the DB, which is already available) | Medium | Content |
| 7.7 | No results-per-page control, no grid/list toggle | Low | UX |
| 7.8 | Filter sidebar not collapsible/drawered on mobile — it simply dominates | Critical | Responsive |
| 7.9 | "Reset All" styled in danger red at 20px tall — reads as destructive and is below touch minimum | Medium | Accessibility |
| 7.10 | Price min/max inputs have no validation (max < min silently returns nothing) | Low | UX |
| 7.11 | Sort `<select>` auto-submits on change with no loading feedback | Low | UX |

---

## 8. Categories Audit (`categories.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 8.1 | Six identical `📂` glyphs at 3rem — the page's dominant visual element carries no information (§3.7) | High | Visual |
| 8.2 | Schema and upload directory support category images; both unused | High | Visual |
| 8.3 | Page is a flat grid of 6 tiles with no hierarchy, no subcategories, no featured products, no editorial | Medium | UX |
| 8.4 | Duplicates the homepage's "Popular Categories" section almost exactly — unclear why both exist | Medium | UX |
| 8.5 | Description truncated at 70 chars mid-word with `...` | Low | Content |

---

## 9. Product Detail Audit (`product.php`)

**The strongest page in the app** — real trust badges, verified-buyer reviews, stock states, related products. Still has gaps:

| # | Issue | Severity | Class |
|---|---|---|---|
| 9.1 | **No Buy Now / express checkout** — only Add to Cart, forcing cart→checkout for every purchase | High | Conversion |
| 9.2 | Gallery shows thumbnails only when >1 image; 13 of 15 products have exactly 1 image, so most pages have a single static photo with no zoom, no lightbox | High | UX |
| 9.3 | SKU + Brand line sits **above** the H1 — inverted hierarchy; brand belongs near the title, SKU belongs in specifications | Medium | Visual |
| 9.4 | Exact stock count exposed ("In Stock (50 available)") — retail convention reveals count only when low, as scarcity | Medium | Conversion |
| 9.5 | Rating input is a `<select>` dropdown, not clickable stars (§3.7) | Medium | Component |
| 9.6 | No rating distribution bars (5★ 80%, 4★ 12%…), no review sorting/filtering, no helpful votes, no review images | Medium | UX |
| 9.7 | Description is `nl2br()`'d plain text — **no specifications table**, though products have structured attributes | High | Content |
| 9.8 | "Customers Also Viewed" is actually *same-category* products — the label asserts behavioral data that doesn't exist | Medium | Content |
| 9.9 | Trust badges are 4 emoji + text in a flat grey box (§3.3) | Medium | Visual |
| 9.10 | No delivery estimate by location, no stock-by-store, no size guide for apparel | Medium | Conversion |
| 9.11 | No breadcrumb structured data / Product schema.org markup (SEO + rich results) | Medium | Functional |
| 9.12 | Quantity control uses inline `onclick="adjustQty()"` with no max-stock feedback when clamped | Low | UX |

---

## 10. Cart Audit (`cart.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 10.1 | **Displayed math is wrong.** Measured live: Subtotal ৳134,420 − Offer Discount ৳6,960 = Total ৳134,420. Root cause: `Cart::getDetails()` computes `subtotal` from `effective_unit_price` (already discounted), then the UI renders the discount as a *separate deduction line*. The correct display subtotal is the pre-discount sum (৳141,380), which then reconciles exactly. **Display bug, not a charging bug — the customer is charged correctly.** | **Critical** | Functional + Conversion |
| 10.2 | **Cart is unusable on mobile.** `<table>` with 5 columns renders 621px wide in a 375px viewport; Remove button sits at x=584 and `canScrollX: false` → **items cannot be removed or re-quantified on a phone** | **Critical** | Responsive |
| 10.3 | Every quantity change triggers `location.reload()` — full page reload, lost scroll position, ~400ms artificial delay | High | UX |
| 10.4 | Native `confirm()` dialogs for removal, inconsistent with the app's own toast system | Medium | UX |
| 10.5 | **No free-shipping progress indicator** despite the threshold being announced in the topbar — a proven AOV lever, already computed server-side (`free_shipping_min`) | High | Conversion |
| 10.6 | "Estimated Delivery" labels a *shipping fee* — the word says time, the value says money | Medium | Content |
| 10.7 | No "Save for later" / move-to-wishlist | Medium | Conversion |
| 10.8 | No "Continue shopping" link | Low | UX |
| 10.9 | Coupon placeholder leaks a live code (§3.7) | Low | Content |
| 10.10 | Stock warning logic reads oddly: `!$item['is_in_stock']` renders "⚠ Only N in stock" — the negative branch describes availability | Low | Content |

---

## 11. Checkout Audit (`checkout.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 11.1 | **Same subtotal/discount math error as §10.1** ("Items Subtotal ৳134,420 / Promotional Offer −৳6,960 / Total ৳134,420") | **Critical** | Functional + Conversion |
| 11.2 | **Full header, nav, search and footer present during checkout** — every professional checkout strips navigation to prevent abandonment; here there are ~20 exit links on the payment page | High | Conversion |
| 11.3 | No progress indicator (Cart → Delivery → Payment → Confirm) | High | UX |
| 11.4 | **Payment methods are decorative.** `api/orders/create.php` validates the method string and creates the order regardless — bKash/Nagad/SSLCommerz perform no redirect, no transaction, no verification. The UI promises "Direct instant payment via official wallet gateway" and "Supports Visa, Mastercard, AMEX, UnionPay". | **Critical** | Functional + Content |
| 11.5 | Selected payment radio gets **no visual state change** — no border highlight, no background — so the selection is nearly invisible | High | UX |
| 11.6 | "Terms of Service & Return Policy" is **plain text, not links** — the pages don't exist | High | Conversion |
| 11.7 | No inline validation; relies entirely on browser `required`. Phone has no pattern despite a `phone` validator existing server-side | Medium | UX |
| 11.8 | Order review list is `max-height:260px; overflow-y:auto` — a nested scroll region inside a scrolling page | Medium | UX |
| 11.9 | No coupon entry at checkout (cart only) — customers who find a code here must navigate back | Medium | Conversion |
| 11.10 | No guest checkout — `includes/auth.php` forces login before the cart can be converted | High | Conversion |
| 11.11 | No order-confirmation email is sent anywhere in `Order::createFromCart()` | High | Functional |

---

## 12. Authentication Audit (`login.php`, `register.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 12.1 | **No "Forgot password" link and no reset flow at all.** A locked-out customer has no recovery path | **Critical** | Functional |
| 12.2 | Login errors render as a **flash banner at the top of the page**, while register renders **inline per-field errors** — two different error systems in the same flow | High | UX |
| 12.3 | No show/hide password toggle on any password field (4 fields across login/register/profile) | Medium | UX |
| 12.4 | No "Remember me" | Low | UX |
| 12.5 | No password strength indicator; minimum is 6 characters | Medium | UX |
| 12.6 | No terms/privacy consent checkbox at registration | Medium | Content |
| 12.7 | No social/OAuth login | Low | Conversion |
| 12.8 | Login page keeps full nav — no distraction reduction | Low | Conversion |
| 12.9 | After login, admin/staff are force-redirected to the admin panel, discarding the `redirect` param — a staff member adding to cart then logging in loses their destination | Medium | UX |

---

## 13. Wishlist Audit (`wishlist.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 13.1 | **No remove control.** The page reuses `product-card.php`, whose heart button *adds* to wishlist — so on the wishlist page the only wishlist action re-adds the item already there | High | Functional |
| 13.2 | Heart button has no active/filled state anywhere — a saved item looks identical to an unsaved one | High | Component |
| 13.3 | No "Move to cart" / "Add all to cart" | Medium | Conversion |
| 13.4 | No price-drop indication, though the empty state promises "track discounts" | Medium | Content |
| 13.5 | Wishlist requires login but the header link is shown to guests, sending them to a login wall with no explanation | Medium | UX |

---

## 14. Account Audit (`profile.php`, `orders.php`, `addresses.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 14.1 | **Account sidebar exists only on `profile.php`.** `orders.php`, `addresses.php`, `wishlist.php` have no sidebar — the account section has no persistent navigation | High | UX |
| 14.2 | `grid-template-columns:240px 1fr` inline on profile — same unbreakable-layout problem as §7.1 | High | Responsive |
| 14.3 | Avatar is a `👤` emoji at 3rem, **despite `uploads/avatars/` containing real avatar images and the users table having an `avatar` column** | Medium | Visual |
| 14.4 | No avatar upload for customers (the admin panel has upload infrastructure) | Low | Functional |
| 14.5 | Orders table has **7 columns with no responsive treatment** — same mobile failure mode as the cart | High | Responsive |
| 14.6 | Order list shows **no product thumbnails** — retail convention is image-led order history | Medium | UX |
| 14.7 | No reorder button, no tracking link, no invoice download, no cancel/return request | High | UX |
| 14.8 | Payment badge renders raw enum: "pending (COD)" | Low | Content |
| 14.9 | No account dashboard overview (recent order, saved items, default address at a glance) — `profile.php` is titled "Account Dashboard" but is only two forms | Medium | UX |
| 14.10 | Email field disabled with no explanation of how to change it | Low | UX |

---

## 15. Search Audit (`search.php`)

| # | Issue | Severity | Class |
|---|---|---|---|
| 15.1 | **Search results have no filters and no sorting**, while `products.php` has both — the same result set is differently capable depending on how you arrived | High | UX |
| 15.2 | Empty `q` renders `Search Results for ""` with the full catalog beneath | Medium | Content |
| 15.3 | No search suggestions, no "did you mean", no popular/recent searches on zero results | Medium | UX |
| 15.4 | No result-count-by-category breakdown | Low | UX |
| 15.5 | Autocomplete dropdown has no keyboard support or ARIA roles (§5.7) | Medium | Accessibility |
| 15.6 | Search term not highlighted in results | Low | UX |

---

## 16. Deals / Offers Audit

| # | Issue | Severity | Class |
|---|---|---|---|
| 16.1 | **"🔥 Deals & Offers" in the main nav just links to `products.php?sale=1`** — there is no deals *page*, no landing experience, despite a full `Offer` model with flash sales, date ranges and tiers | High | Conversion |
| 16.2 | Because every product is discounted (§3.2), the "deals" view is identical to the full catalog | High | Content |
| 16.3 | Flash sale shows an end *date*, not a countdown timer | Medium | Conversion |
| 16.4 | Nav item is red + bold + emoji — shouting for attention among neutral siblings | Low | Visual |

---

## 17. Footer Audit

| # | Issue | Severity | Class |
|---|---|---|---|
| 17.1 | **"Built with PHP 8 & MySQL"** — delete immediately (§3.7) | High | Content |
| 17.2 | **No policy links whatsoever**: no Returns, Shipping, Privacy, Terms, FAQ. Checkout references Terms and Return Policy that do not exist | **Critical** | Conversion |
| 17.3 | Payment methods as `💳` emoji ×3 instead of brand marks | High | Visual |
| 17.4 | No social links, no newsletter signup | Medium | Conversion |
| 17.5 | No business registration / trade licence number — standard trust marker for BD e-commerce | Medium | Conversion |
| 17.6 | "Featured Categories" link goes to the plain categories page; "About Our Brand" / "Customer Support" are wordier than needed | Low | Content |
| 17.7 | Contact details duplicate the topbar exactly | Low | Content |

---

## 18. Responsive Audit

Measured on the running site at 375px (iPhone SE/12 mini class), authenticated, cart populated.

| # | Finding | Severity | Class |
|---|---|---|---|
| 18.1 | **Cart: content extends to 621px in a 375px viewport.** Remove button at x=584, `canScrollX: false` → **unreachable**. Customers cannot remove cart items on mobile. | **Critical** | Responsive |
| 18.2 | **Products: grid computes to `260px 240px` at 375px** — sidebar 69% of screen, product column overflows off-canvas | **Critical** | Responsive |
| 18.3 | Orders: 7-column table, same overflow failure mode as cart | High | Responsive |
| 18.4 | Profile: `240px 1fr` inline grid does not collapse | High | Responsive |
| 18.5 | **60 of 91 interactive elements are under 44×44px.** Worst: "Reset All" 54×20, category filter links 210×23, search input 239×37, action buttons 39px tall | High | Accessibility |
| 18.6 | Product-card image `padding-top:80%` (1.25 ratio) forced on source images ranging **0.65–1.50** — portrait products are aggressively cropped (§26) | High | Visual |
| 18.7 | Existing breakpoints are only 992px and 900px — nothing between 375 and 900, so the 400–768 band is unstyled | Medium | Responsive |
| 18.8 | Checkout address grid `1.5fr 1fr 1fr` (City/Area/Postal) does not stack — three cramped fields on a phone | Medium | Responsive |
| 18.9 | `html, body { overflow-x: hidden }` masks overflow rather than fixing it — see §29.7 | High | Responsive |
| 18.10 | Product detail `1fr 1.2fr` collapses at 992px (correct), but gallery `aspect-ratio: 1` + `object-fit: contain` letterboxes wide product shots with large empty bands | Medium | Visual |

---

## 19. Accessibility Audit

| # | Finding | Severity | Class |
|---|---|---|---|
| 19.1 | **No visible focus indicator.** Measured on `.btn-primary`: `outline-style: none`, `box-shadow: none`. Keyboard users cannot see their position anywhere on the site. WCAG 2.4.7 failure | **Critical** | Accessibility |
| 19.2 | **Old price contrast 2.56:1** (`#94a3b8` on white) — fails WCAG AA (4.5:1). Appears on every discounted card, i.e. all of them | High | Accessibility |
| 19.3 | 60/91 touch targets under 44px (§18.5) — WCAG 2.5.8 | High | Accessibility |
| 19.4 | Heading hierarchy skips: `products.php` goes H1 → H3 (no H2) | Medium | Accessibility |
| 19.5 | Emoji icons are announced literally by screen readers ("shopping trolley Add to Cart", "heart") and lack `aria-hidden` | High | Accessibility |
| 19.6 | Search autocomplete has no `role="combobox"`, `aria-expanded`, `aria-activedescendant`, or keyboard nav | Medium | Accessibility |
| 19.7 | Toasts are not `role="status"` / `aria-live` — cart additions are silent to screen readers | Medium | Accessibility |
| 19.8 | Category/rating filters are `<a>` links styled as filter controls, not checkboxes/radios — wrong semantics for the interaction | Medium | Accessibility |
| 19.9 | No skip-to-content link (~150px of chrome + 6 nav links to tab through on every page) | Medium | Accessibility |
| 19.10 | Form validation errors are not linked to inputs via `aria-describedby`; login errors are not `role="alert"` | Medium | Accessibility |
| 19.11 | Disabled "Out of Stock" button uses `opacity:0.6`, dropping contrast below AA | Low | Accessibility |
| 19.12 | **Alt text is present and meaningful on all 12 images — genuinely good, keep it** | — | ✅ |
| 19.13 | Reduced-motion preference not respected (card hover transforms, toast slide-in) | Low | Accessibility |

---

## 20. Component Audit

| Component | State | Severity | Notes |
|---|---|---|---|
| Header | Fragmented into 3 bars | High | §5 |
| Announcement bar | Static, non-dismissible | Low | Duplicates footer |
| Search | Works; autocomplete lacks a11y + keyboard | Medium | §5.7 |
| Navigation | **No active state applied** | High | §5.3 |
| Buttons | 7 variants, heights 36–48px, `→` suffix ad hoc | Medium | §4.7 |
| Product card | **The one genuinely reusable component** — sound structure | Low | Needs image-ratio + badge fixes only |
| Category card | Emoji-only, no imagery | High | §8.1 |
| Badges | Over-applied to the point of meaninglessness | Critical | §3.2 |
| Rating display | Emoji star + number; no partial stars | Medium | §3.7 |
| Price display | Good hierarchy; old-price contrast fails | High | §19.2 |
| Forms | Inconsistent error patterns login vs register | High | §12.2 |
| Inputs | Consistent `.form-control`; focus ring present here (good) | Low | — |
| Selects | Native, unstyled | Low | — |
| Filters | Two competing mechanisms | High | §7.2 |
| Pagination | Every page numbered, no prev/next | High | §7.5 |
| Wishlist button | No active state; cannot remove | High | §13.1–13.2 |
| Cart controls | Reload on every mutation | High | §10.3 |
| Modals | **None exist** — uses native `confirm()` | Medium | §10.4 |
| Alerts (flash) | Inline styles, three near-duplicate blocks in `header.php` | Medium | Should be `.alert-{type}` |
| Toasts | Well built — animation, types, auto-dismiss | Low | Add `aria-live` |
| Footer | Emoji payments, no policies, stack advertisement | High | §17 |
| Breadcrumbs | Only on product detail; plain `>` separators, no schema | Medium | Missing on listing/category |
| Empty states | **Consistently well done** across cart/wishlist/orders/search | — | ✅ Keep |
| Loading states | **None anywhere** — no skeletons, no spinners, no button busy state except checkout | High | — |

---

## 21. Typography Recommendations

Current: Inter 400/500/600/700/800, sizes ad hoc from 0.75rem to 2.75rem, nearly every heading at weight 800.

**Recommended — one family, three weights, eight steps:**

| Token | Size | Weight | Line height | Use |
|---|---|---|---|---|
| `--text-xs` | 12px | 500 | 1.4 | Badges, metadata, captions |
| `--text-sm` | 14px | 400 | 1.5 | Secondary text, labels |
| `--text-base` | 16px | 400 | 1.6 | Body, product titles |
| `--text-lg` | 18px | 600 | 1.4 | Card prices, subsection heads |
| `--text-xl` | 22px | 600 | 1.3 | Section titles |
| `--text-2xl` | 28px | 700 | 1.25 | Page titles |
| `--text-3xl` | 36px | 700 | 1.15 | Hero (desktop) |
| `--text-4xl` | 48px | 700 | 1.1 | Hero (large desktop only) |

- **Drop weights 500 and 800.** Keep 400 / 600 / 700. Weight 800 everywhere is why nothing reads as more important than anything else.
- Prices get `font-variant-numeric: tabular-nums` so columns align.
- Keep Inter — it is a correct, neutral choice for commerce. Consider a distinct display face for the hero/brand only if a real identity is developed; do **not** add a second face for body text.
- Product titles: `-webkit-line-clamp: 2` is already correct. Keep.

---

## 22. Color Recommendations

The palette was revised during pre-audit work to a blue-and-white direction (`--primary #2563EB`, `--secondary #172554`, `--accent #0284C7`). That direction is sound. Remaining work is **discipline, not hue**:

```css
:root {
  /* Brand */
  --primary:        #2563EB;
  --primary-hover:  #1D4ED8;
  --primary-light:  #EFF6FF;
  --secondary:      #172554;

  /* Neutrals — slightly blue-biased to sit with the brand */
  --bg-body:        #F8FAFC;
  --bg-surface:     #FFFFFF;
  --bg-subtle:      #F1F5F9;   /* replaces hardcoded #f8fafc/#f1f5f9 */
  --border:         #E2E8F0;
  --border-strong:  #CBD5E1;

  /* Text — note the fix */
  --text-main:      #0F172A;
  --text-muted:     #475569;   /* was #64748b — lifts to 7.5:1 */
  --text-subtle:    #64748B;   /* was #94a3b8 — fixes §19.2 (2.56:1 → 4.8:1) */

  /* Semantic — functional, NOT brand */
  --success:        #059669;
  --warning:        #D97706;
  --danger:         #DC2626;
  --sale:           #DC2626;
}
```

**Rules to enforce:**
1. **`--text-light: #94a3b8` must not be used on white.** It is the §19.2 failure. Reserve it for dark surfaces only.
2. Semantic colors are for state, never decoration. Red means error/sale-price, not "attention-grabbing nav link."
3. No raw hex in page markup — all 336 inline styles should resolve to tokens.
4. Sale red and danger red should be the *same* token; two reds competing is visual noise.
5. Status pill pairs (bg + text) become classes: `.pill-success`, `.pill-warning`, `.pill-danger`, `.pill-info`, `.pill-neutral`.

---

## 23. Spacing Recommendations

There is currently no scale. Adopt a 4px base:

```css
--space-1:  4px;    --space-2:  8px;    --space-3:  12px;
--space-4:  16px;   --space-5:  20px;   --space-6:  24px;
--space-8:  32px;   --space-10: 40px;   --space-12: 48px;
--space-16: 64px;   --space-20: 80px;
```

| Context | Value |
|---|---|
| Inside card padding | `--space-5` (20px) |
| Card grid gap | `--space-6` (24px) |
| Between sections | `--space-16` desktop / `--space-10` mobile |
| Page top/bottom | `--space-12` desktop / `--space-8` mobile |
| Form field gap | `--space-5` |
| Inline icon↔text | `--space-2` |
| Container padding | `--space-6` desktop / `--space-4` mobile |

Container max-width 1280px is reasonable; consider 1200px for a slightly denser, more retail feel.

---

## 24. Card Recommendations

**Product card** — the structure is already good; fix these four things:

| Property | Current | Recommended |
|---|---|---|
| Image ratio | `padding-top: 80%` (1.25) on 0.65–1.50 sources | **1:1** with `object-fit: contain` on `--bg-subtle`, OR normalize all source images to 1:1 (preferred — see §26) |
| Padding | `1.25rem` | `--space-5` (20px) |
| Radius | `--radius-md` (10px) | `12px`, consistently |
| Border/shadow | Border **and** `shadow-sm` **and** hover `shadow-lg` + 4px lift | Border only at rest; `shadow-md` + 2px lift on hover. Currently double-elevated |
| Badges | Up to 3 stacked, 79%/100% saturation | **Max 1 badge.** Priority: Out of stock > discount ≥15% > nothing |
| Title | `min-height: 2.7rem` | Keep — prevents ragged grids |
| CTA | Always-visible full-width blue button | Keep visible (correct for mobile); consider secondary styling so the *price* leads |

**Category card:** replace `📂` with a category photograph, name overlaid or beneath, product count as metadata. This single change transforms the homepage.

---

## 25. Button Recommendations

One height spec, one radius, four semantic variants:

| Variant | Background | Text | Border | Use |
|---|---|---|---|---|
| Primary | `--primary` | white | none | Add to cart, Place order — **one per view** |
| Secondary | white | `--text-main` | `--border-strong` | Cancel, secondary nav |
| Tertiary/ghost | transparent | `--primary` | none | Inline links, "View all" |
| Destructive | white | `--danger` | `--danger` | Remove, delete |

| Size | Height | Padding | Font |
|---|---|---|---|
| `sm` | 36px | 0 14px | 14px/600 |
| `md` (default) | 44px | 0 20px | 15px/600 |
| `lg` | 52px | 0 28px | 16px/600 |

**Rules:**
- `md` = 44px is the default because it satisfies the touch minimum (§18.5).
- Every button needs `:hover`, `:active`, `:focus-visible` (2px offset ring), `:disabled`, **and a busy/loading state**.
- **Remove the decorative `→` suffix** from buttons that submit rather than navigate ("Sign In →", "Create Account →").
- Disabled: `--bg-subtle` background + `--text-subtle` text, never `opacity` (§19.11).

---

## 26. Image Treatment Recommendations

**Measured source ratios — the actual problem:**

| Ratio band | Products |
|---|---|
| 0.65–0.67 (tall portrait) | argan-oil, desk-lamp, leather-wallet, oxford-shirt, serum, luxury-pen |
| 0.77–1.00 | floral-maxi, yoga-mat |
| 1.13–1.33 | ergo-chair, macbook-air, atomic-habits |
| 1.49–1.50 (wide) | sony-xm5, logitech-mx3s, samsung-g5, dumbbell-set |

A 0.65 image and a 1.50 image forced through one `object-fit: cover` frame at 1.25 means the portrait shots lose ~48% of their height. That is the "inconsistent crops" tell.

**Recommendations:**
1. **Normalize the catalog to 1:1** at 1200×1200, product centered, consistent background. This is a content task, not a code task, and it is the highest-visual-impact fix available.
2. Until then, use `object-fit: contain` on `--bg-subtle` — letterboxing is honest; cropping a wallet's top half is not.
3. Backgrounds are currently inconsistent (yellow for headphones, purple for MacBook, orange for the dress, white for others). Real catalogs pick one: pure white for hard goods, one consistent lifestyle treatment for apparel.
4. Add `width`/`height` attributes to prevent CLS (`loading="lazy"` is already correctly applied — keep it).
5. Serve WebP with JPEG fallback.
6. **Fix the placeholder bug (§29.1)** — `getImageUrl()` falls back to `placeholder.png`, but only `placeholder.svg` exists.

---

## 27. Copy / Content Recommendations

| Current | Problem | Replace with |
|---|---|---|
| "Experience top quality, authentic brands, and incredible value right at your doorstep." | Says nothing checkable | "Free delivery over ৳5,000. 7-day returns. Dhaka same-day dispatch." |
| "Carefully picked premium items for you" | Filler | Delete, or "Hand-picked by our buyers this week" |
| "We are dedicated to offering an elevated, transparent, and seamless…" | Pure AI cadence | Founding year, location, team size, sourcing specifics |
| "Built with PHP 8 & MySQL" | Identifies as a demo | **Delete** |
| "Your Premium Online Shopping Destination" | Generic superlative | A real positioning line |
| "Start Shopping Now →" / "Explore Products →" / "Browse All Products →" | Three phrasings, one action | Standardize: "Browse products" |
| "No Products Match Your Criteria" | Robotic | "Nothing matched those filters" + suggest loosening |
| "Total Payable" | Uncommon phrasing | "Order total" |
| "Estimated Delivery" (on a fee) | Mislabeled | "Delivery" or "Shipping" |
| "Customers Also Viewed" | Asserts data that doesn't exist | "More in {Category}" |

**Sentence case everywhere** — currently mixes Title Case ("Shopping Cart", "My Saved Wishlist"), sentence case, and ALL CAPS hero eyebrows.

---

## 28. Conversion / Trust Recommendations

Ordered by expected impact:

1. **Fix the cart math (§10.1).** Nothing else matters if the totals look wrong.
2. **Make the mobile cart usable (§10.2).** Currently a hard stop for phone shoppers.
3. **Write the policy pages** — Returns, Shipping, Privacy, Terms — and link them from the footer and checkout. Checkout currently references documents that don't exist (§11.6, §17.2).
4. **Free-shipping progress bar in cart** ("Add ৳420 more for free delivery") — data already available.
5. **Strip navigation from checkout** (§11.2).
6. **Either integrate the payment gateways or relabel them honestly** (§11.4). "Pay on delivery" and "Bank transfer — we'll send instructions" are honest; a fake SSLCommerz button is not.
7. **Add Buy Now** on product detail (§9.1).
8. **Trust strip on the homepage** — delivery, returns, authenticity, payment (§6.7).
9. **Real payment brand marks** in the footer, not `💳` (§17.3).
10. **Order confirmation email** (§11.11).
11. **Reduce badge noise** so discounts regain meaning (§3.2).
12. **Guest checkout** (§11.10).
13. Review distribution bars + verified-buyer prominence — the data is already there and is a genuine strength.

---

## 29. Functional Issues Found

Documented separately per the brief. These affect UX directly.

| # | Issue | Severity |
|---|---|---|
| 29.1 | **`getImageUrl()` falls back to `placeholder.png`; only `placeholder.svg` exists.** Every product without an image renders as a broken image icon | High |
| 29.2 | **Cart/checkout totals display inconsistent arithmetic** (§10.1). Charging is correct; display is not | **Critical** |
| 29.3 | **No password reset flow** (§12.1) | **Critical** |
| 29.4 | **Payment methods are non-functional** but presented as live gateways (§11.4) | **Critical** |
| 29.5 | **Wishlist has no remove function** (§13.1) | High |
| 29.6 | **Mobile cart controls unreachable** (§10.2) | **Critical** |
| 29.7 | **`overflow-x: hidden` masks overflow.** Added during pre-audit work to fix mobile nav overflow. It fixed the nav, but on cart/orders it converted "horizontally scrollable, ugly but usable" into "clipped and unreachable." The nav fix should be kept; the cart/orders tables must be made genuinely responsive rather than relying on the clip | High |
| 29.8 | `reset_passwords.php` is **publicly accessible and resets all demo account passwords to a known value** on load, with no auth | **Critical (security)** |
| 29.9 | `setup.php` and `download_images.php` are publicly reachable | High (security) |
| 29.10 | No order confirmation email (§11.11) | High |
| 29.11 | Nav active state never applied (§5.3) | Medium |
| 29.12 | `products.php` category heading de-slugs incorrectly (§7.6) | Medium |
| 29.13 | "Showing 12 of 15" uses page count, not range (§7.4) | Low |
| 29.14 | Admin/staff login discards `redirect` param (§12.9) | Medium |
| 29.15 | Category images supported by schema/upload dir but never used (§8.2); hero banner image likewise never rendered (§3.5) | Medium |

---

## 30. Priority Matrix

### Phase 0 — Stop the bleeding (do first, small, high impact)

| Task | Ref |
|---|---|
| Fix cart/checkout subtotal display math | §10.1 |
| Delete `reset_passwords.php`, guard `setup.php` + `download_images.php` | §29.8–29.9 |
| Fix `placeholder.png` → `.svg` | §29.1 |
| Delete "Built with PHP 8 & MySQL" | §17.1 |
| Add visible `:focus-visible` ring globally | §19.1 |
| Fix old-price contrast (`--text-light` → `#64748B`) | §19.2 |

*Roughly a day's work; removes two Critical trust failures and two Critical security holes.*

### Phase 1 — Foundation

Spacing scale, type scale (drop 2 weights), color-token discipline, container system, **and the big one: extract the 336 inline styles into real classes.** Everything downstream depends on this — responsive fixes are impossible while layout lives inline.
→ §4, §21, §22, §23

### Phase 2 — Responsive rescue

Cart → card layout on mobile (not a table). Orders → same. Products filter → drawer/accordion. Profile grid → stack. Remove reliance on `overflow-x: hidden`. Touch targets to 44px.
→ §18, §10.2, §7.1, §7.8, §29.7

### Phase 3 — Icon + image system

Replace all 95 emoji with an outline SVG set. Normalize product images to 1:1. Add category photography. Render the hero banner image that already exists.
→ §3.3, §3.5, §8.1, §26

### Phase 4 — Navigation + header

Consolidate three bars into two. Apply nav active states. Sticky nav. Mini-cart drawer. Account dropdown. Search a11y + keyboard.
→ §5

### Phase 5 — Core commerce

Product card badge discipline. Homepage deduplication + section differentiation. Product detail: specs table, Buy Now, star rating input, review distribution. Filter chips. Pagination rewrite.
→ §3.1, §3.2, §6, §7, §9

### Phase 6 — Conversion + trust

Policy pages. Checkout nav stripping + progress steps. Free-shipping progress bar. Guest checkout. Payment honesty. Order emails. Password reset. Wishlist remove.
→ §11, §12.1, §13.1, §17.2, §28

### Phase 7 — Polish + QA

Loading/skeleton states, remove `location.reload()` patterns, replace `confirm()` with modals, hover/transition consistency, `prefers-reduced-motion`, cross-page regression pass.
→ §4.10, §10.3, §10.4, §19.13

---

## Closing Statements

### 1. The biggest visual problems

**Emoji as the entire icon system (95 instances), and six identical `📂` folder glyphs standing in for category imagery.** Nothing else on the site broadcasts "generated" as loudly. Second: a hero that is a blue gradient rectangle while a real hero photograph sits unused in `uploads/banners/`. Third: product photography spanning 0.65–1.50 aspect ratios forced through a single crop, so half the catalog is visibly mangled.

### 2. The biggest UX problems

**The mobile cart is not merely awkward — it is unusable.** Remove and quantity controls render at x=584 in a 375px viewport with scrolling disabled. A phone customer cannot change their order. Alongside it: the product listing gives 69% of a phone screen to filters and pushes products off-canvas, and there is no password reset flow at all for a locked-out customer.

### 3. The biggest AI-slop problems

**24 product cards rendering 13 unique products, with 79% carrying "Featured" and 100% carrying a discount badge.** This is the purest expression of the problem: structure generated to fill a template rather than to merchandise a catalog, with every emphasis device applied to everything until none of them mean anything. The copy follows the same pattern — "elevated, transparent, seamless" — abstract virtue words with nothing verifiable behind them.

### 4. The most important changes to make first

1. **Fix the cart/checkout arithmetic.** ৳134,420 − ৳6,960 = ৳134,420 on screen. A customer who notices this leaves and does not come back. Purely a display fix — the charge is correct.
2. **Delete `reset_passwords.php`** and guard `setup.php` / `download_images.php`. Anyone can currently reset every demo account.
3. **Make the mobile cart usable.** Convert the table to stacked cards.
4. **Extract the 336 inline styles into classes.** Unglamorous, but every responsive and consistency fix is blocked until layout stops living in `style=` attributes.
5. **Replace emoji with an SVG icon set and give categories real images.** The single largest perceived-quality jump per hour spent.

### 5. What must NOT be changed — it already works well

- **The entire backend.** 16 model classes, 18 REST endpoints, CSRF on every mutation, prepared statements throughout, guest→user cart merging, the coupon/offer engine, verified-buyer review gating. This is genuinely well-built and is not the problem.
- **`includes/product-card.php`** — the one properly reusable component. Its structure (image → category → title → rating → price → CTA) follows retail convention correctly. It needs badge discipline and an image-ratio fix, not a rewrite.
- **Empty states** — cart, wishlist, orders, and search-zero-results are consistently well-executed with icon, headline, explanation, and a clear action. Better than most production sites. Keep the pattern; just swap the emoji for icons.
- **The toast system** in `app.js` — clean implementation with types, animation, and auto-dismiss. Only needs `aria-live`.
- **Alt text** — present and meaningful on every image. Do not regress this.
- **Form input styling** (`.form-control`) — consistent, with a proper focus ring. It is the one component whose focus state is already correct; extend that treatment to buttons rather than changing it.
- **The blue-and-white palette direction** — sound for a general marketplace. The problem is token discipline, not hue choice.
- **`loading="lazy"` on product images** — correct, keep.
- **The URL/filter architecture** in `products.php` (query-param driven, shareable, back-button friendly) — the *mechanism* is right even though the *UI* needs work.
