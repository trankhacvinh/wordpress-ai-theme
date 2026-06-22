<?php

if (!defined('ABSPATH')) {
    exit;
}

final class PMEDIA_AI_Component_Registry
{
    public static function schema(): array
    {
        return self::with_common_fields(array_merge(PMEDIA_AI_Section_Schema::schema(), self::advanced_schema()));
    }

    public static function defaults(): array
    {
        $defaults = PMEDIA_AI_Section_Schema::defaults();
        foreach (array_keys(self::advanced_schema()) as $type) {
            $defaults[$type] = self::default_component($type);
        }

        foreach ($defaults as $type => $default) {
            if (is_array($default)) {
                $defaults[$type] = array_merge(self::common_defaults(), $default);
            }
        }

        return $defaults;
    }

    private static function common_fields(): array
    {
        return [
            'custom_id' => ['label' => 'Custom ID', 'type' => 'text'],
            'custom_class' => ['label' => 'Custom class', 'type' => 'text'],
            'style_vars' => ['label' => 'CSS variables JSON, ví dụ {"--hero-height":"90vh"}', 'type' => 'json'],
            'data_attrs' => ['label' => 'Data attributes JSON, ví dụ {"tracking":"hero"}', 'type' => 'json'],
        ];
    }

    private static function common_defaults(): array
    {
        return [
            'custom_id' => '',
            'custom_class' => '',
            'style_vars' => [],
            'data_attrs' => [],
        ];
    }

    private static function with_common_fields(array $schema): array
    {
        foreach ($schema as $type => $config) {
            if (!isset($config['fields']) || !is_array($config['fields'])) {
                $config['fields'] = [];
            }
            $config['fields'] = self::common_fields() + $config['fields'];
            $schema[$type] = $config;
        }

        return $schema;
    }

