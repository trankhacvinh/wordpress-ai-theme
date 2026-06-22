<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Section_Schema
{
    public static function schema(): array
    {
        return [
            'hero' => [
                'label' => 'Hero',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'button_text' => ['label' => 'Nút chính', 'type' => 'text'],
                    'button_link' => ['label' => 'Link nút chính', 'type' => 'text'],
                    'secondary_button_text' => ['label' => 'Nút phụ', 'type' => 'text'],
                    'secondary_button_link' => ['label' => 'Link nút phụ', 'type' => 'text'],
                    'image' => ['label' => 'Ảnh', 'type' => 'image'],
                    'image_alt' => ['label' => 'Alt ảnh', 'type' => 'text'],
                ],
            ],
            'content' => [
                'label' => 'Content',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'content' => ['label' => 'Nội dung HTML', 'type' => 'textarea'],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'items' => [
                        'label' => 'Danh sách dịch vụ/lợi ích',
                        'type' => 'repeater',
                        'item_fields' => [
                            'icon' => ['label' => 'Icon/Text ngắn', 'type' => 'text'],
                            'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                            'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
            'pricing' => [
                'label' => 'Pricing',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'items' => [
                        'label' => 'Gói giá',
                        'type' => 'repeater',
                        'item_fields' => [
                            'name' => ['label' => 'Tên gói', 'type' => 'text'],
                            'price' => ['label' => 'Giá', 'type' => 'text'],
                            'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                            'features' => ['label' => 'Tính năng, mỗi dòng một mục', 'type' => 'lines'],
                        ],
                    ],
                ],
            ],
            'faq' => [
                'label' => 'FAQ',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'items' => [
                        'label' => 'Câu hỏi thường gặp',
                        'type' => 'repeater',
                        'item_fields' => [
                            'question' => ['label' => 'Câu hỏi', 'type' => 'text'],
                            'answer' => ['label' => 'Trả lời', 'type' => 'textarea'],
                        ],
                    ],
                ],
            ],
            'cta' => [
                'label' => 'CTA',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'button_text' => ['label' => 'Nút', 'type' => 'text'],
                    'button_link' => ['label' => 'Link nút', 'type' => 'text'],
                ],
            ],
            'contact' => [
                'label' => 'Contact',
                'fields' => [
                    'eyebrow' => ['label' => 'Eyebrow', 'type' => 'text'],
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'phone' => ['label' => 'Điện thoại', 'type' => 'text'],
                    'email' => ['label' => 'Email', 'type' => 'text'],
                    'address' => ['label' => 'Địa chỉ', 'type' => 'textarea'],
                    'form_shortcode' => ['label' => 'Form shortcode', 'type' => 'text'],
                ],
            ],
        ];
    }

    public static function defaults(): array
    {
        $defaults = [];
        foreach (array_keys(self::schema()) as $type) {
            $defaults[$type] = self::default_section($type);
        }

        return $defaults;
    }

    public static function default_section(string $type): array
    {
        switch ($type) {
            case 'hero':
                return [
                    'type' => 'hero',
                    'eyebrow' => 'PMEDIA AI Website',
                    'title' => 'Tiêu đề chính của trang',
                    'description' => 'Mô tả ngắn gọn giá trị chính của trang.',
                    'button_text' => 'Liên hệ tư vấn',
                    'button_link' => '/lien-he',
                    'secondary_button_text' => 'Xem dịch vụ',
                    'secondary_button_link' => '/dich-vu',
                    'image' => '',
                    'image_alt' => '',
                ];
            case 'content':
                return [
                    'type' => 'content',
                    'title' => 'Nội dung chính',
                    'content' => '<p>Nhập nội dung chi tiết tại đây.</p>',
                ];
            case 'services':
                return [
                    'type' => 'services',
                    'eyebrow' => 'Dịch vụ',
                    'title' => 'Các dịch vụ chính',
                    'description' => 'Những hạng mục có thể triển khai cho khách hàng.',
                    'items' => [
                        ['icon' => '01', 'title' => 'Dịch vụ 1', 'description' => 'Mô tả ngắn dịch vụ 1.'],
                        ['icon' => '02', 'title' => 'Dịch vụ 2', 'description' => 'Mô tả ngắn dịch vụ 2.'],
                        ['icon' => '03', 'title' => 'Dịch vụ 3', 'description' => 'Mô tả ngắn dịch vụ 3.'],
                    ],
                ];
            case 'pricing':
                return [
                    'type' => 'pricing',
                    'title' => 'Bảng giá tham khảo',
                    'description' => 'Có thể điều chỉnh theo nhu cầu thực tế.',
                    'items' => [
                        ['name' => 'Cơ bản', 'price' => 'Liên hệ', 'description' => 'Phù hợp nhu cầu đơn giản.', 'features' => ['Tư vấn', 'Thiết kế cơ bản', 'Bàn giao hướng dẫn']],
                        ['name' => 'Doanh nghiệp', 'price' => 'Liên hệ', 'description' => 'Phù hợp website công ty.', 'features' => ['Giao diện riêng', 'Quản trị nội dung', 'Tối ưu SEO cơ bản']],
                    ],
                ];
            case 'faq':
                return [
                    'type' => 'faq',
                    'title' => 'Câu hỏi thường gặp',
                    'description' => '',
                    'items' => [
                        ['question' => 'Thời gian triển khai bao lâu?', 'answer' => 'Thời gian phụ thuộc phạm vi nội dung và tính năng.'],
                        ['question' => 'Tôi có tự sửa nội dung được không?', 'answer' => 'Có. Nội dung được quản lý trong WordPress Admin.'],
                    ],
                ];
            case 'cta':
                return [
                    'type' => 'cta',
                    'title' => 'Sẵn sàng bắt đầu?',
                    'description' => 'Liên hệ để được tư vấn giải pháp phù hợp.',
                    'button_text' => 'Liên hệ ngay',
                    'button_link' => '/lien-he',
                ];
            case 'contact':
                return [
                    'type' => 'contact',
                    'eyebrow' => 'Liên hệ',
                    'title' => 'Trao đổi với chúng tôi',
                    'description' => 'Để lại thông tin để được tư vấn.',
                    'phone' => '',
                    'email' => '',
                    'address' => '',
                    'form_shortcode' => '',
                ];
            default:
                return ['type' => $type];
        }
    }

    public static function sample_sections(): array
    {
        return [
            self::default_section('hero'),
            self::default_section('services'),
            self::default_section('pricing'),
            self::default_section('faq'),
            self::default_section('cta'),
        ];
    }

    public static function sample_sections_json(): string
    {
        return self::encode_sections(self::sample_sections());
    }

    public static function encode_sections(array $sections): string
    {
        return (string) wp_json_encode($sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function generate_sections_for_page(array $page, array $context): array
    {
        $type = self::infer_page_type($page);
        $brand = self::value($context, 'brand_name', get_bloginfo('name'));
        $summary = self::value($context, 'business_summary', 'Cung cấp giải pháp chuyên nghiệp cho khách hàng.');
        $target = self::value($context, 'target_customers', 'doanh nghiệp và khách hàng cần giải pháp phù hợp');
        $service = self::value($context, 'primary_service', 'dịch vụ chuyên nghiệp');
        $cta_text = self::value($context, 'primary_cta_text', 'Liên hệ tư vấn');
        $cta_link = self::value($context, 'primary_cta_link', '/lien-he');
        $title = self::value($page, 'title', 'Trang mới');

        $contact = self::default_section('contact');
        $contact['phone'] = self::value($context, 'phone', '');
        $contact['email'] = self::value($context, 'email', '');
        $contact['address'] = self::value($context, 'address', '');

        $hero = self::default_section('hero');
        $hero['eyebrow'] = $brand;
        $hero['title'] = self::hero_title($title, $type, $brand, $service);
        $hero['description'] = self::hero_description($type, $summary, $target, $service);
        $hero['button_text'] = $cta_text;
        $hero['button_link'] = $cta_link;
        $hero['secondary_button_text'] = 'Xem thêm';
        $hero['secondary_button_link'] = '#noi-dung';

        if ($type === 'home') {
            $services = self::services_section($service, $summary);
            $content = self::content_section('Vì sao chọn ' . $brand . '?', self::paragraphs([
                $brand . ' tập trung vào giải pháp thực tế, dễ triển khai và dễ vận hành cho ' . $target . '.',
                'Nội dung website được tổ chức thành từng section để có thể chỉnh sửa nhanh trong WordPress Admin mà không cần sửa code.',
                'Cách làm này giúp rút ngắn thời gian triển khai, giảm phụ thuộc vào page builder nặng và vẫn giữ được route, media, user, SEO trong WordPress.',
            ]));
            return [$hero, $services, $content, self::pricing_section(), self::faq_section(), self::cta_section($brand, $cta_text, $cta_link), $contact];
        }

        if ($type === 'about') {
            $content = self::content_section('Giới thiệu ' . $brand, self::paragraphs([
                $summary,
                $brand . ' hướng đến việc cung cấp giải pháp rõ ràng, dễ sử dụng và phù hợp với nhu cầu thực tế của ' . $target . '.',
                'Chúng tôi ưu tiên quy trình làm việc minh bạch, sản phẩm dễ bàn giao và có thể mở rộng khi doanh nghiệp phát triển.',
            ]));
            return [$hero, $content, self::services_section($service, $summary), self::cta_section($brand, $cta_text, $cta_link)];
        }

        if ($type === 'services') {
            $content = self::content_section($title, self::paragraphs([
                'Trang này trình bày các hạng mục liên quan đến ' . $title . ' dành cho ' . $target . '.',
                'Mỗi hạng mục có thể được tùy chỉnh theo quy trình, ngân sách và mục tiêu vận hành cụ thể.',
            ]));
            return [$hero, $content, self::services_section($title, $summary), self::faq_section(), self::cta_section($brand, $cta_text, $cta_link), $contact];
        }

        if ($type === 'pricing') {
            return [$hero, self::pricing_section(), self::faq_section(), self::cta_section($brand, $cta_text, $cta_link), $contact];
        }

        if ($type === 'contact') {
            return [$hero, $contact, self::faq_section()];
        }

        if ($type === 'faq') {
            return [$hero, self::faq_section(), self::cta_section($brand, $cta_text, $cta_link)];
        }

        $content = self::content_section($title, self::paragraphs([
            $title . ' là một phần trong hệ thống nội dung của ' . $brand . '.',
            $summary,
            'Nội dung trang có thể được cập nhật bằng Section Builder trong WordPress Admin.',
        ]));

        return [$hero, $content, self::cta_section($brand, $cta_text, $cta_link)];
    }

    public static function infer_page_type(array $page): string
    {
        $path = strtolower((string) ($page['path'] ?? ''));
        $title = strtolower((string) ($page['title'] ?? ''));
        $value = $path . ' ' . $title;

        if ($path === '/' || $path === '' || strpos($value, 'trang chủ') !== false || strpos($value, 'home') !== false) {
            return 'home';
        }
        if (strpos($value, 'gioi-thieu') !== false || strpos($value, 'giới thiệu') !== false || strpos($value, 'about') !== false) {
            return 'about';
        }
        if (strpos($value, 'lien-he') !== false || strpos($value, 'liên hệ') !== false || strpos($value, 'contact') !== false) {
            return 'contact';
        }
        if (strpos($value, 'bang-gia') !== false || strpos($value, 'bảng giá') !== false || strpos($value, 'pricing') !== false) {
            return 'pricing';
        }
        if (strpos($value, 'hoi-dap') !== false || strpos($value, 'faq') !== false || strpos($value, 'câu hỏi') !== false) {
            return 'faq';
        }
        if (strpos($value, 'dich-vu') !== false || strpos($value, 'dịch vụ') !== false || strpos($value, 'service') !== false) {
            return 'services';
        }

        return 'content';
    }

    private static function services_section(string $service, string $summary): array
    {
        return [
            'type' => 'services',
            'eyebrow' => 'Giải pháp',
            'title' => 'Những gì chúng tôi có thể triển khai',
            'description' => $summary,
            'items' => [
                ['icon' => '01', 'title' => 'Tư vấn & định hướng', 'description' => 'Làm rõ nhu cầu, mục tiêu và phạm vi triển khai trước khi bắt đầu.'],
                ['icon' => '02', 'title' => ucfirst($service), 'description' => 'Xây dựng giải pháp phù hợp với quy trình và ngân sách thực tế.'],
                ['icon' => '03', 'title' => 'Bàn giao & hướng dẫn', 'description' => 'Bàn giao sản phẩm kèm hướng dẫn để khách hàng có thể tự vận hành.'],
            ],
        ];
    }

    private static function pricing_section(): array
    {
        return [
            'type' => 'pricing',
            'title' => 'Gói triển khai tham khảo',
            'description' => 'Chi phí có thể điều chỉnh theo phạm vi thực tế của từng dự án.',
            'items' => [
                ['name' => 'Starter', 'price' => 'Liên hệ', 'description' => 'Phù hợp nhu cầu giới thiệu cơ bản.', 'features' => ['Tư vấn cấu trúc nội dung', 'Giao diện responsive', 'Bàn giao hướng dẫn']],
                ['name' => 'Business', 'price' => 'Liên hệ', 'description' => 'Phù hợp doanh nghiệp cần website hoàn chỉnh.', 'features' => ['Nhiều trang nội dung', 'Quản trị nội dung', 'SEO cơ bản']],
                ['name' => 'Custom', 'price' => 'Theo yêu cầu', 'description' => 'Phù hợp dự án cần tính năng riêng.', 'features' => ['Thiết kế theo quy trình', 'Tích hợp nâng cao', 'Hỗ trợ mở rộng']],
            ],
        ];
    }

    private static function faq_section(): array
    {
        return [
            'type' => 'faq',
            'title' => 'Câu hỏi thường gặp',
            'description' => '',
            'items' => [
                ['question' => 'Thời gian triển khai bao lâu?', 'answer' => 'Thời gian phụ thuộc số lượng trang, nội dung và yêu cầu giao diện.'],
                ['question' => 'Có tự cập nhật nội dung được không?', 'answer' => 'Có. Nội dung được quản lý trong WordPress Admin bằng Section Builder.'],
                ['question' => 'Có thể thêm trang mới sau này không?', 'answer' => 'Có. Có thể tạo thêm Page và dùng lại các section sẵn có.'],
                ['question' => 'Có tối ưu mobile không?', 'answer' => 'Theme được xây dựng responsive để hiển thị tốt trên nhiều kích thước màn hình.'],
            ],
        ];
    }

    private static function cta_section(string $brand, string $button_text, string $button_link): array
    {
        return [
            'type' => 'cta',
            'title' => 'Cần tư vấn giải pháp phù hợp?',
            'description' => $brand . ' có thể hỗ trợ phân tích nhu cầu và đề xuất hướng triển khai phù hợp.',
            'button_text' => $button_text,
            'button_link' => $button_link,
        ];
    }

    private static function content_section(string $title, string $content): array
    {
        return [
            'type' => 'content',
            'title' => $title,
            'content' => $content,
        ];
    }

    private static function hero_title(string $title, string $type, string $brand, string $service): string
    {
        if ($type === 'home') {
            return $brand . ' - ' . ucfirst($service) . ' chuyên nghiệp';
        }

        return $title;
    }

    private static function hero_description(string $type, string $summary, string $target, string $service): string
    {
        if ($type === 'home') {
            return $summary . ' Dành cho ' . $target . ', tập trung vào tốc độ triển khai, khả năng quản trị và hiệu quả sử dụng.';
        }

        return 'Nội dung trang được xây dựng tự động theo sitemap và có thể chỉnh sửa lại bằng Section Builder trong WordPress Admin.';
    }

    private static function paragraphs(array $paragraphs): string
    {
        $html = '';
        foreach ($paragraphs as $paragraph) {
            $html .= '<p>' . esc_html($paragraph) . '</p>';
        }

        return $html;
    }

    private static function value(array $array, string $key, string $default = ''): string
    {
        $value = $array[$key] ?? $default;
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value !== '' ? $value : $default;
    }
}
