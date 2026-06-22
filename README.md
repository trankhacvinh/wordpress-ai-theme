# WordPress AI Theme

Bộ khung WordPress nhẹ gồm theme trắng và plugin lõi để làm website bằng AI nhưng vẫn cho khách cập nhật nội dung trong WordPress Admin.

## Thành phần

- `theme/pmedia-ai-blank`: theme trắng, không phụ thuộc page builder, render section từ dữ liệu động.
- `plugin/pmedia-ai-core`: plugin quản lý Section Builder, Prompt Builder không cần API key, Site Generator theo sitemap, SEO field, custom post type và renderer helper.
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
6. Không có API key: vào **PMEDIA AI > Prompt Builder**.
7. Nhập brief + sitemap, bấm **Copy prompt**.
8. Mang prompt sang ChatGPT/Claude/Gemini để sinh JSON.
9. Dán JSON kết quả vào ô import để tạo/cập nhật Page.
10. Vào từng Page để chỉnh nội dung bằng **PMEDIA AI Section Builder**.

## Prompt Builder không cần API key

Prompt Builder phù hợp khi chưa muốn tích hợp OpenAI API trực tiếp vào WordPress.

Quy trình:

```text
PMEDIA AI > Prompt Builder
→ nhập brief/sitemap
→ copy prompt
→ dán sang AI bên ngoài
→ AI trả JSON
→ dán JSON vào WordPress
→ import thành nhiều Page
```

Output AI nên có dạng:

```json
{
  "pages": [
    {
      "path": "/",
      "title": "Trang chủ",
      "seo_title": "",
      "seo_description": "",
      "sections": [
        {
          "type": "hero",
          "title": "",
          "description": ""
        }
      ]
    }
  ]
}
```

Plugin sẽ:

- Import JSON dạng `{ "pages": [...] }` hoặc array page trực tiếp.
- Tạo/cập nhật Page theo `path`.
- Tạo parent/child page theo đường dẫn lồng nhau.
- Lưu `sections` vào `_pmedia_sections`.
- Lưu SEO title/description nếu có.
- Có thể đặt trang `/` làm homepage.

## Site Generator

Site Generator cho phép tạo hàng loạt Page theo sitemap bằng rule nội bộ, không cần gọi AI bên ngoài. Mỗi dòng là một trang:

```text
/ | Trang chủ
/gioi-thieu | Giới thiệu
/dich-vu | Dịch vụ
/dich-vu/thiet-ke-website | Thiết kế website
/bang-gia | Bảng giá
/cau-hoi-thuong-gap | Câu hỏi thường gặp
/lien-he | Liên hệ
```

Hệ thống sẽ:

- Tạo Page theo slug/path.
- Tạo parent/child page theo đường dẫn lồng nhau.
- Sinh section JSON phù hợp theo loại trang.
- Lưu prompt nguồn vào meta.
- Có thể đặt trang `/` làm homepage.
- Có bảng quản lý các trang đã tạo bằng PMEDIA AI.

## Section Builder

Trong từng Page/Post/Service/Project sẽ có meta box **PMEDIA AI Section Builder**.

Có thể:

- Thêm section.
- Xóa section.
- Nhân bản section.
- Kéo thả đổi thứ tự section.
- Thêm/xóa item trong repeater như services, pricing, FAQ.
- Chọn ảnh từ WordPress Media Library.
- Mở tab JSON để copy/paste nội dung từ AI.

## Section hỗ trợ

- `hero`
- `content`
- `services`
- `pricing`
- `faq`
- `cta`
- `contact`

## JSON mẫu section

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
