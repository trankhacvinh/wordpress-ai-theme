# WordPress AI Theme

Bộ khung WordPress nhẹ gồm theme trắng và plugin lõi để làm website bằng AI nhưng vẫn cho khách cập nhật nội dung trong WordPress Admin.

## Thành phần

- `theme/pmedia-ai-blank`: theme trắng, không phụ thuộc page builder, render section từ dữ liệu động.
- `plugin/pmedia-ai-core`: plugin quản lý section JSON, SEO field, custom post type và renderer helper.
- `.github/workflows/build-zip.yml`: GitHub Actions tự lint PHP, build `.zip`, upload artifact và publish release asset khi tạo tag `v*`.

## Cấu trúc

```text
.
├── theme/
│   └── pmedia-ai-blank/
│       ├── style.css
│       ├── functions.php
│       ├── page.php
│       ├── sections/
│       └── assets/
├── plugin/
│   └── pmedia-ai-core/
│       ├── pmedia-ai-core.php
│       ├── includes/
│       └── assets/
└── .github/
    └── workflows/
        └── build-zip.yml
```

## Cách dùng nhanh

1. Vào tab **Actions** của GitHub repo.
2. Chạy workflow **Build WordPress ZIP Packages** hoặc push code mới.
3. Tải artifact `wordpress-ai-theme-packages`.
4. Cài `pmedia-ai-core.zip` trong WordPress Admin > Plugins.
5. Cài `pmedia-ai-blank.zip` trong WordPress Admin > Appearance > Themes.
6. Tạo Page mới, dán JSON section vào meta box **PMEDIA AI Sections**.
7. Publish page và dùng permalink WordPress như `/about`, `/dich-vu`, `/lien-he`.

## Section hỗ trợ

- `hero`
- `content`
- `services`
- `pricing`
- `faq`
- `cta`
- `contact`

## JSON mẫu

```json
[
  {
    "type": "hero",
    "eyebrow": "PMEDIA AI Website",
    "title": "Website đẹp, nhẹ, dễ cập nhật nội dung",
    "description": "WordPress giữ vai trò CMS và routing. Theme trắng render giao diện AI-generated.",
    "button_text": "Tư vấn ngay",
    "button_link": "/lien-he",
    "secondary_button_text": "Xem dịch vụ",
    "secondary_button_link": "/dich-vu"
  },
  {
    "type": "services",
    "title": "Dịch vụ chính",
    "items": [
      {
        "title": "Thiết kế website",
        "description": "Landing page, website công ty, website dịch vụ."
      },
      {
        "title": "Mini CMS",
        "description": "Quản trị nội dung gọn hơn WordPress truyền thống."
      }
    ]
  }
]
```

## Nguyên tắc thiết kế

WordPress giữ phần mạnh nhất: CMS, route, permalink, user, media, database. Plugin giữ dữ liệu và logic nội dung. Theme chỉ render giao diện. Nhờ vậy có thể dùng AI để sinh layout HTML/CSS nhưng khách vẫn cập nhật nội dung trong admin mà không sửa code.

## Build local

```bash
mkdir -p dist
cd theme && zip -r ../dist/pmedia-ai-blank.zip pmedia-ai-blank
cd ../plugin && zip -r ../dist/pmedia-ai-core.zip pmedia-ai-core
```
