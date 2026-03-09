# Avantiy API Integration Guideline — For Nick

> **Created**: March 8, 2026  
> **From**: Filip
> **To**: Nick
> **Purpose**: Complete technical spec of every API call Epicurus One needs from the Avantiy side to enable the Duda App Store integration.  
> **Context**: Epicurus One is an AI-powered SEO/AEO content engine. It generates full blog articles (3,000+ words, images, FAQ, schema markup, internal/external links, YouTube embeds) and auto-publishes them to the user's website on a schedule. We need Avantiy API equivalents so Epicurus can publish to Avantiy-managed sites.

---

## What We Need From You (Priority Order)

---

### TIER 1 — MUST HAVE (Day 1)

**1. Create Blog Post**
- We send: title, full HTML body, slug, excerpt/meta description, status (draft or publish), scheduled publish date, tags/categories if supported
- You return: post ID + published URL
- This is the core action — Epicurus generates an article and pushes it to the user's site

**2. Upload Media / Featured Image**
- We send: binary JPEG file (multipart/form-data), alt text
- You return: media ID + public URL
- We send 1 thumbnail (1536×1024) + 1-3 inline images (1024×1024) per article
- We need a way to attach the featured image to the blog post (either at creation or as a separate call)

**3. Set SEO Meta Fields**
- We send: meta title, meta description, focus keyword (per post)
- Equivalent to what Yoast/RankMath do on WordPress
- If Avantiy handles SEO meta natively, we just need the field names

**4. Account Status Check**
- We send: site_name (Duda site identifier)
- You return: is this site active, what plan are they on, are they allowed to use Epicurus
- Called before every publish action and on SSO open

---

### TIER 2 — MUST HAVE (Within First Week)

**5. List Existing Blog Posts**
- We send: site_name
- You return: array of posts (ID, title, slug, URL, publish date)
- Needed to avoid duplicate topics and build internal links to existing content

**6. Get Single Blog Post**
- We send: site_name + post ID
- You return: full post (title, HTML body, slug, meta, status)
- For editing, preview, status checks

**7. Update Blog Post**
- We send: post ID + updated fields (title, body, meta, etc.)
- For content refreshes and SEO improvements on existing articles

**8. Delete Blog Post**
- We send: post ID
- For cleanup when user requests removal

**9. Get Site Info**
- We send: site_name
- You return: domain/URL, site name, language, business category
- Used during initial site analysis to understand the niche

**10. Get Site Pages**
- We send: site_name
- You return: list of all pages (URL, title, page type)
- Critical for: internal linking (we link new articles to existing pages), duplicate detection, site structure analysis
- If you can also return meta descriptions and heading structure per page, even better — saves us from crawling

---

### TIER 3 — SHOULD HAVE (Week 2)

**11. Install Notification**
- We call you when a site installs Epicurus via Duda
- We send: site_name, plan UUID, account info
- So your system knows this site is active on Epicurus

**12. Uninstall Notification**
- We call you when a site uninstalls
- We send: site_name
- So you can deactivate on your end

**13. Plan Change Notification**
- We call you when upgrade/downgrade happens
- We send: site_name + new plan UUID

**14. Upload Inline Images to Content Library**
- Same as #2 but for the site's general media library (not attached to a specific post)
- We upload 1-3 editorial images per article that get embedded inline in the HTML

---

### TIER 4 — NICE TO HAVE (Later)

**15. Get Page Content**
- We send: site_name + page URL or ID
- You return: page body content, headings, meta tags
- For deeper niche analysis — if you have this in structured form already, it's faster and more reliable than us crawling

**16. Inject Site-Wide HTML**
- We send: site_name + HTML snippet
- Injects into all pages (used for verification scripts, global schema markup)

---

## The Data We Push (What Becomes a Blog Post)

When Epicurus publishes, here's exactly what we send to your API:

