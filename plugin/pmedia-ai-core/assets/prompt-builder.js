document.addEventListener('DOMContentLoaded', function () {
    var promptBox = document.getElementById('pmedia_generated_prompt');
    if (!promptBox) return;

    var advancedBlock = [
        '',
        'ADVANCED COMPONENT SYSTEM:',
        '- Có thể dùng component lồng nhau bằng field children.',
        '- Có thể dùng settings object hoặc field phẳng. Plugin hỗ trợ cả hai kiểu.',
        '- Không tự viết CSS/JS. Chỉ dùng type/field theo schema.',
        '',
        'COMPONENT TYPE NÂNG CAO:',
        'modal, gallery, slider, tabs, accordion, portfolio.',
        '',
        'NESTED RULES:',
        '- modal.children có thể chứa: gallery, slider, content, form/contact, cta, tabs, accordion.',
        '- tabs.items[].children có thể chứa: content, gallery, pricing, services, cta.',
        '- accordion.items[].children có thể chứa: content, gallery, form/contact, cta.',
        '- portfolio.items[].modal có thể chứa children để mở modal chi tiết dự án.',
        '',
        'SCHEMA NÂNG CAO MẪU:',
        '{',
        '  "type": "modal",',
        '  "id": "project-gallery-modal",',
        '  "button_text": "Xem thư viện ảnh",',
        '  "modal_title": "Hình ảnh dự án",',
        '  "modal_size": "large",',
        '  "children": [',
        '    {',
        '      "type": "gallery",',
        '      "variant": "grid",',
        '      "lightbox": true,',
        '      "items": [',
        '        {"image":"","title":"Ảnh 1","description":""}',
        '      ]',
        '    }',
        '  ]',
        '}',
        '',
        '{',
        '  "type": "portfolio",',
        '  "variant": "filterable_grid",',
        '  "filters": ["Tất cả", "Website", "Mini App", "Phần mềm"],',
        '  "items": [',
        '    {',
        '      "title": "Dự án mẫu",',
        '      "category": "Website",',
        '      "image": "",',
        '      "description": "",',
        '      "modal": {',
        '        "title": "Chi tiết dự án",',
        '        "children": [',
        '          {"type":"slider","variant":"cards","items":[{"image":"","title":"Slide 1","description":""}]},',
        '          {"type":"content","title":"Mô tả","content":"<p>Nội dung mô tả dự án.</p>"}',
        '        ]',
        '      }',
        '    }',
        '  ]',
        '}',
        '',
        'YÊU CẦU THÊM:',
        '- Với trang dự án/hình ảnh, ưu tiên dùng portfolio hoặc modal chứa gallery/slider.',
        '- Với trang dịch vụ dài, có thể dùng tabs hoặc accordion để chia nội dung.',
        '- Nếu dùng modal/gallery/slider, vẫn phải giữ JSON hợp lệ trong output {"pages": [...]}.',
    ].join('\n');

    function appendAdvanced() {
        if (!promptBox.value) return;
        if (promptBox.value.indexOf('ADVANCED COMPONENT SYSTEM:') !== -1) return;
        promptBox.value = promptBox.value + '\n' + advancedBlock;
    }

    ['pmedia_copy_prompt', 'pmedia_refresh_prompt'].forEach(function (id) {
        var button = document.getElementById(id);
        if (button) {
            button.addEventListener('click', function () {
                window.setTimeout(appendAdvanced, 0);
            });
        }
    });

    ['pmedia_prompt_brand','pmedia_prompt_service','pmedia_prompt_target','pmedia_prompt_tone','pmedia_prompt_summary','pmedia_prompt_phone','pmedia_prompt_email','pmedia_prompt_address','pmedia_prompt_sitemap'].forEach(function (id) {
        var input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', function () {
                window.setTimeout(appendAdvanced, 0);
            });
        }
    });

    window.setTimeout(appendAdvanced, 0);
});
