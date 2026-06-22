(function ($) {
    'use strict';

    var cfg = window.PMEDIA_AI_BUILDER || {};
    var schema = cfg.schema || {};
    var defaults = cfg.defaults || {};

    function copy(obj) { return JSON.parse(JSON.stringify(obj)); }
    function html(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
    function parse(v) { try { var x = JSON.parse(v || '[]'); return Array.isArray(x) ? x : []; } catch (e) { return []; } }
    function stringify(v) { return JSON.stringify(v, null, 2); }
    function toLines(v) { return Array.isArray(v) ? v.join('\n') : (v || ''); }
    function fromLines(v) { return String(v || '').split('\n').map(function (x) { return $.trim(x); }).filter(Boolean); }

    function itemDefault(fields) {
        var item = {};
        $.each(fields || {}, function (k, f) {
            if (f.type === 'lines') item[k] = [];
            else if (f.type === 'checkbox') item[k] = false;
            else if (f.type === 'number') item[k] = 0;
            else item[k] = '';
        });
        return item;
    }

    function label(section, index) {
        var type = section.type || 'content';
        var typeName = schema[type] && schema[type].label ? schema[type].label : type;
        var settings = section.settings && typeof section.settings === 'object' ? section.settings : {};
        var title = section.title || settings.title || section.name || settings.name || section.modal_title || settings.modal_title || '';
        return (index + 1) + '. ' + typeName + (title ? ' — ' + title : '');
    }

    function normalizeInputValue($input, type) {
        if (type === 'checkbox') return $input.is(':checked');
        if (type === 'number') return parseInt($input.val() || '0', 10);
        if (type === 'lines') return fromLines($input.val());
        return $input.val();
    }

    function optionsHtml(options, value) {
        var out = '';
        $.each(options || {}, function (k, label) {
            out += '<option value="' + html(k) + '" ' + (String(value || '') === String(k) ? 'selected' : '') + '>' + html(label) + '</option>';
        });
        return out;
    }

    function fieldHtml(sectionIndex, key, field, value) {
        var type = field.type || 'text';
        var out = '<div class="pmedia-ai-field-row"><label>' + html(field.label || key) + '</label>';
        if (type === 'textarea' || type === 'json' || type === 'lines') {
            out += '<textarea rows="' + (type === 'json' ? '7' : '3') + '" class="widefat pmedia-ai-field" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-t="' + html(type) + '">' + html(type === 'lines' ? toLines(value) : (value || '')) + '</textarea>';
        } else if (type === 'select') {
            out += '<select class="widefat pmedia-ai-field" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-t="select">' + optionsHtml(field.options || {}, value) + '</select>';
        } else if (type === 'checkbox') {
            out += '<label class="pmedia-ai-inline-check"><input type="checkbox" class="pmedia-ai-field" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-t="checkbox" ' + (value ? 'checked' : '') + '> Bật</label>';
        } else if (type === 'image') {
            out += '<div class="pmedia-ai-image-line"><input type="text" class="widefat pmedia-ai-field" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-t="image" value="' + html(value || '') + '"><button type="button" class="button pmedia-ai-pick-image" data-i="' + sectionIndex + '" data-k="' + html(key) + '">Chọn ảnh</button></div>';
        } else {
            out += '<input type="' + (type === 'number' ? 'number' : 'text') + '" class="widefat pmedia-ai-field" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-t="' + html(type) + '" value="' + html(value == null ? '' : value) + '">';
        }
        if (type === 'json') out += '<p class="description">Dùng JSON hợp lệ cho cấu trúc lồng nhau. Ví dụ: [{"type":"gallery","items":[]}]</p>';
        return out + '</div>';
    }

    function repeaterInputHtml(sectionIndex, key, itemIndex, itemKey, itemField, value) {
        var t = itemField.type || 'text';
        var attrs = 'data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-ii="' + itemIndex + '" data-ik="' + html(itemKey) + '" data-t="' + html(t) + '"';
        var out = '<div class="pmedia-ai-field-row"><label>' + html(itemField.label || itemKey) + '</label>';
        if (t === 'textarea' || t === 'lines' || t === 'json') {
            out += '<textarea rows="' + (t === 'json' ? '7' : '3') + '" class="widefat pmedia-ai-item-field" ' + attrs + '>' + html(t === 'lines' ? toLines(value) : (value || '')) + '</textarea>';
        } else if (t === 'select') {
            out += '<select class="widefat pmedia-ai-item-field" ' + attrs + '>' + optionsHtml(itemField.options || {}, value) + '</select>';
        } else if (t === 'checkbox') {
            out += '<label class="pmedia-ai-inline-check"><input type="checkbox" class="pmedia-ai-item-field" ' + attrs + ' ' + (value ? 'checked' : '') + '> Bật</label>';
        } else if (t === 'image') {
            out += '<div class="pmedia-ai-image-line"><input type="text" class="widefat pmedia-ai-item-field" ' + attrs + ' value="' + html(value || '') + '"><button type="button" class="button pmedia-ai-pick-item-image" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-ii="' + itemIndex + '" data-ik="' + html(itemKey) + '">Chọn ảnh</button></div>';
        } else {
            out += '<input type="' + (t === 'number' ? 'number' : 'text') + '" class="widefat pmedia-ai-item-field" ' + attrs + ' value="' + html(value == null ? '' : value) + '">';
        }
        if (t === 'json') out += '<p class="description">Dùng JSON hợp lệ cho modal/children nâng cao.</p>';
        return out + '</div>';
    }

    function repeaterHtml(sectionIndex, key, field, value) {
        var items = Array.isArray(value) ? value : [];
        var out = '<div class="pmedia-ai-repeater"><div class="pmedia-ai-repeater-head"><strong>' + html(field.label || key) + '</strong> <button type="button" class="button button-small pmedia-ai-add-item" data-i="' + sectionIndex + '" data-k="' + html(key) + '">Thêm mục</button></div>';
        $.each(items, function (itemIndex, item) {
            out += '<div class="pmedia-ai-repeater-item"><div class="pmedia-ai-repeater-item-head"><span>Mục ' + (itemIndex + 1) + '</span><button type="button" class="button button-small pmedia-ai-del-item" data-i="' + sectionIndex + '" data-k="' + html(key) + '" data-ii="' + itemIndex + '">Xóa</button></div>';
            $.each(field.item_fields || {}, function (itemKey, itemField) {
                var t = itemField.type || 'text';
                var val = item && item[itemKey] != null ? item[itemKey] : (t === 'lines' ? [] : (t === 'checkbox' ? false : ''));
                out += repeaterInputHtml(sectionIndex, key, itemIndex, itemKey, itemField, val);
            });
            out += '</div>';
        });
        return out + '</div>';
    }

    function init($box) {
        var $hidden = $box.find('.pmedia-ai-sections-json');
        var $editor = $box.find('.pmedia-ai-json-editor');
        var $list = $box.find('.pmedia-ai-builder-list');
        var sections = parse($hidden.val());

        function sync() {
            var value = stringify(sections);
            $hidden.val(value);
            $editor.val(value);
        }

        function render() {
            $list.empty();
            $.each(sections, function (index, section) {
                var type = section.type || 'content';
                var fields = schema[type] && schema[type].fields ? schema[type].fields : {};
                var out = '<div class="pmedia-ai-section" data-i="' + index + '"><div class="pmedia-ai-section-head"><span class="pmedia-ai-drag">☰</span><strong class="pmedia-ai-section-title">' + html(label(section, index)) + '</strong><span class="pmedia-ai-section-actions"><button type="button" class="button button-small pmedia-ai-fold">Thu gọn</button> <button type="button" class="button button-small pmedia-ai-copy" data-i="' + index + '">Nhân bản</button> <button type="button" class="button button-small pmedia-ai-del" data-i="' + index + '">Xóa</button></span></div><div class="pmedia-ai-section-body">';
                $.each(fields, function (key, field) {
                    out += field.type === 'repeater' ? repeaterHtml(index, key, field, section[key]) : fieldHtml(index, key, field, section[key]);
                });
                $list.append(out + '</div></div>');
            });
            try { $list.sortable('destroy'); } catch (e) {}
            $list.sortable({ handle: '.pmedia-ai-drag', update: reorder });
            sync();
        }

        function reorder() {
            var next = [];
            $list.find('.pmedia-ai-section').each(function () {
                var oldIndex = parseInt($(this).attr('data-i'), 10);
                if (sections[oldIndex]) next.push(sections[oldIndex]);
            });
            sections = next;
            render();
        }

        $box.on('click', '.pmedia-ai-add-section', function () {
            var type = $box.find('.pmedia-ai-section-type').val() || 'content';
            var section = defaults[type] ? copy(defaults[type]) : { type: type };
            section.type = type;
            sections.push(section);
            render();
        });

        $box.on('click', '.pmedia-ai-del', function () {
            if (!window.confirm('Xóa section này?')) return;
            sections.splice(parseInt($(this).attr('data-i'), 10), 1);
            render();
        });

        $box.on('click', '.pmedia-ai-copy', function () {
            var i = parseInt($(this).attr('data-i'), 10);
            if (sections[i]) { sections.splice(i + 1, 0, copy(sections[i])); render(); }
        });

        $box.on('click', '.pmedia-ai-fold', function () { $(this).closest('.pmedia-ai-section').toggleClass('is-collapsed'); });
        $box.on('click', '.pmedia-ai-expand-all', function () { $box.find('.pmedia-ai-section').removeClass('is-collapsed'); });
        $box.on('click', '.pmedia-ai-collapse-all', function () { $box.find('.pmedia-ai-section').addClass('is-collapsed'); });
        $box.on('click', '.pmedia-ai-toggle-json', function () { $box.find('.pmedia-ai-json-panel').prop('hidden', function (_, v) { return !v; }); });

        $box.on('input change', '.pmedia-ai-field', function () {
            var $field = $(this);
            var i = parseInt($field.attr('data-i'), 10);
            var k = $field.attr('data-k');
            var t = $field.attr('data-t') || 'text';
            if (sections[i]) {
                sections[i][k] = normalizeInputValue($field, t);
                sync();
                $box.find('.pmedia-ai-section[data-i="' + i + '"] .pmedia-ai-section-title').text(label(sections[i], i));
            }
        });

        $box.on('input change', '.pmedia-ai-item-field', function () {
            var $field = $(this);
            var i = parseInt($field.attr('data-i'), 10), k = $field.attr('data-k'), ii = parseInt($field.attr('data-ii'), 10), ik = $field.attr('data-ik'), t = $field.attr('data-t') || 'text';
            if (sections[i] && Array.isArray(sections[i][k]) && sections[i][k][ii]) {
                sections[i][k][ii][ik] = normalizeInputValue($field, t);
                sync();
            }
        });

        $box.on('click', '.pmedia-ai-add-item', function () {
            var i = parseInt($(this).attr('data-i'), 10), k = $(this).attr('data-k'), type = sections[i] ? sections[i].type : '', field = schema[type] && schema[type].fields ? schema[type].fields[k] : null;
            if (!field) return;
            if (!Array.isArray(sections[i][k])) sections[i][k] = [];
            sections[i][k].push(itemDefault(field.item_fields || {}));
            render();
        });

        $box.on('click', '.pmedia-ai-del-item', function () {
            var i = parseInt($(this).attr('data-i'), 10), k = $(this).attr('data-k'), ii = parseInt($(this).attr('data-ii'), 10);
            if (sections[i] && Array.isArray(sections[i][k])) { sections[i][k].splice(ii, 1); render(); }
        });

        $box.on('click', '.pmedia-ai-apply-json', function () {
            try { var parsed = JSON.parse($editor.val() || '[]'); if (!Array.isArray(parsed)) throw new Error('Invalid'); sections = parsed; render(); } catch (e) { window.alert('JSON section không hợp lệ.'); }
        });

        function openMediaPicker(callback) {
            var frame = wp.media({ title: 'Chọn ảnh', button: { text: 'Dùng ảnh này' }, multiple: false });
            frame.on('select', function () { var a = frame.state().get('selection').first().toJSON(); if (a && a.url) callback(a.url); });
            frame.open();
        }

        $box.on('click', '.pmedia-ai-pick-image', function () {
            var i = parseInt($(this).attr('data-i'), 10), k = $(this).attr('data-k');
            openMediaPicker(function (url) { if (sections[i]) { sections[i][k] = url; render(); } });
        });

        $box.on('click', '.pmedia-ai-pick-item-image', function () {
            var i = parseInt($(this).attr('data-i'), 10), k = $(this).attr('data-k'), ii = parseInt($(this).attr('data-ii'), 10), ik = $(this).attr('data-ik');
            openMediaPicker(function (url) { if (sections[i] && Array.isArray(sections[i][k]) && sections[i][k][ii]) { sections[i][k][ii][ik] = url; render(); } });
        });

        $box.closest('form').on('submit', sync);
        render();
    }

    $(function () { $('.pmedia-ai-builder').each(function () { init($(this)); }); });
})(jQuery);
