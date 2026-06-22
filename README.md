# WordPress AI Theme

Bộ khung WordPress nhẹ gồm:

- `theme/pmedia-ai-blank`: theme trắng để render giao diện HTML/CSS/JS do AI tạo.
- `plugin/pmedia-ai-core`: plugin quản lý section, custom field, custom post type và render dữ liệu động.
- `.github/workflows/build-zip.yml`: GitHub Actions tự đóng gói theme/plugin thành file `.zip`.

## Cấu trúc

```text
.
├── theme/
│   └── pmedia-ai-blank/
├── plugin/
│   └── pmedia-ai-core/
└── .github/
    └── workflows/
        └── build-zip.yml
```

## Cách dùng nhanh

1. Tải artifact từ GitHub Actions sau mỗi lần chạy workflow.
2. Cài `pmedia-ai-core.zip` trong WordPress Admin > Plugins.
3. Cài `pmedia-ai-blank.zip` trong WordPress Admin > Appearance > Themes.
4. Tạo Page mới, dán JSON section vào meta box `PMEDIA AI Sections`.
5. Theme sẽ render giao diện theo dữ liệu JSON.

## Ý tưởng chính

WordPress giữ vai trò CMS, route, permalink, user, media, database. Theme chỉ render giao diện. Plugin giữ logic nội dung để khi đổi theme không mất dữ liệu.
