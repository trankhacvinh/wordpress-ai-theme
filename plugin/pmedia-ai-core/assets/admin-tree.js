(function () {
    'use strict';

    var cfg = window.PMEDIA_AI_BUILDER || {};
    var schema = cfg.schema || {};
    var defaults = cfg.defaults || {};

    function clone(value) { return JSON.parse(JSON.stringify(value)); }
    function encode(value) { return JSON.stringify(value, null, 2); }
    function parseJson(value, fallback) {
        try { var parsed = JSON.parse(value || ''); return parsed == null ? fallback : parsed; } catch (e) { return fallback; }
    }
    function esc(value) {
        return String(value == null ? '' : value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function pathKey(path) { return encodeURIComponent(JSON.stringify(path)); }
    function parsePathKey(value) { try { return JSON.parse(decodeURIComponent(value || '[]')); } catch (e) { return []; } }
    function toLines(value) { return Array.isArray(value) ? value.join('\n') : (value || ''); }
    function fromLines(value) { return String(value || '').split('\n').map(function (x) { return x.trim(); }).filter(Boolean); }

    function getAt(root, path) {
        var ref = root;
        for (var i = 0; i < path.length; i++) { if (ref == null) return null; ref = ref[path[i]]; }
        return ref;
    }
    function setAt(root, path, value) {
        if (!path.length) return false;
        var parent = getAt(root, path.slice(0, -1));
        if (parent == null) return false;
        parent[path[path.length - 1]] = value;
        return true;
    }
    function deleteAt(root, path) {
        if (!path.length) return false;
        var parent = getAt(root, path.slice(0, -1));
        var last = path[path.length - 1];
        if (Array.isArray(parent) && typeof last === 'number') { parent.splice(last, 1); return true; }
        if (parent && typeof parent === 'object') { delete parent[last]; return true; }
        return false;
    }
    function parentArrayInfo(root, path) {
        if (!path.length) return null;
        var parent = getAt(root, path.slice(0, -1));
        var index = path[path.length - 1];
        return Array.isArray(parent) && typeof index === 'number' ? { array: parent, index: index, parentPath: path.slice(0, -1) } : null;
    }
    function sameArrayPath(a, b) {
        if (a.length !== b.length) return false;
        for (var i = 0; i < a.length; i++) if (a[i] !== b[i]) return false;
        return true;
    }
    function isAncestorPath(parent, child) {
        if (!parent.length || parent.length >= child.length) return false;
        for (var i = 0; i < parent.length; i++) if (parent[i] !== child[i]) return false;
        return true;
    }
    function adjustPathAfterDelete(path, deletedPath) {
        var deletedParent = deletedPath.slice(0, -1);
        var deletedIndex = deletedPath[deletedPath.length - 1];
        var candidateParent = path.slice(0, deletedParent.length);
        if (sameArrayPath(candidateParent, deletedParent) && typeof deletedIndex === 'number') {
            var next = path.slice();
            var idx = deletedParent.length;
            if (typeof next[idx] === 'number' && next[idx] > deletedIndex) next[idx] = next[idx] - 1;
            return next;
        }
        return path.slice();
    }

    function componentType(node) {
        if (!node || typeof node !== 'object') return 'content';
        return node.type || (node.modal_title || node.children ? 'modal' : 'item');
    }
    function componentTitle(node, fallback) {
        if (!node || typeof node !== 'object') return fallback || 'Node';
        var settings = node.settings && typeof node.settings === 'object' ? node.settings : {};
        return node.title || settings.title || node.modal_title || settings.modal_title || node.label || node.name || node.category || fallback || componentType(node);
    }
    function defaultComponent(type) {
        var component = defaults[type] ? clone(defaults[type]) : { type: type };
        component.type = type;
        return component;
    }
    function optionsHtml(options, value) {
        var out = '';
        Object.keys(options || {}).forEach(function (key) { out += '<option value="' + esc(key) + '" ' + (String(value || '') === String(key) ? 'selected' : '') + '>' + esc(options[key]) + '</option>'; });
        return out;
    }
    function itemDefault(fields) {
        var item = {};
        Object.keys(fields || {}).forEach(function (key) {
            var type = fields[key].type || 'text';
            item[key] = type === 'lines' ? [] : (type === 'checkbox' ? false : (type === 'number' ? 0 : ''));
        });
        return item;
    }

    function walkNode(node, path, depth, rows, context) {
        if (!node || typeof node !== 'object') return;
        rows.push({ node: node, path: path, depth: depth, context: context || 'component' });

        if (Array.isArray(node.children)) {
            node.children.forEach(function (child, index) { walkNode(child, path.concat(['children', index]), depth + 1, rows, 'children'); });
        }

        if (Array.isArray(node.items)) {
            node.items.forEach(function (item, itemIndex) {
                if (!item || typeof item !== 'object') return;
                var hasNested = Array.isArray(item.children) || item.modal || item.children_json || item.modal_json;
                if (hasNested) rows.push({ node: item, path: path.concat(['items', itemIndex]), depth: depth + 1, context: 'item' });

                if (Array.isArray(item.children)) {
                    item.children.forEach(function (child, childIndex) { walkNode(child, path.concat(['items', itemIndex, 'children', childIndex]), depth + 2, rows, 'item_children'); });
                }

                var modal = null;
                if (item.modal && typeof item.modal === 'object') modal = item.modal;
                else if (item.modal_json) { modal = parseJson(item.modal_json, null); if (modal) item.modal = modal; }
                if (modal) {
                    rows.push({ node: modal, path: path.concat(['items', itemIndex, 'modal']), depth: depth + 2, context: 'modal' });
                    if (Array.isArray(modal.children)) {
                        modal.children.forEach(function (child, childIndex) { walkNode(child, path.concat(['items', itemIndex, 'modal', 'children', childIndex]), depth + 3, rows, 'modal_children'); });
                    }
                }
            });
        }
    }
    function collectRows(sections) {
        var rows = [];
        sections.forEach(function (section, index) { walkNode(section, [index], 0, rows, 'root'); });
        return rows;
    }

    function addChildToPath(root, path, component) {
        var node = getAt(root, path);
        if (!node || typeof node !== 'object') return null;

        var parentPath = path.slice(0, -2);
        var possibleParent = getAt(root, parentPath);
        if (path.length >= 2 && path[path.length - 2] === 'items' && possibleParent && possibleParent.type === 'portfolio') {
            if (!node.modal || typeof node.modal !== 'object') node.modal = { title: node.title || 'Chi tiết', children: [] };
            if (!Array.isArray(node.modal.children)) node.modal.children = [];
            node.modal.children.push(component);
            return path.concat(['modal', 'children', node.modal.children.length - 1]);
        }

        if (!Array.isArray(node.children)) node.children = [];
        node.children.push(component);
        return path.concat(['children', node.children.length - 1]);
    }

    function insertNearPath(root, targetPath, component, mode) {
        var info = parentArrayInfo(root, targetPath);
        if (!info) return addChildToPath(root, targetPath, component);
        var insertIndex = mode === 'after' ? info.index + 1 : info.index;
        info.array.splice(insertIndex, 0, component);
        return info.parentPath.concat([insertIndex]);
    }

    function moveNode(root, sourcePath, targetPath, mode) {
        if (!sourcePath.length || !targetPath.length || JSON.stringify(sourcePath) === JSON.stringify(targetPath)) return null;
        if (isAncestorPath(sourcePath, targetPath)) return null;

        var moving = clone(getAt(root, sourcePath));
        if (!moving) return null;
        deleteAt(root, sourcePath);
        var adjustedTarget = adjustPathAfterDelete(targetPath, sourcePath);

        if (mode === 'before' || mode === 'after') return insertNearPath(root, adjustedTarget, moving, mode);
        return addChildToPath(root, adjustedTarget, moving);
    }

    function openMediaPicker(callback) {
        if (!window.wp || !wp.media) return;
        var frame = wp.media({ title: 'Chọn ảnh', button: { text: 'Dùng ảnh này' }, multiple: false });
        frame.on('select', function () { var a = frame.state().get('selection').first().toJSON(); if (a && a.url) callback(a.url); });
        frame.open();
    }

    function initTreeBuilder(builder) {
        var hidden = builder.querySelector('.pmedia-ai-sections-json');
        var jsonEditor = builder.querySelector('.pmedia-ai-json-editor');
        var panel = builder.querySelector('.pmedia-ai-tree-panel');
        var list = builder.querySelector('.pmedia-ai-tree-list');
        var nodeEditor = builder.querySelector('.pmedia-ai-tree-node-json');
        var pathInput = builder.querySelector('.pmedia-ai-tree-path');
        var addType = builder.querySelector('.pmedia-ai-tree-add-type');
        var selectedPath = null;
        var draggedPath = null;
        var sections = [];
        var formWrap = builder.querySelector('.pmedia-ai-tree-node-form');

        if (!formWrap && nodeEditor) {
            formWrap = document.createElement('div');
            formWrap.className = 'pmedia-ai-tree-node-form';
            nodeEditor.parentNode.insertBefore(formWrap, nodeEditor);
        }

        function readSections() {
            sections = parseJson(hidden ? hidden.value : '[]', []);
            if (!Array.isArray(sections)) sections = [];
        }
        function writeSections(refreshForm) {
            var json = encode(sections);
            if (hidden) hidden.value = json;
            if (jsonEditor) jsonEditor.value = json;
            if (refreshForm) {
                var apply = builder.querySelector('.pmedia-ai-apply-json');
                if (apply) apply.click();
            }
        }
        function syncSelectedNodeToJson() {
            var node = selectedPath ? getAt(sections, selectedPath) : null;
            if (pathInput) pathInput.value = selectedPath ? JSON.stringify(selectedPath) : '';
            if (nodeEditor) nodeEditor.value = node ? encode(node) : '';
        }
        function updateSelectedTreeLabel() {
            var node = selectedPath ? getAt(sections, selectedPath) : null;
            var selected = list ? list.querySelector('.pmedia-ai-tree-node.is-selected') : null;
            if (node && selected) {
                var title = selected.querySelector('.pmedia-ai-tree-node-title');
                var type = selected.querySelector('.pmedia-ai-tree-node-type');
                if (title) title.textContent = componentTitle(node, 'Node');
                if (type) type.textContent = componentType(node);
            }
        }

        function renderTree() {
            readSections();
            var rows = collectRows(sections);
            if (!selectedPath && rows.length) selectedPath = rows[0].path;
            var selectedKey = selectedPath ? JSON.stringify(selectedPath) : '';
            list.innerHTML = rows.map(function (row) {
                var selected = JSON.stringify(row.path) === selectedKey;
                return '<button type="button" draggable="true" class="pmedia-ai-tree-node ' + (selected ? 'is-selected' : '') + '" data-path="' + pathKey(row.path) + '" style="--tree-depth:' + row.depth + '">' +
                    '<span class="pmedia-ai-tree-node-type">' + esc(componentType(row.node)) + '</span>' +
                    '<span class="pmedia-ai-tree-node-title">' + esc(componentTitle(row.node, 'Node')) + '</span>' +
                    '<span class="pmedia-ai-tree-node-context">' + esc(row.context) + '</span>' +
                    '<span class="pmedia-ai-tree-drop-hint">Drop: before / child / after</span>' +
                    '</button>';
            }).join('');
            syncSelectedNodeToJson();
            renderNodeForm();
        }

        function renderScalarField(key, field, value, prefix) {
            var type = field.type || 'text';
            var data = 'data-key="' + esc(key) + '" data-type="' + esc(type) + '"' + (prefix ? ' data-prefix="' + esc(prefix) + '"' : '');
            var html = '<div class="pmedia-ai-tree-form-row"><label>' + esc(field.label || key) + '</label>';
            if (type === 'textarea' || type === 'json' || type === 'lines') {
                html += '<textarea rows="' + (type === 'json' ? '6' : '3') + '" class="widefat pmedia-ai-tree-field" ' + data + '>' + esc(type === 'lines' ? toLines(value) : (type === 'json' && typeof value !== 'string' ? encode(value || {}) : (value || ''))) + '</textarea>';
            } else if (type === 'select') {
                html += '<select class="widefat pmedia-ai-tree-field" ' + data + '>' + optionsHtml(field.options || {}, value) + '</select>';
            } else if (type === 'checkbox') {
                html += '<label class="pmedia-ai-inline-check"><input type="checkbox" class="pmedia-ai-tree-field" ' + data + ' ' + (value ? 'checked' : '') + '> Bật</label>';
            } else if (type === 'image') {
                html += '<div class="pmedia-ai-image-line"><input type="text" class="widefat pmedia-ai-tree-field" ' + data + ' value="' + esc(value || '') + '"><button type="button" class="button pmedia-ai-tree-pick-image" data-key="' + esc(key) + '"' + (prefix ? ' data-prefix="' + esc(prefix) + '"' : '') + '>Chọn ảnh</button></div>';
            } else {
                html += '<input type="' + (type === 'number' ? 'number' : 'text') + '" class="widefat pmedia-ai-tree-field" ' + data + ' value="' + esc(value == null ? '' : value) + '">';
            }
            return html + '</div>';
        }

        function renderRepeater(key, field, value) {
            var items = Array.isArray(value) ? value : [];
            var html = '<div class="pmedia-ai-tree-repeater" data-key="' + esc(key) + '"><div class="pmedia-ai-tree-repeater-head"><strong>' + esc(field.label || key) + '</strong><button type="button" class="button button-small pmedia-ai-tree-add-repeater-item" data-key="' + esc(key) + '">Thêm mục</button></div>';
            items.forEach(function (item, index) {
                html += '<div class="pmedia-ai-tree-repeater-item"><div class="pmedia-ai-tree-repeater-item-head"><span>Mục ' + (index + 1) + '</span><button type="button" class="button button-small pmedia-ai-tree-delete-repeater-item" data-key="' + esc(key) + '" data-index="' + index + '">Xóa</button></div>';
                Object.keys(field.item_fields || {}).forEach(function (itemKey) {
                    var itemField = field.item_fields[itemKey];
                    var value = item && item[itemKey] != null ? item[itemKey] : (itemField.type === 'lines' ? [] : (itemField.type === 'checkbox' ? false : ''));
                    html += renderScalarField(itemKey, itemField, value, key + '.' + index);
                });
                html += '</div>';
            });
            return html + '</div>';
        }

        function genericSchemaForNode(node) {
            var out = {};
            Object.keys(node || {}).forEach(function (key) {
                if (key === 'children' || key === 'modal') return;
                var value = node[key];
                if (Array.isArray(value)) out[key] = { label: key, type: key === 'filters' ? 'lines' : 'json' };
                else if (value && typeof value === 'object') out[key] = { label: key, type: 'json' };
                else if (typeof value === 'boolean') out[key] = { label: key, type: 'checkbox' };
                else if (typeof value === 'number') out[key] = { label: key, type: 'number' };
                else if (key === 'description' || key === 'content') out[key] = { label: key, type: 'textarea' };
                else if (key === 'image') out[key] = { label: key, type: 'image' };
                else out[key] = { label: key, type: 'text' };
            });
            return out;
        }

        function renderNodeForm() {
            if (!formWrap) return;
            var node = selectedPath ? getAt(sections, selectedPath) : null;
            if (!node || typeof node !== 'object') {
                formWrap.innerHTML = '<p class="description">Chọn một node trong cây để sửa bằng form.</p>';
                return;
            }
            var type = componentType(node);
            var fields = schema[type] && schema[type].fields ? schema[type].fields : genericSchemaForNode(node);
            var html = '<div class="pmedia-ai-tree-form-head"><strong>Form editor: ' + esc(type) + '</strong><span class="description">Sửa nhanh field phổ biến. JSON node bên dưới vẫn dùng cho cấu trúc sâu.</span></div>';
            Object.keys(fields).forEach(function (key) {
                var field = fields[key];
                if (field.type === 'repeater') html += renderRepeater(key, field, node[key]);
                else html += renderScalarField(key, field, node[key]);
            });
            formWrap.innerHTML = html;
        }

        function fieldValue(input, type) {
            if (type === 'checkbox') return input.checked;
            if (type === 'number') return parseInt(input.value || '0', 10);
            if (type === 'lines') return fromLines(input.value);
            if (type === 'json') return parseJson(input.value, input.value || '');
            return input.value;
        }

        function updateNodeFromForm(input) {
            var node = selectedPath ? getAt(sections, selectedPath) : null;
            if (!node || typeof node !== 'object') return;
            var key = input.getAttribute('data-key');
            var prefix = input.getAttribute('data-prefix');
            var type = input.getAttribute('data-type') || 'text';
            var value = fieldValue(input, type);

            if (prefix) {
                var parts = prefix.split('.');
                var repeaterKey = parts[0];
                var index = parseInt(parts[1] || '0', 10);
                if (!Array.isArray(node[repeaterKey])) node[repeaterKey] = [];
                if (!node[repeaterKey][index]) node[repeaterKey][index] = {};
                node[repeaterKey][index][key] = value;
            } else {
                node[key] = value;
            }

            writeSections(true);
            syncSelectedNodeToJson();
            updateSelectedTreeLabel();
        }

        builder.addEventListener('click', function (event) {
            var toggle = event.target.closest('.pmedia-ai-toggle-tree');
            if (toggle) { if (!panel) return; panel.hidden = !panel.hidden; if (!panel.hidden) renderTree(); return; }

            var nodeButton = event.target.closest('.pmedia-ai-tree-node');
            if (nodeButton && builder.contains(nodeButton)) { selectedPath = parsePathKey(nodeButton.getAttribute('data-path')); renderTree(); return; }

            if (event.target.closest('.pmedia-ai-tree-add-root')) {
                readSections();
                sections.push(defaultComponent(addType ? addType.value : 'content'));
                selectedPath = [sections.length - 1];
                writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-add-child')) {
                readSections();
                if (!selectedPath) { window.alert('Chọn một node trước khi thêm child.'); return; }
                selectedPath = addChildToPath(sections, selectedPath, defaultComponent(addType ? addType.value : 'content')) || selectedPath;
                writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-apply-node')) {
                if (!selectedPath) return;
                var parsed = parseJson(nodeEditor ? nodeEditor.value : '', null);
                if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) { window.alert('Node JSON không hợp lệ.'); return; }
                readSections(); setAt(sections, selectedPath, parsed); writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-delete-node')) {
                if (!selectedPath || !window.confirm('Xóa node này?')) return;
                readSections(); deleteAt(sections, selectedPath); selectedPath = null; writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-duplicate-node')) {
                if (!selectedPath) return;
                readSections();
                var info = parentArrayInfo(sections, selectedPath);
                if (!info) { window.alert('Node này không thể nhân bản tại vị trí hiện tại.'); return; }
                info.array.splice(info.index + 1, 0, clone(info.array[info.index]));
                selectedPath = selectedPath.slice(0, -1).concat([info.index + 1]);
                writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-move-up') || event.target.closest('.pmedia-ai-tree-move-down')) {
                if (!selectedPath) return;
                readSections();
                var moveInfo = parentArrayInfo(sections, selectedPath);
                if (!moveInfo) { window.alert('Node này không thể di chuyển tại vị trí hiện tại.'); return; }
                var dir = event.target.closest('.pmedia-ai-tree-move-up') ? -1 : 1;
                var nextIndex = moveInfo.index + dir;
                if (nextIndex < 0 || nextIndex >= moveInfo.array.length) return;
                var temp = moveInfo.array[moveInfo.index];
                moveInfo.array[moveInfo.index] = moveInfo.array[nextIndex];
                moveInfo.array[nextIndex] = temp;
                selectedPath = selectedPath.slice(0, -1).concat([nextIndex]);
                writeSections(true); renderTree(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-add-repeater-item')) {
                var node = selectedPath ? getAt(sections, selectedPath) : null;
                var key = event.target.closest('.pmedia-ai-tree-add-repeater-item').getAttribute('data-key');
                var type = node ? componentType(node) : '';
                var field = schema[type] && schema[type].fields ? schema[type].fields[key] : null;
                if (!node || !field) return;
                if (!Array.isArray(node[key])) node[key] = [];
                node[key].push(itemDefault(field.item_fields || {}));
                writeSections(true); renderNodeForm(); syncSelectedNodeToJson(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-delete-repeater-item')) {
                var del = event.target.closest('.pmedia-ai-tree-delete-repeater-item');
                var delNode = selectedPath ? getAt(sections, selectedPath) : null;
                var delKey = del.getAttribute('data-key');
                var delIndex = parseInt(del.getAttribute('data-index') || '0', 10);
                if (delNode && Array.isArray(delNode[delKey])) delNode[delKey].splice(delIndex, 1);
                writeSections(true); renderNodeForm(); syncSelectedNodeToJson(); return;
            }

            if (event.target.closest('.pmedia-ai-tree-pick-image')) {
                var button = event.target.closest('.pmedia-ai-tree-pick-image');
                openMediaPicker(function (url) {
                    var input = button.parentNode.querySelector('.pmedia-ai-tree-field');
                    if (input) { input.value = url; updateNodeFromForm(input); }
                });
            }
        });

        builder.addEventListener('input', function (event) {
            if (event.target.classList.contains('pmedia-ai-tree-field')) updateNodeFromForm(event.target);
        });
        builder.addEventListener('change', function (event) {
            if (event.target.classList.contains('pmedia-ai-tree-field')) updateNodeFromForm(event.target);
            if (event.target.classList.contains('pmedia-ai-json-editor') && panel && !panel.hidden) renderTree();
        });

        builder.addEventListener('dragstart', function (event) {
            var node = event.target.closest('.pmedia-ai-tree-node');
            if (!node) return;
            draggedPath = parsePathKey(node.getAttribute('data-path'));
            event.dataTransfer.setData('text/plain', pathKey(draggedPath));
            event.dataTransfer.effectAllowed = 'move';
            node.classList.add('is-dragging');
        });
        builder.addEventListener('dragend', function () {
            draggedPath = null;
            builder.querySelectorAll('.pmedia-ai-tree-node').forEach(function (node) { node.classList.remove('is-dragging', 'is-drop-before', 'is-drop-after', 'is-drop-inside'); });
        });
        builder.addEventListener('dragover', function (event) {
            var node = event.target.closest('.pmedia-ai-tree-node');
            if (!node || !draggedPath) return;
            event.preventDefault();
            var rect = node.getBoundingClientRect();
            var ratio = (event.clientY - rect.top) / Math.max(rect.height, 1);
            var mode = ratio < 0.25 ? 'before' : (ratio > 0.75 ? 'after' : 'inside');
            builder.querySelectorAll('.pmedia-ai-tree-node').forEach(function (n) { n.classList.remove('is-drop-before', 'is-drop-after', 'is-drop-inside'); });
            node.classList.add('is-drop-' + mode);
            node.setAttribute('data-drop-mode', mode);
        });
        builder.addEventListener('drop', function (event) {
            var node = event.target.closest('.pmedia-ai-tree-node');
            if (!node) return;
            event.preventDefault();
            readSections();
            var source = draggedPath || parsePathKey(event.dataTransfer.getData('text/plain'));
            var target = parsePathKey(node.getAttribute('data-path'));
            var mode = node.getAttribute('data-drop-mode') || 'inside';
            var nextPath = moveNode(sections, source, target, mode);
            if (!nextPath) { window.alert('Không thể thả node vào vị trí này.'); return; }
            selectedPath = nextPath;
            writeSections(true);
            renderTree();
        });

        if (jsonEditor) jsonEditor.addEventListener('change', function () { if (panel && !panel.hidden) renderTree(); });
    }

    document.addEventListener('DOMContentLoaded', function () { document.querySelectorAll('.pmedia-ai-builder').forEach(initTreeBuilder); });
})();