| Field | Description | Example |
|-------|-------------|---------|
| **title** | Article headline | "Best Camping Spots in the UK for 2026" |
| **body** | Full HTML | Complete HTML with H2s, H3s, paragraphs, inline images, YouTube embeds, FAQ, Key Takeaways, CTA block, Schema JSON-LD |
| **slug** | URL-friendly path | "best-camping-spots-uk" |
| **excerpt** | Short summary | "Discover the top camping spots..." (120-150 chars) |
| **status** | Draft or publish | "publish" |
| **scheduled_date** | ISO 8601 timestamp | "2026-03-15T10:07:00Z" |
| **featured_image** | Binary JPEG (1536×1024) | Uploaded separately, attached by media ID |
| **inline_images** | 1-3 binary JPEGs (1024×1024) | Embedded in HTML body as `<figure><img>` |
| **seo_title** | SEO-optimized title | "Best Camping Spots UK 2026 \| Brand Name" |
| **seo_description** | Meta description | Same as excerpt |
| **focus_keyword** | Primary target keyword | "camping spots uk" |
| **tags/categories** | Topic labels | ["camping", "uk travel", "outdoors"] |
| **schema_markup** | Structured data | Article + FAQ JSON-LD (injected into HTML body) |

---

## Blog Post Payload Structure

This is the JSON structure that Epicurus generates per article. The fields relevant to your API are under `page`, `meta`, and `content`:

```json
{
  "page": {
    "slug": "best-camping-spots-uk",
    "type": "seo_article",
    "template": "article_v1",
    "published_at": null,
    "scheduled_for": "2026-03-15T10:07:00Z"
  },
  "meta": {
    "title": "SEO title (max ~55 chars, keyword first)",
    "description": "Meta description (120-150 chars)",
    "keywords": ["primary keyword", "secondary", "..."],
    "canonical_url": "",
    "robots": "index, follow",
    "og_title": "Open Graph title",
    "og_description": "Open Graph description",
    "schema": {
      "article": {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": "...",
        "description": "...",
        "author": { "@type": "Organization", "name": "Brand Name" },
        "datePublished": "2026-03-06T...",
        "keywords": ["..."]
      },
      "faq": {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
          {
            "@type": "Question",
            "name": "Question text?",
            "acceptedAnswer": { "@type": "Answer", "text": "Answer text" }
          }
        ]
      }
    }
  },
  "content": {
    "h1": "Main Headline With Keyword",
    "intro": "Opening paragraph (150-200 words), may contain <a href> links",
    "sections": [
      {
        "h2": "Section Heading",
        "content": "Section body (300-500 words), may contain <a href> and [VIDEO_EMBED_N] placeholders",
        "h3s": [
          { "heading": "Subheading", "content": "Subsection body (150-250 words)" }
        ]
      }
    ],
    "faq": [
      { "question": "FAQ question?", "answer": "80-150 word answer" }
    ],
    "key_takeaways": ["Bullet point 1", "Bullet point 2", "..."],
    "internal_links": [
      { "url": "https://site.com/page", "anchor_text": "link text", "context": "sentence" }
    ],
    "external_links": [
      { "url": "https://external.com", "anchor_text": "link text", "context": "sentence" }
    ],
    "videos": [
      {
        "videoId": "dQw4w9WgXcQ",
        "title": "Video Title",
        "embedUrl": "https://www.youtube.com/embed/dQw4w9WgXcQ",
        "embedHtml": "<iframe ...></iframe>"
      }
    ]
  }
}
```

**Note:** We convert this JSON to full rendered HTML before sending it to your create blog post endpoint. You receive ready-to-publish HTML, not raw JSON. The JSON above is just so you understand the source structure.

A CTA block may also be appended at publish time: `{ text, buttonText, buttonUrl }` from the user's site settings.

---

## WordPress Publish Flow (Reference for Your API Design)

This is our current WordPress flow — your Avantiy API needs to support equivalent operations:

**Step 1 — Upload featured image:**
- `POST /wp/v2/media` with binary JPEG (multipart/form-data) + alt text
- Returns: media ID + public URL

**Step 2 — Upload inline images (1-3 per article):**
- Same media endpoint, one call per image
- Returns: media ID + public URL per image (we embed the URLs in the HTML body)

**Step 3 — Create the post:**

| Field | Value |
|-------|-------|
| title | Article headline |
| content | Full rendered HTML (all sections, images, embeds, FAQ, schema) |
| status | "publish" or "draft" |
| slug | URL-friendly path |
| excerpt | Meta description |
| featured_media | Media ID from step 1 |

