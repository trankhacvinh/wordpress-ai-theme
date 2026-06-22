(function () {
    'use strict';

    var cfg = window.PMEDIA_AI_BUILDER || {};
    var schema = cfg.schema || {};
    var defaults = cfg.defaults || {};

    function clone(value) {
        return JSON.parse(JSON.stringify(value));
    }

    function parseJson(value, fallback) {
        try {
            var parsed = JSON.parse(value || '');
            return parsed == null ? fallback : parsed;
        } catch (error) {
            return fallback;
        }
    }

    function encode(value) {
        return JSON.stringify(value, null, 2);
    }

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pathKey(path) {
        return encodeURIComponent(JSON.stringify(path));
    }

    function parsePathKey(value) {
        try {
            return JSON.parse(decodeURIComponent(value || '[]'));
        } catch (error) {
            return [];
        }
    }

    function getAt(root, path) {
        var ref = root;
        for (var i = 0; i < path.length; i++) {
            if (ref == null) return null;
            ref = ref[path[i]];
        }
        return ref;
    }

    function setAt(root, path, value) {
        if (!path.length) return;
        var parent = getAt(root, path.slice(0, -1));
        if (parent == null) return;
        parent[path[path.length - 1]] = value;
    }

    function deleteAt(root, path) {
        if (!path.length) return false;
        var parent = getAt(root, path.slice(0, -1));
        var last = path[path.length - 1];
        if (Array.isArray(parent) && typeof last === 'number') {
            parent.splice(last, 1);
            return true;
        }
        if (parent && typeof parent === 'object') {
            delete parent[last];
            return true;
        }
        return false;
    }

    function parentArrayInfo(root, path) {
        if (!path.length) return null;
        var parent = getAt(root, path.slice(0, -1));
        var index = path[path.length - 1];
        if (Array.isArray(parent) && typeof index === 'number') {
            return { array: parent, index: index };
        }
        return null;
    }

    function componentType(node) {
        if (!node || typeof node !== 'object') return 'content';
        return node.type || (node.modal_title || node.children ? 'modal' : 'item');
    }

    function componentTitle(node, fallback) {
        if (!node || typeof node !== 'object') return fallback || 'Node';
        var settings = node.settings && typeof node.settings === 'object' ? node.settings : {};
        return node.title || settings.title || node.modal_title || settings.modal_title || node.label || node.name || fallback || componentType(node);
    }

    function componentLabel(node, path) {
        var type = componentType(node);
        var typeLabel = schema[type] && schema[type].label ? schema[type].label : type;
        return typeLabel + ' — ' + componentTitle(node, typeLabel);
    }

    function defaultComponent(type) {
        var component = defaults[type] ? clone(defaults[type]) : { type: type };
        component.type = type;
        return component;
    }

    function walkNode(root, node, path, depth, rows, context) {
        if (!node || typeof node !== 'object') return;

        rows.push({ node: node, path: path, depth: depth, context: context || 'component' });

        if (Array.isArray(node.children)) {
            node.children.forEach(function (child, index) {
                walkNode(root, child, path.concat(['children', index]), depth + 1, rows, 'children');
            });
        }

        if (Array.isArray(node.items)) {
            node.items.forEach(function (item, itemIndex) {
                if (!item || typeof item !== 'object') return;

                var itemHasNested = Array.isArray(item.children) || item.modal || item.children_json || item.modal_json;
                if (itemHasNested) {
                    rows.push({
                        node: item,
                        path: path.concat(['items', itemIndex]),
                        depth: depth + 1,
                        context: 'item'
                    });
                }

                if (Array.isArray(item.children)) {
                    item.children.forEach(function (child, childIndex) {
                        walkNode(root, child, path.concat(['items', itemIndex, 'children', childIndex]), depth + 2, rows, 'item_children');
                    });
                }

                var modal = null;
                if (item.modal && typeof item.modal === 'object') {
                    modal = item.modal;
                } else if (item.modal_json) {
                    modal = parseJson(item.modal_json, null);
                    if (modal) item.modal = modal;
                }

                if (modal) {
                    rows.push({ node: modal, path: path.concat(['items', itemIndex, 'modal']), depth: depth + 2, context: 'modal' });
                    if (Array.isArray(modal.children)) {
                        modal.children.forEach(function (child, childIndex) {
                            walkNode(root, child, path.concat(['items', itemIndex, 'modal', 'children', childIndex]), depth + 3, rows, 'modal_children');
                        });
                    }
                }
            });
        }
    }

    function collectRows(sections) {
        var rows = [];
        sections.forEach(function (section, index) {
            walkNode(sections, section, [index], 0, rows, 'root');
        });
        return rows;
    }

    function canUseChildren(node) {
        return node && typeof node === 'object';
    }

    function addChildToNode(root, path, type) {
        var node = getAt(root, path);
        if (!node || typeof node !== 'object') return false;
        var component = defaultComponent(type);

        var last = path[path.length - 1];
        var parentPath = path.slice(0, -2);
        var possibleParent = getAt(root, parentPath);

        if (path.length >= 2 && path[path.length - 2] === 'items' && possibleParent && possibleParent.type === 'portfolio') {
            if (!node.modal || typeof node.modal !== 'object') {
                node.modal = { title: node.title || 'Chi tiết', children: [] };
            }
            if (!Array.isArray(node.modal.children)) node.modal.children = [];
            node.modal.children.push(component);
            return true;
        }

        if (!Array.isArray(node.children)) node.children = [];
        node.children.push(component);
        return true;
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
        var sections = [];

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

        function renderTree() {
            readSections();
            var rows = collectRows(sections);
            if (!selectedPath && rows.length) selectedPath = rows[0].path;
            var selectedKey = selectedPath ? JSON.stringify(selectedPath) : '';

            list.innerHTML = rows.map(function (row) {
                var isSelected = JSON.stringify(row.path) === selectedKey;
                return '<button type="button" class="pmedia-ai-tree-node ' + (isSelected ? 'is-selected' : '') + '" data-path="' + pathKey(row.path) + '" style="--tree-depth:' + row.depth + '">' +
                    '<span class="pmedia-ai-tree-node-type">' + esc(componentType(row.node)) + '</span>' +
                    '<span class="pmedia-ai-tree-node-title">' + esc(componentTitle(row.node, 'Node')) + '</span>' +
                    '<span class="pmedia-ai-tree-node-context">' + esc(row.context) + '</span>' +
                    '</button>';
            }).join('');

            updateSelectedEditor();
        }

        function updateSelectedEditor() {
            var node = selectedPath ? getAt(sections, selectedPath) : null;
            if (pathInput) pathInput.value = selectedPath ? JSON.stringify(selectedPath) : '';
            if (nodeEditor) nodeEditor.value = node ? encode(node) : '';
        }

        function selectPath(path) {
            selectedPath = path;
            renderTree();
        }

        builder.addEventListener('click', function (event) {
            var toggle = event.target.closest('.pmedia-ai-toggle-tree');
            if (toggle) {
                if (!panel) return;
                panel.hidden = !panel.hidden;
                if (!panel.hidden) renderTree();
                return;
            }

            var nodeButton = event.target.closest('.pmedia-ai-tree-node');
            if (nodeButton && builder.contains(nodeButton)) {
                selectPath(parsePathKey(nodeButton.getAttribute('data-path')));
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-add-root')) {
                readSections();
                sections.push(defaultComponent(addType ? addType.value : 'content'));
                selectedPath = [sections.length - 1];
                writeSections(true);
                renderTree();
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-add-child')) {
                readSections();
                if (!selectedPath) {
                    window.alert('Chọn một node trước khi thêm child.');
                    return;
                }
                if (!addChildToNode(sections, selectedPath, addType ? addType.value : 'content')) {
                    window.alert('Node này chưa hỗ trợ child.');
                    return;
                }
                writeSections(true);
                renderTree();
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-apply-node')) {
                if (!selectedPath) return;
                var parsed = parseJson(nodeEditor ? nodeEditor.value : '', null);
                if (!parsed || typeof parsed !== 'object' || Array.isArray(parsed)) {
                    window.alert('Node JSON không hợp lệ.');
                    return;
                }
                readSections();
                setAt(sections, selectedPath, parsed);
                writeSections(true);
                renderTree();
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-delete-node')) {
                if (!selectedPath) return;
                if (!window.confirm('Xóa node này?')) return;
                readSections();
                deleteAt(sections, selectedPath);
                selectedPath = null;
                writeSections(true);
                renderTree();
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-duplicate-node')) {
                if (!selectedPath) return;
                readSections();
                var info = parentArrayInfo(sections, selectedPath);
                if (!info) {
                    window.alert('Node này không thể nhân bản tại vị trí hiện tại.');
                    return;
                }
                info.array.splice(info.index + 1, 0, clone(info.array[info.index]));
                selectedPath = selectedPath.slice(0, -1).concat([info.index + 1]);
                writeSections(true);
                renderTree();
                return;
            }

            if (event.target.closest('.pmedia-ai-tree-move-up') || event.target.closest('.pmedia-ai-tree-move-down')) {
                if (!selectedPath) return;
                readSections();
                var moveInfo = parentArrayInfo(sections, selectedPath);
                if (!moveInfo) {
                    window.alert('Node này không thể di chuyển tại vị trí hiện tại.');
                    return;
                }
                var dir = event.target.closest('.pmedia-ai-tree-move-up') ? -1 : 1;
                var nextIndex = moveInfo.index + dir;
                if (nextIndex < 0 || nextIndex >= moveInfo.array.length) return;
                var temp = moveInfo.array[moveInfo.index];
                moveInfo.array[moveInfo.index] = moveInfo.array[nextIndex];
                moveInfo.array[nextIndex] = temp;
                selectedPath = selectedPath.slice(0, -1).concat([nextIndex]);
                writeSections(true);
                renderTree();
            }
        });

        if (jsonEditor) {
            jsonEditor.addEventListener('change', function () {
                if (panel && !panel.hidden) renderTree();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.pmedia-ai-builder').forEach(initTreeBuilder);
    });
})();
