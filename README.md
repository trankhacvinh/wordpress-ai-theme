# WordPress AI Theme

Bộ khung WordPress nhẹ gồm theme trắng và plugin lõi để làm website bằng AI nhưng vẫn cho khách cập nhật nội dung trong WordPress Admin.

## Thành phần

- `theme/pmedia-ai-blank`: theme trắng, không phụ thuộc page builder, render section/component từ dữ liệu động.
- `plugin/pmedia-ai-core`: plugin quản lý Section Builder, Nested Component Builder, Prompt Builder không cần API key, Site Generator theo sitemap, SEO field, custom post type và renderer helper.
- `.github/workflows/build-zip.yml`: GitHub Actions tự lint PHP, build `.zip`, upload artifact và publish release asset khi tạo tag `v*`.

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

```text
PMEDIA AI > Prompt Builder
→ nhập brief/sitemap
→ copy prompt
→ dán sang AI bên ngoài
→ AI trả JSON
→ dán JSON vào WordPress
→ import thành nhiều Page
```

Plugin import được dạng:

```json
{
  "pages": [
    {
      "path": "/",
      "title": "Trang chủ",
      "seo_title": "",
      "seo_description": "",
      "sections": []
    }
  ]
}
```

## Component nâng cao

Hỗ trợ các component cơ bản:

- `hero`
- `content`
- `services`
- `pricing`
- `faq`
- `cta`
- `contact`

Hỗ trợ các component tương tác nâng cao:

- `modal`
- `gallery`
- `slider`
- `tabs`
- `accordion`
- `portfolio`

Các component nâng cao được render bằng template PHP riêng trong theme và chạy bằng `interactive.js`. Không cần để AI sinh CSS/JS tùy ý.

## Nested component

Có thể lồng component bằng `children`.

Ví dụ modal chứa gallery:

```json
{
  "type": "modal",
  "id": "project-gallery-modal",
  "button_text": "Xem thư viện ảnh",
  "modal_title": "Hình ảnh dự án",
  "modal_size": "large",
  "children": [
    {
      "type": "gallery",
      "variant": "grid",
      "lightbox": true,
      "title": "Thư viện ảnh",
      "items": [
        {
          "image": "/wp-content/uploads/anh-1.jpg",
          "title": "Ảnh 1",
          "description": "Mô tả ảnh 1"
        }
      ]
    }
  ]
}
```

Ví dụ portfolio item mở modal chi tiết, trong modal có slider + content:

```json
{
  "type": "portfolio",
  "variant": "filterable_grid",
  "title": "Dự án đã triển khai",
  "filters": ["Tất cả", "Website", "Mini App", "Phần mềm"],
  "items": [
    {
      "title": "Website phòng khám",
      "category": "Website",
      "image": "/wp-content/uploads/clinic-cover.jpg",
      "description": "Website giới thiệu dịch vụ phòng khám.",
      "modal": {
        "title": "Chi tiết website phòng khám",
        "children": [
          {
            "type": "slider",
            "variant": "cards",
            "items": [
              {
                "image": "/wp-content/uploads/clinic-1.jpg",
                "title": "Trang chủ",
                "description": "Giao diện trang chủ"
              }
            ]
          },
          {
            "type": "content",
            "title": "Mô tả dự án",
            "content": "<p>Dự án tối ưu mobile, SEO và quản trị nội dung.</p>"
          }
        ]
      }
    }
  ]
}
```

## Quy tắc nested nên dùng

- `modal.children`: có thể chứa `gallery`, `slider`, `content`, `cta`, `tabs`, `accordion`.
- `tabs.items[].children`: có thể chứa `content`, `gallery`, `pricing`, `services`, `cta`.
- `accordion.items[].children`: có thể chứa `content`, `gallery`, `contact`, `cta`.
- `portfolio.items[].modal`: modal chi tiết riêng cho từng item.
- Không để AI tự viết JavaScript hoặc CSS trong JSON.

## Section Builder

Trong từng Page/Post/Service/Project sẽ có meta box **PMEDIA AI Section Builder**.

Có thể:

- Thêm section/component.
- Xóa component.
- Nhân bản component.
- Kéo thả đổi thứ tự.
- Thêm/xóa item trong repeater như services, pricing, FAQ, gallery, slider, portfolio.
- Chọn ảnh từ WordPress Media Library.
- Mở tab JSON để copy/paste nội dung từ AI.
- Dùng field JSON nâng cao như `children_json`, `modal_json` khi cần nested phức tạp.

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

## Nguyên tắc thiết kế

WordPress giữ phần mạnh nhất: CMS, route, permalink, user, media, database. Plugin giữ dữ liệu và logic nội dung. Theme chỉ render giao diện. Nhờ vậy có thể dùng AI để sinh layout HTML/CSS nhưng khách vẫn cập nhật nội dung trong admin mà không sửa code.

## Build local

```bash
mkdir -p dist
cd theme && zip -r ../dist/pmedia-ai-blank.zip pmedia-ai-blank
cd ../plugin && zip -r ../dist/pmedia-ai-core.zip pmedia-ai-core
```
