# PMEDIA AI Custom Code

Custom Code cho phép thêm CSS/JS ở 3 cấp:

1. Global: áp dụng toàn site.
2. Page: áp dụng riêng từng Page/Post/Service/Project.
3. Section: target từng component bằng custom ID/class/style variables/data attributes.

## Global Custom Code

Vào:

```text
PMEDIA AI > Custom Code
```

Có các field:

- Bật/tắt Global Custom Code
- Global CSS
- Global JS Head
- Global JS Footer
- Before Body HTML

Global CSS in ra `wp_head` trong thẻ `style`.
Global JS Head in ra `wp_head` trong thẻ `script`.
Global JS Footer in ra `wp_footer` trong thẻ `script`.
Before Body HTML in trực tiếp trước `</body>`.

Không cần nhập thẻ `<style>` hoặc `<script>` trong ô CSS/JS.

## Page Custom Code

Trong từng Page/Post sẽ có meta box:

```text
PMEDIA AI Page Custom Code
```

Có thể chỉnh:

- Body class riêng
- Tắt Global Custom Code trên trang này
- Page CSS
- Page JS Footer

Chỉ admin `manage_options` mới thấy và lưu được meta box này.

## Section targeting

Mỗi component trong Section Builder/Tree Builder có thêm:

- `custom_id`
- `custom_class`
- `style_vars`
- `data_attrs`

Ví dụ:

```json
{
  "type": "hero",
  "custom_id": "home-hero",
  "custom_class": "hero-premium hero-dark",
  "style_vars": {
    "--hero-height": "92vh",
    "--hero-radius": "32px"
  },
  "data_attrs": {
    "tracking": "hero"
  }
}
```

Theme sẽ render ra HTML có ID/class/style/data attribute tương ứng.

## Khuyến nghị

- Ưu tiên CSS global/page trước khi thêm JS.
- JS nên đặt ở footer nếu không bắt buộc chạy trong head.
- Dùng Page Custom Code cho landing page/campaign đặc biệt.
- Dùng Section `custom_id` hoặc `custom_class` để CSS/JS target chính xác, tránh query selector quá rộng.
- Nếu site lỗi sau khi thêm code, vào `PMEDIA AI > Custom Code` và tắt Global Custom Code.
