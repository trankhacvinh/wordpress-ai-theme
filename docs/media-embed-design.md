# Media, Embed, Design Settings

## Component media/embed mới

Builder hỗ trợ thêm các component:

- `rich_text`: nội dung văn bản có style riêng.
- `media`: ảnh hoặc video từ Media Library hoặc URL.
- `video`: video URL có poster, autoplay, muted, loop, controls.
- `iframe`: nhúng nội dung ngoài bằng URL frame.
- `html`: block HTML an toàn, render qua WordPress allowed HTML.
- `shortcode`: nhúng shortcode của plugin ngoài như form, gallery, booking, map.

## Ví dụ media image

```json
{
  "type": "media",
  "media_type": "image",
  "url": "/wp-content/uploads/demo.jpg",
  "alt": "Ảnh demo",
  "caption": "Mô tả ảnh",
  "aspect_ratio": "16/9",
  "object_fit": "cover"
}
```

## Ví dụ video

```json
{
  "type": "video",
  "title": "Video giới thiệu",
  "video_url": "/wp-content/uploads/intro.mp4",
  "poster": "/wp-content/uploads/poster.jpg",
  "aspect_ratio": "16/9",
  "controls": true,
  "autoplay": false,
  "muted": true,
  "loop": false
}
```

## Ví dụ frame

```json
{
  "type": "iframe",
  "title": "Nội dung nhúng",
  "src": "https://example.com/embed",
  "aspect_ratio": "16/9",
  "loading": "lazy"
}
```

## Ví dụ shortcode plugin ngoài

```json
{
  "type": "shortcode",
  "title": "Form liên hệ",
  "shortcode": "[plugin_shortcode id=123]",
  "wrapper": "card"
}
```

## Design Settings

Vào:

```text
PMEDIA AI > Design Settings
```

Có thể cấu hình:

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
- Custom CSS root variables

## External plugin assets

Vào:

```text
PMEDIA AI > Custom Code
```

Có thể thêm:

- External CSS URLs
- External JS Head URLs
- External JS Footer URLs

Mỗi dòng một URL. Dùng cho các thư viện ngoài hoặc plugin frontend cần CSS/JS CDN.

## Khuyến nghị an toàn

- Script ngoài nên để Footer nếu không bắt buộc phải chạy ở Head.
- HTML block không dùng cho script; script nên đặt trong Custom Code.
- Frame chỉ nên dùng URL tin cậy.
- Shortcode là cách tốt nhất để nhúng output của plugin WordPress ngoài.