**Step 4 — Set SEO meta:**
- Meta title, meta description, focus keyword attached to the post

**What we need from your Avantiy API equivalents:**
1. Create a blog post (title, HTML body, slug, excerpt, status, publish date)
2. Upload media and attach featured image
3. Set SEO meta fields (title, description, focus keyword)
4. Return the published URL + post ID

---

## Site Data We Need to Read

To generate relevant articles, Epicurus analyzes the user's existing site. We need this data per page:

| Data | What we use it for |
|------|-------------------|
| Page title | Niche detection, avoiding duplicate topics |
| Meta description | Understanding existing SEO coverage |
| H1/H2/H3 headings | Content gap analysis |
| Page URLs | Internal linking (linking new articles to existing pages) |
| Body text | Niche and topic detection |

**What we need from you:** An endpoint that returns the site's page list with titles, URLs, and ideally meta descriptions + headings. If Avantiy already stores this in structured form, that's perfect — otherwise we can crawl the public site ourselves.

---

## Image/Media Specs

| Image type | Size | Format | Per article |
|-----------|------|--------|-------------|
| Featured image (thumbnail) | 1536×1024 | JPEG | 1 |
| Inline editorial images | 1024×1024 | JPEG | 1-3 |

- We send images as **binary JPEG files via multipart/form-data** (not base64, not URLs)
- We need back: **media ID + public URL** per uploaded image
- Featured image gets attached to the blog post; inline images get embedded in the HTML body as `<figure><img src="PUBLIC_URL" alt="..."></figure>`

---

## How Publishing Gets Triggered

Epicurus runs an automated scheduler. When an article is due for publishing:

1. Images are generated and uploaded to the site via your media API
2. HTML is built from the article data
3. Blog post is created on the site via your create post API
4. SEO meta fields are set
5. We record the published URL + post ID on our side

Articles are published on a drip schedule throughout the day. Your API will receive multiple create/upload calls per day per active site (typically 1-2 articles per day depending on the user's plan).

**Platform routing:** On our side, each site has a `platform` value. Avantiy-managed sites would route all publish operations through your API endpoints.

---

## Quick Reference — All 16 API Calls

| # | Call | Priority | Direction |
|---|------|----------|-----------|
| 1 | Create blog post | MUST — Day 1 | Epicurus → Avanti |
| 2 | Upload media / featured image | MUST — Day 1 | Epicurus → Avanti |
| 3 | Set SEO meta fields | MUST — Day 1 | Epicurus → Avanti |
| 4 | Account status check | MUST — Day 1 | Epicurus → Avanti |
| 5 | List blog posts | MUST — Week 1 | Epicurus ← Avanti |
| 6 | Get single blog post | MUST — Week 1 | Epicurus ← Avanti |
| 7 | Update blog post | MUST — Week 1 | Epicurus → Avanti |
| 8 | Delete blog post | MUST — Week 1 | Epicurus → Avanti |
| 9 | Get site info | MUST — Week 1 | Epicurus ← Avanti |
| 10 | Get site pages | MUST — Week 1 | Epicurus ← Avanti |
| 11 | Install notification | SHOULD — Week 2 | Epicurus → Avanti |
| 12 | Uninstall notification | SHOULD — Week 2 | Epicurus → Avanti |
| 13 | Plan change notification | SHOULD — Week 2 | Epicurus → Avanti |
| 14 | Upload to content library | SHOULD — Week 2 | Epicurus → Avanti |
| 15 | Get page content | NICE — Later | Epicurus ← Avanti |
| 16 | Inject site-wide HTML | NICE — Later | Epicurus → Avanti |

---

## What We Need Back From You

Once you've reviewed this, we need from your side:

1. **Base URL** for the Avantiy API
2. **Auth method** — API key, Bearer token, Basic Auth, OAuth? How do we authenticate per-site?
3. **Endpoint paths + payload formats** for each call above
4. **Response formats** — what does each endpoint return?
5. **Rate limits** — any throttling we should know about?
6. **Error codes** — how do you signal failures?

With that info we can wire up our side immediately.

---

*Generated March 8, 2026 — Epicurus One / Leonova Technologies Ltd*
