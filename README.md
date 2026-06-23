# WordPress AI Theme

Bộ khung WordPress nhẹ gồm theme trắng và plugin lõi để làm website bằng AI nhưng vẫn cho khách cập nhật nội dung trong WordPress Admin.

## Thành phần

- `theme/pmedia-ai-blank`: theme trắng, không phụ thuộc page builder, render section/component từ dữ liệu động.
- `plugin/pmedia-ai-core`: plugin quản lý Global Settings, Design Settings, Custom Code, Section Builder, Tree Builder, Prompt Builder không cần API key, Site Generator theo sitemap, SEO field, custom post type và renderer helper.
- `.github/workflows/build-zip.yml`: GitHub Actions tự lint PHP, build `.zip`, upload artifact và publish release asset khi tạo tag `v*`.

## Cách dùng nhanh

1. Vào tab **Actions** của GitHub repo.
2. Chạy workflow **Build WordPress ZIP Packages** hoặc push code mới.
3. Tải artifact `wordpress-ai-theme-packages`.
4. Cài `pmedia-ai-core.zip` trong WordPress Admin > Plugins.
5. Cài `pmedia-ai-blank.zip` trong WordPress Admin > Appearance > Themes.
6. Vào **PMEDIA AI > Global Settings** để chỉnh header/menu/footer/mobile.
7. Vào **PMEDIA AI > Design Settings** để chỉnh font, màu, typography, radius, spacing.
8. Vào **PMEDIA AI > Custom Code** nếu cần CSS/JS hoặc external assets dùng chung toàn site.
9. Không có API key: vào **PMEDIA AI > Prompt Builder**.
10. Vào từng Page để chỉnh nội dung bằng **Section Builder**, **Tree Builder** hoặc **Page Custom Code**.

## Design Settings

Vào:

```text
PMEDIA AI > Design Settings
```

Có thể chỉnh:

- Google Fonts CSS URL
- Body font-family
- Heading font-family
- Base font size
- Body/heading line-height
- Heading letter-spacing
- Primary/secondary/text/muted colors
- Border radius
- Button radius
- Section spacing
- Card shadow
- Custom root CSS variables

## Custom Code

Có 3 cấp custom code:

### 1. Global Custom Code

Vào:

```text
PMEDIA AI > Custom Code
```

Có thể thêm:

- External CSS URLs
- External JS Head URLs
- External JS Footer URLs
- Global CSS
- Global JS Head
- Global JS Footer
- Before Body HTML
- Công tắc bật/tắt Global Custom Code / External Assets

### 2. Page Custom Code

Trong từng Page/Post/Service/Project có meta box:

```text
PMEDIA AI Page Custom Code
```

Có thể thêm:

- Body class riêng
- Tắt Global Custom Code trên trang đó
- Page CSS
- Page JS Footer

### 3. Section targeting

Mỗi component có thêm các field:

- `custom_id`
- `custom_class`
- `style_vars`
- `data_attrs`

## Media / Embed Components

Builder hỗ trợ thêm:

- `rich_text`: nội dung văn bản có style.
- `media`: ảnh hoặc video đơn giản.
- `video`: video upload/URL có poster, autoplay, muted, loop, controls.
- `iframe`: nhúng frame ngoài bằng URL.
- `html`: block HTML an toàn.
- `shortcode`: nhúng shortcode của plugin WordPress.

Ví dụ shortcode:

```json
{
  "type": "shortcode",
  "title": "Form liên hệ",
  "shortcode": "[plugin_shortcode id=123]",
  "wrapper": "card"
}
```

## Prompt Builder không cần API key

```text
PMEDIA AI > Prompt Builder
→ nhập brief/sitemap
→ copy prompt
→ dán sang AI bên ngoài
→ AI trả JSON
→ dán JSON vào WordPress
→ import thành nhiều Page
```

## Component nâng cao

Component cơ bản:

- `hero`
- `content`
- `services`
- `pricing`
- `faq`
- `cta`
- `contact`

Component tương tác/media nâng cao:

- `modal`
- `gallery`
- `slider`
- `tabs`
- `accordion`
- `portfolio`
- `rich_text`
- `media`
- `video`
- `iframe`
- `html`
- `shortcode`

## Tree Builder UI

Trong meta box **PMEDIA AI Section Builder** có nút **Tree Builder**.

Tree Builder dùng để quản lý cấu trúc lồng nhau:

- Xem toàn bộ section/component dưới dạng cây.
- Chọn từng node để sửa JSON riêng hoặc form editor.
- Thêm root component.
- Thêm child component vào node đang chọn.
- Xóa node.
- Nhân bản node.
- Di chuyển/kéo thả node.
- Đồng bộ ngược về JSON chính và Form Builder.

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

## Nguyên tắc thiết kế

WordPress giữ phần mạnh nhất: CMS, route, permalink, user, media, database. Plugin giữ dữ liệu và logic nội dung. Theme chỉ render giao diện. Nhờ vậy có thể dùng AI để sinh layout HTML/CSS nhưng khách vẫn cập nhật nội dung trong admin mà không sửa code.

## Build local

```bash
mkdir -p dist
cd theme && zip -r ../dist/pmedia-ai-blank.zip pmedia-ai-blank
cd ../plugin && zip -r ../dist/pmedia-ai-core.zip pmedia-ai-core
```