    public static function advanced_schema(): array
    {
        $effects = [
            'none' => 'None',
            'fade-up' => 'Fade Up',
            'fade-in' => 'Fade In',
            'slide-left' => 'Slide Left',
            'slide-right' => 'Slide Right',
            'zoom-in' => 'Zoom In',
        ];

        return [
            'modal' => [
                'label' => 'Modal',
                'fields' => [
                    'id' => ['label' => 'ID cũ / fallback', 'type' => 'text'],
                    'button_text' => ['label' => 'Nút mở modal', 'type' => 'text'],
                    'modal_title' => ['label' => 'Tiêu đề modal', 'type' => 'text'],
                    'modal_description' => ['label' => 'Mô tả modal', 'type' => 'textarea'],
                    'modal_size' => ['label' => 'Kích thước', 'type' => 'select', 'options' => ['medium' => 'Medium', 'large' => 'Large', 'fullscreen' => 'Fullscreen']],
                    'animation' => ['label' => 'Animation', 'type' => 'select', 'options' => $effects],
                    'content' => ['label' => 'Nội dung HTML', 'type' => 'textarea'],
                    'form_shortcode' => ['label' => 'Form shortcode', 'type' => 'text'],
                    'children_json' => ['label' => 'Children JSON nâng cao', 'type' => 'json'],
                ],
            ],
            'gallery' => [
                'label' => 'Gallery',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'variant' => ['label' => 'Layout', 'type' => 'select', 'options' => ['grid' => 'Grid', 'masonry' => 'Masonry', 'slider' => 'Slider']],
                    'lightbox' => ['label' => 'Bật lightbox', 'type' => 'checkbox'],
                    'animation' => ['label' => 'Animation', 'type' => 'select', 'options' => $effects],
                    'items' => [
                        'label' => 'Ảnh',
                        'type' => 'repeater',
                        'item_fields' => [
                            'image' => ['label' => 'Ảnh URL', 'type' => 'image'],
                            'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                            'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                            'link' => ['label' => 'Link', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
            'slider' => [
                'label' => 'Slider',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'variant' => ['label' => 'Layout', 'type' => 'select', 'options' => ['cards' => 'Card slider', 'hero' => 'Hero slider', 'logos' => 'Logo slider']],
                    'autoplay' => ['label' => 'Autoplay', 'type' => 'checkbox'],
                    'interval' => ['label' => 'Interval ms', 'type' => 'number'],
                    'show_arrows' => ['label' => 'Hiện arrows', 'type' => 'checkbox'],
                    'show_dots' => ['label' => 'Hiện dots', 'type' => 'checkbox'],
                    'items' => [
                        'label' => 'Slides',
                        'type' => 'repeater',
                        'item_fields' => [
                            'image' => ['label' => 'Ảnh URL', 'type' => 'image'],
                            'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                            'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                            'button_text' => ['label' => 'Nút', 'type' => 'text'],
                            'button_link' => ['label' => 'Link nút', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
            'tabs' => [
                'label' => 'Tabs',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'items' => [
                        'label' => 'Tabs',
                        'type' => 'repeater',
                        'item_fields' => [
                            'label' => ['label' => 'Tên tab', 'type' => 'text'],
                            'content' => ['label' => 'Nội dung HTML', 'type' => 'textarea'],
                            'children_json' => ['label' => 'Children JSON', 'type' => 'json'],
                        ],
                    ],
                ],
            ],
            'accordion' => [
                'label' => 'Accordion',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'items' => [
                        'label' => 'Items',
                        'type' => 'repeater',
                        'item_fields' => [
                            'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                            'content' => ['label' => 'Nội dung HTML', 'type' => 'textarea'],
                            'children_json' => ['label' => 'Children JSON', 'type' => 'json'],
                        ],
                    ],
                ],
            ],
            'portfolio' => [
                'label' => 'Portfolio',
                'fields' => [
                    'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                    'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                    'variant' => ['label' => 'Layout', 'type' => 'select', 'options' => ['grid' => 'Grid', 'filterable_grid' => 'Filterable grid']],
                    'filters' => ['label' => 'Filters, mỗi dòng một mục', 'type' => 'lines'],
                    'items' => [
                        'label' => 'Dự án',
                        'type' => 'repeater',
                        'item_fields' => [
                            'image' => ['label' => 'Ảnh URL', 'type' => 'image'],
                            'title' => ['label' => 'Tiêu đề', 'type' => 'text'],
                            'category' => ['label' => 'Danh mục', 'type' => 'text'],
                            'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
                            'link' => ['label' => 'Link', 'type' => 'text'],
                            'modal_json' => ['label' => 'Modal JSON chi tiết', 'type' => 'json'],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function default_component(string $type): array
    {
        switch ($type) {
            case 'modal':
                return array_merge(self::common_defaults(), [
                    'type' => 'modal',
                    'id' => 'modal-demo',
                    'button_text' => 'Xem chi tiết',
                    'modal_title' => 'Chi tiết nội dung',
                    'modal_description' => 'Modal có thể chứa content, gallery, slider, form hoặc CTA.',
                    'modal_size' => 'large',
                    'content' => '<p>Nhập nội dung modal tại đây.</p>',
                    'children' => [self::default_component('gallery')],
                ]);
            case 'gallery':
                return array_merge(self::common_defaults(), [
                    'type' => 'gallery',
                    'title' => 'Thư viện ảnh',
                    'description' => 'Một số hình ảnh nổi bật.',
                    'variant' => 'grid',
                    'lightbox' => true,
                    'items' => [
                        ['image' => '', 'title' => 'Ảnh 1', 'description' => 'Mô tả ảnh 1', 'link' => ''],
                        ['image' => '', 'title' => 'Ảnh 2', 'description' => 'Mô tả ảnh 2', 'link' => ''],
                    ],
                ]);
            case 'slider':
                return array_merge(self::common_defaults(), [
                    'type' => 'slider',
                    'title' => 'Slider nổi bật',
                    'description' => '',
                    'variant' => 'cards',
                    'autoplay' => true,
                    'interval' => 5000,
                    'show_arrows' => true,
                    'show_dots' => true,
                    'items' => [
                        ['image' => '', 'title' => 'Slide 1', 'description' => 'Mô tả slide 1', 'button_text' => '', 'button_link' => ''],
                        ['image' => '', 'title' => 'Slide 2', 'description' => 'Mô tả slide 2', 'button_text' => '', 'button_link' => ''],
                    ],
                ]);
            case 'tabs':
                return array_merge(self::common_defaults(), [
                    'type' => 'tabs',
                    'title' => 'Nội dung theo tab',
                    'description' => '',
                    'items' => [
                        ['label' => 'Tab 1', 'content' => '<p>Nội dung tab 1.</p>', 'children' => []],
                        ['label' => 'Tab 2', 'content' => '<p>Nội dung tab 2.</p>', 'children' => []],
                    ],
                ]);
            case 'accordion':
                return array_merge(self::common_defaults(), [
                    'type' => 'accordion',
                    'title' => 'Nội dung mở rộng',
                    'description' => '',
                    'items' => [
                        ['title' => 'Mục 1', 'content' => '<p>Nội dung mục 1.</p>', 'children' => []],
                        ['title' => 'Mục 2', 'content' => '<p>Nội dung mục 2.</p>', 'children' => []],
                    ],
                ]);
            case 'portfolio':
                return array_merge(self::common_defaults(), [
                    'type' => 'portfolio',
                    'title' => 'Dự án đã triển khai',
                    'description' => '',
                    'variant' => 'filterable_grid',
                    'filters' => ['Tất cả', 'Website', 'Mini App', 'Phần mềm'],
                    'items' => [
                        ['image' => '', 'title' => 'Dự án mẫu', 'category' => 'Website', 'description' => 'Mô tả dự án.', 'link' => '', 'modal' => self::default_component('modal')],
                    ],
                ]);
            default:
                return array_merge(self::common_defaults(), ['type' => $type]);
        }
    }
}
