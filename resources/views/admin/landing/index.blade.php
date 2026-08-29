@extends('admin.layouts.app')

@section('title', 'Landing Page Builder')

@push('styles')
<style>
    .builder-wrap { display: grid; grid-template-columns: 280px 1fr; gap: 16px; min-height: 85vh; }
    .blocks-panel { background: white; border-radius: 12px; padding: 14px; border: 1px solid #e5e7eb; height: fit-content; position: sticky; top: 80px; max-height: 85vh; overflow-y: auto; }
    .panel-section { margin-bottom: 16px; }
    .panel-title { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
    .block-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 6px; margin-bottom: 4px; cursor: pointer; transition: all 0.15s; background: #fafafa; font-size: 12px; }
    .block-item:hover { border-color: #f97316; background: #fff7ed; }
    .block-item svg { width: 16px; height: 16px; color: #f97316; flex-shrink: 0; }
    .canvas { background: white; border-radius: 12px; border: 1px solid #e5e7eb; }
    .canvas-header { padding: 10px 14px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; border-radius: 12px 12px 0 0; }
    .canvas-body { padding: 14px; min-height: 500px; }
    
    /* Row Section */
    .row-section { border: 2px dashed #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 10px; position: relative; background: #fafafa; }
    .row-section:hover { border-color: #f97316; }
    .row-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
    .row-label { font-size: 10px; font-weight: 700; color: #f97316; text-transform: uppercase; }
    .row-controls { display: flex; gap: 4px; }
    .row-controls button { width: 22px; height: 22px; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; font-size: 10px; }
    .row-controls .up, .row-controls .down { background: #6b7280; color: white; }
    .row-controls .settings { background: #3b82f6; color: white; }
    .row-controls .delete { background: #ef4444; color: white; }
    
    /* Columns */
    .columns-container { display: flex; gap: 10px; min-height: 80px; }
    .column { flex: 1; border: 1px dashed #d1d5db; border-radius: 6px; padding: 8px; background: white; min-height: 60px; position: relative; }
    .column:hover { border-color: #3b82f6; }
    .column.col-25 { flex: 0 0 25%; }
    .column.col-33 { flex: 0 0 33.33%; }
    .column.col-50 { flex: 0 0 50%; }
    .column.col-66 { flex: 0 0 66.66%; }
    .column.col-75 { flex: 0 0 75%; }
    .column.col-100 { flex: 0 0 100%; }
    .column-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; margin-bottom: 6px; }
    .add-element-btn { width: 100%; padding: 8px; background: #f3f4f6; border: 1px dashed #d1d5db; border-radius: 4px; cursor: pointer; font-size: 11px; color: #6b7280; text-align: center; }
    .add-element-btn:hover { background: #e5e7eb; border-color: #9ca3af; }
    
    /* Elements inside columns */
    .element { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px; margin-bottom: 6px; position: relative; font-size: 11px; }
    .element:hover { border-color: #f97316; }
    .element-controls { position: absolute; top: 4px; right: 4px; display: flex; gap: 2px; opacity: 0; transition: opacity 0.15s; }
    .element:hover .element-controls { opacity: 1; }
    .element-controls button { width: 18px; height: 18px; border-radius: 3px; border: none; cursor: pointer; font-size: 9px; display: flex; align-items: center; justify-content: center; }
    .element-type { font-size: 9px; color: #f97316; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; }
    
    /* Layout picker */
    .layout-picker { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 12px; }
    .layout-option { padding: 8px 4px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; text-align: center; font-size: 10px; background: #fafafa; }
    .layout-option:hover { border-color: #f97316; background: #fff7ed; }
    .layout-option.active { border-color: #f97316; background: #fff7ed; }
    .layout-preview { display: flex; gap: 2px; height: 20px; margin-bottom: 4px; }
    .layout-preview div { background: #f97316; border-radius: 2px; opacity: 0.6; }
    
    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 100; display: flex; align-items: center; justify-content: center; }
    .modal-box { background: white; border-radius: 12px; width: 95%; max-width: 700px; max-height: 85vh; overflow: hidden; display: flex; flex-direction: column; }
    .modal-header { padding: 14px 18px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
    .modal-body { padding: 18px; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 14px 18px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end; gap: 8px; }
    
    /* Form */
    .form-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 11px; font-weight: 600; color: #374151; margin-bottom: 4px; }
    .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 12px; }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #f97316; }
    .form-hint { font-size: 10px; color: #9ca3af; margin-top: 3px; }
    .color-input { display: flex; gap: 6px; align-items: center; }
    .color-input input[type="color"] { width: 32px; height: 32px; border: none; border-radius: 4px; cursor: pointer; }
    
    /* Tabs */
    .tab-btn { padding: 8px 16px; border: none; background: transparent; cursor: pointer; font-weight: 500; color: #6b7280; border-bottom: 2px solid transparent; font-size: 12px; }
    .tab-btn.active { color: #f97316; border-color: #f97316; }
    
    /* Source */
    #source-code { width: 100%; min-height: 500px; font-family: 'Monaco', monospace; font-size: 11px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 6px; background: #1e1e1e; color: #d4d4d4; }
    
    .empty-canvas { text-align: center; padding: 50px; color: #9ca3af; }
</style>
@endpush

@section('content')
<div x-data="pageBuilder()" x-init="init()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800">Landing Page Builder</h1>
            <p class="text-xs text-gray-500">Build with rows, columns & elements like WordPress</p>
        </div>
        <div class="flex gap-2">
            <button @click="showPreview()" class="btn-secondary text-sm py-1.5 px-3">Preview</button>
            <button @click="savePage()" class="btn-primary text-sm py-1.5 px-3" :disabled="saving">
                <span x-text="saving ? 'Saving...' : 'Save Page'"></span>
            </button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-t-xl border border-b-0 border-gray-200">
        <button @click="activeTab = 'visual'" :class="activeTab === 'visual' ? 'active' : ''" class="tab-btn">Visual Builder</button>
        <button @click="activeTab = 'source'" :class="activeTab === 'source' ? 'active' : ''" class="tab-btn">Source Code</button>
    </div>

    <!-- Visual Builder -->
    <div x-show="activeTab === 'visual'" class="builder-wrap">
        <!-- Blocks Panel -->
        <div class="blocks-panel">
            <div class="panel-section">
                <div class="panel-title">Add Row</div>
                <div class="layout-picker">
                    <div class="layout-option" @click="addRow('100')">
                        <div class="layout-preview"><div style="flex:1"></div></div>
                        <span>Full</span>
                    </div>
                    <div class="layout-option" @click="addRow('50-50')">
                        <div class="layout-preview"><div style="flex:1"></div><div style="flex:1"></div></div>
                        <span>50/50</span>
                    </div>
                    <div class="layout-option" @click="addRow('33-33-33')">
                        <div class="layout-preview"><div style="flex:1"></div><div style="flex:1"></div><div style="flex:1"></div></div>
                        <span>33/33/33</span>
                    </div>
                    <div class="layout-option" @click="addRow('25-75')">
                        <div class="layout-preview"><div style="flex:1"></div><div style="flex:3"></div></div>
                        <span>25/75</span>
                    </div>
                    <div class="layout-option" @click="addRow('75-25')">
                        <div class="layout-preview"><div style="flex:3"></div><div style="flex:1"></div></div>
                        <span>75/25</span>
                    </div>
                    <div class="layout-option" @click="addRow('33-66')">
                        <div class="layout-preview"><div style="flex:1"></div><div style="flex:2"></div></div>
                        <span>33/66</span>
                    </div>
                    <div class="layout-option" @click="addRow('66-33')">
                        <div class="layout-preview"><div style="flex:2"></div><div style="flex:1"></div></div>
                        <span>66/33</span>
                    </div>
                    <div class="layout-option" @click="addRow('25-25-25-25')">
                        <div class="layout-preview"><div style="flex:1"></div><div style="flex:1"></div><div style="flex:1"></div><div style="flex:1"></div></div>
                        <span>4 cols</span>
                    </div>
                </div>
            </div>
            
            <div class="panel-section">
                <div class="panel-title">Elements</div>
                <template x-for="el in elements" :key="el.type">
                    <div class="block-item" draggable="true" @dragstart="dragElement($event, el)" :data-type="el.type">
                        <span x-html="el.icon"></span>
                        <span x-text="el.name"></span>
                    </div>
                </template>
            </div>
            
            <div class="panel-section">
                <div class="panel-title">Pre-built Sections</div>
                <div class="block-item" @click="addPrebuilt('hero')">🚀 Hero Section</div>
                <div class="block-item" @click="addPrebuilt('features')">✨ Features (3 cols)</div>
                <div class="block-item" @click="addPrebuilt('cta')">📢 Call to Action</div>
                <div class="block-item" @click="addPrebuilt('footer')">📋 Footer</div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="canvas">
            <div class="canvas-header">
                <span class="text-xs font-medium text-gray-600">Page Canvas (<span x-text="rows.length"></span> rows)</span>
                <button @click="rows = []; generateSource()" class="text-xs text-red-500 hover:text-red-700">Clear All</button>
            </div>
            <div class="canvas-body">
                <template x-if="rows.length === 0">
                    <div class="empty-canvas">
                        <p class="text-sm font-medium text-gray-400 mb-2">Click a layout above to add a row</p>
                        <p class="text-xs text-gray-300">Then drag elements into columns</p>
                    </div>
                </template>
                
                <template x-for="(row, rowIdx) in rows" :key="row.id">
                    <div class="row-section" :style="'background:' + (row.bgColor || '#fafafa')">
                        <div class="row-header">
                            <span class="row-label">Row <span x-text="rowIdx + 1"></span> (<span x-text="row.layout"></span>)</span>
                            <div class="row-controls">
                                <button class="up" @click="moveRow(rowIdx, -1)" x-show="rowIdx > 0">↑</button>
                                <button class="down" @click="moveRow(rowIdx, 1)" x-show="rowIdx < rows.length - 1">↓</button>
                                <button class="settings" @click="editRow(row, rowIdx)">⚙</button>
                                <button class="delete" @click="deleteRow(rowIdx)">✕</button>
                            </div>
                        </div>
                        <div class="columns-container">
                            <template x-for="(col, colIdx) in row.columns" :key="colIdx">
                                <div class="column" :class="'col-' + col.width" 
                                     @dragover.prevent 
                                     @drop="dropElement($event, rowIdx, colIdx)">
                                    <div class="column-label" x-text="col.width + '%'"></div>
                                    
                                    <template x-for="(el, elIdx) in col.elements" :key="el.id">
                                        <div class="element">
                                            <div class="element-controls">
                                                <button style="background:#3b82f6;color:white" @click="editElement(rowIdx, colIdx, elIdx)">✎</button>
                                                <button style="background:#ef4444;color:white" @click="deleteElement(rowIdx, colIdx, elIdx)">✕</button>
                                            </div>
                                            <div class="element-type" x-text="el.type"></div>
                                            <div x-html="getElementPreview(el)"></div>
                                        </div>
                                    </template>
                                    
                                    <div class="add-element-btn" @click="openElementPicker(rowIdx, colIdx)">+ Add Element</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Source Code -->
    <div x-show="activeTab === 'source'" class="bg-white rounded-b-xl border border-gray-200 p-4">
        <textarea id="source-code" x-model="sourceCode"></textarea>
    </div>

    <!-- Element Picker Modal -->
    <template x-if="showElementPicker">
        <div class="modal-overlay" @click.self="showElementPicker = false">
            <div class="modal-box" style="max-width: 500px">
                <div class="modal-header">
                    <h3 class="font-semibold text-gray-800 text-sm">Add Element</h3>
                    <button @click="showElementPicker = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-3 gap-2">
                        <template x-for="el in elements" :key="el.type">
                            <div class="block-item" @click="addElementToColumn(el.type)">
                                <span x-html="el.icon"></span>
                                <span x-text="el.name"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Edit Modal -->
    <template x-if="showEditModal">
        <div class="modal-overlay" @click.self="showEditModal = false">
            <div class="modal-box">
                <div class="modal-header">
                    <h3 class="font-semibold text-gray-800 text-sm" x-text="editModalTitle"></h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                <div class="modal-body" x-html="getEditForm()"></div>
                <div class="modal-footer">
                    <button @click="showEditModal = false" class="btn-secondary text-sm">Cancel</button>
                    <button @click="saveEdit()" class="btn-primary text-sm">Apply</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function pageBuilder() {
    return {
        activeTab: 'visual',
        rows: [],
        sourceCode: '',
        saving: false,
        showElementPicker: false,
        showEditModal: false,
        editModalTitle: '',
        pickerTarget: { rowIdx: -1, colIdx: -1 },
        editTarget: { type: '', rowIdx: -1, colIdx: -1, elIdx: -1 },
        editData: {},
        draggedElement: null,

        elements: [
            { type: 'heading', name: 'Heading', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>' },
            { type: 'text', name: 'Text', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h10"/></svg>' },
            { type: 'image', name: 'Image', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' },
            { type: 'button', name: 'Button', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2z"/></svg>' },
            { type: 'icon', name: 'Icon/Emoji', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
            { type: 'spacer', name: 'Spacer', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>' },
            { type: 'divider', name: 'Divider', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/></svg>' },
            { type: 'html', name: 'Custom HTML', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>' },
        ],

        init() {
            const saved = localStorage.getItem('quixko_page_rows');
            if (saved) {
                try { this.rows = JSON.parse(saved); } catch(e) { this.rows = []; }
            }
            this.generateSource();
        },

        addRow(layout) {
            const columns = this.getColumnsForLayout(layout);
            this.rows.push({
                id: Date.now(),
                layout: layout,
                bgColor: '#ffffff',
                padding: '60',
                columns: columns
            });
            this.generateSource();
        },

        getColumnsForLayout(layout) {
            const layouts = {
                '100': [{width: 100, elements: []}],
                '50-50': [{width: 50, elements: []}, {width: 50, elements: []}],
                '33-33-33': [{width: 33, elements: []}, {width: 33, elements: []}, {width: 33, elements: []}],
                '25-75': [{width: 25, elements: []}, {width: 75, elements: []}],
                '75-25': [{width: 75, elements: []}, {width: 25, elements: []}],
                '33-66': [{width: 33, elements: []}, {width: 66, elements: []}],
                '66-33': [{width: 66, elements: []}, {width: 33, elements: []}],
                '25-25-25-25': [{width: 25, elements: []}, {width: 25, elements: []}, {width: 25, elements: []}, {width: 25, elements: []}],
            };
            return layouts[layout] || [{width: 100, elements: []}];
        },

        moveRow(idx, dir) {
            const newIdx = idx + dir;
            if (newIdx >= 0 && newIdx < this.rows.length) {
                [this.rows[idx], this.rows[newIdx]] = [this.rows[newIdx], this.rows[idx]];
                this.generateSource();
            }
        },

        deleteRow(idx) {
            if (confirm('Delete this row?')) {
                this.rows.splice(idx, 1);
                this.generateSource();
            }
        },

        editRow(row, idx) {
            this.editTarget = { type: 'row', rowIdx: idx };
            this.editData = JSON.parse(JSON.stringify(row));
            this.editModalTitle = 'Edit Row Settings';
            this.showEditModal = true;
        },

        openElementPicker(rowIdx, colIdx) {
            this.pickerTarget = { rowIdx, colIdx };
            this.showElementPicker = true;
        },

        addElementToColumn(type) {
            const { rowIdx, colIdx } = this.pickerTarget;
            const el = {
                id: Date.now(),
                type: type,
                data: this.getElementDefaults(type)
            };
            this.rows[rowIdx].columns[colIdx].elements.push(el);
            this.showElementPicker = false;
            this.generateSource();
        },

        dragElement(e, el) {
            this.draggedElement = el;
            e.dataTransfer.setData('text/plain', el.type);
        },

        dropElement(e, rowIdx, colIdx) {
            e.preventDefault();
            const type = e.dataTransfer.getData('text/plain') || (this.draggedElement ? this.draggedElement.type : null);
            if (type) {
                const el = {
                    id: Date.now(),
                    type: type,
                    data: this.getElementDefaults(type)
                };
                this.rows[rowIdx].columns[colIdx].elements.push(el);
                this.generateSource();
            }
            this.draggedElement = null;
        },

        editElement(rowIdx, colIdx, elIdx) {
            const el = this.rows[rowIdx].columns[colIdx].elements[elIdx];
            this.editTarget = { type: 'element', rowIdx, colIdx, elIdx };
            this.editData = JSON.parse(JSON.stringify(el));
            this.editModalTitle = 'Edit ' + el.type.charAt(0).toUpperCase() + el.type.slice(1);
            this.showEditModal = true;
        },

        deleteElement(rowIdx, colIdx, elIdx) {
            this.rows[rowIdx].columns[colIdx].elements.splice(elIdx, 1);
            this.generateSource();
        },

        saveEdit() {
            if (this.editTarget.type === 'row') {
                this.rows[this.editTarget.rowIdx] = this.editData;
            } else if (this.editTarget.type === 'element') {
                this.rows[this.editTarget.rowIdx].columns[this.editTarget.colIdx].elements[this.editTarget.elIdx] = this.editData;
            }
            this.showEditModal = false;
            this.generateSource();
        },

        getElementDefaults(type) {
            const defaults = {
                heading: { text: 'Your Heading Here', size: '36', color: '#1a1a1a', align: 'left', weight: '700' },
                text: { content: 'Add your paragraph text here. You can describe your product, service, or any content.', size: '16', color: '#666666', align: 'left' },
                image: { src: 'https://via.placeholder.com/600x400', alt: 'Image', width: '100%', borderRadius: '12' },
                button: { text: 'Click Here', url: '#', bgColor: '#0C831F', textColor: '#ffffff', size: 'large', fullWidth: false },
                icon: { emoji: '🚀', size: '48' },
                spacer: { height: '40' },
                divider: { color: '#e5e7eb', thickness: '1' },
                html: { code: '<div>Custom HTML</div>' }
            };
            return defaults[type] || {};
        },

        getElementPreview(el) {
            const d = el.data;
            const previews = {
                heading: `<div style="font-size:14px;font-weight:${d.weight};color:${d.color};text-align:${d.align}">${d.text?.substring(0,30)}${d.text?.length > 30 ? '...' : ''}</div>`,
                text: `<div style="font-size:11px;color:${d.color};text-align:${d.align}">${d.content?.substring(0,50)}...</div>`,
                image: `<img src="${d.src}" style="max-width:100%;max-height:60px;border-radius:4px" alt="${d.alt}">`,
                button: `<button style="background:${d.bgColor};color:${d.textColor};padding:4px 12px;border:none;border-radius:4px;font-size:10px">${d.text}</button>`,
                icon: `<div style="font-size:24px;text-align:center">${d.emoji}</div>`,
                spacer: `<div style="height:20px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:9px;color:#9ca3af">${d.height}px</div>`,
                divider: `<hr style="border:none;border-top:${d.thickness}px solid ${d.color};margin:8px 0">`,
                html: `<div style="font-size:9px;color:#10b981;font-family:monospace">HTML</div>`
            };
            return previews[el.type] || '';
        },

        getEditForm() {
            if (this.editTarget.type === 'row') {
                return `
                    <div class="form-group"><label>Background Color</label><div class="color-input"><input type="color" x-model="editData.bgColor"><input type="text" x-model="editData.bgColor"></div></div>
                    <div class="form-group"><label>Padding (px)</label><input type="number" x-model="editData.padding" min="0" max="200"></div>
                `;
            }
            
            const forms = {
                heading: `
                    <div class="form-group"><label>Heading Text</label><input type="text" x-model="editData.data.text"></div>
                    <div class="form-row">
                        <div class="form-group"><label>Font Size (px)</label><input type="number" x-model="editData.data.size" min="12" max="72"></div>
                        <div class="form-group"><label>Font Weight</label><select x-model="editData.data.weight"><option value="400">Normal</option><option value="500">Medium</option><option value="600">Semibold</option><option value="700">Bold</option><option value="800">Extra Bold</option></select></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Color</label><div class="color-input"><input type="color" x-model="editData.data.color"><input type="text" x-model="editData.data.color"></div></div>
                        <div class="form-group"><label>Alignment</label><select x-model="editData.data.align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></div>
                    </div>
                `,
                text: `
                    <div class="form-group"><label>Text Content</label><textarea x-model="editData.data.content" rows="4"></textarea></div>
                    <div class="form-row">
                        <div class="form-group"><label>Font Size (px)</label><input type="number" x-model="editData.data.size" min="12" max="24"></div>
                        <div class="form-group"><label>Alignment</label><select x-model="editData.data.align"><option value="left">Left</option><option value="center">Center</option><option value="right">Right</option></select></div>
                    </div>
                    <div class="form-group"><label>Color</label><div class="color-input"><input type="color" x-model="editData.data.color"><input type="text" x-model="editData.data.color"></div></div>
                `,
                image: `
                    <div class="form-group"><label>Image URL</label><input type="text" x-model="editData.data.src"></div>
                    <div class="form-row">
                        <div class="form-group"><label>Alt Text</label><input type="text" x-model="editData.data.alt"></div>
                        <div class="form-group"><label>Width</label><input type="text" x-model="editData.data.width" placeholder="100% or 300px"></div>
                    </div>
                    <div class="form-group"><label>Border Radius (px)</label><input type="number" x-model="editData.data.borderRadius" min="0" max="50"></div>
                `,
                button: `
                    <div class="form-row">
                        <div class="form-group"><label>Button Text</label><input type="text" x-model="editData.data.text"></div>
                        <div class="form-group"><label>Link URL</label><input type="text" x-model="editData.data.url"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Background</label><div class="color-input"><input type="color" x-model="editData.data.bgColor"><input type="text" x-model="editData.data.bgColor"></div></div>
                        <div class="form-group"><label>Text Color</label><div class="color-input"><input type="color" x-model="editData.data.textColor"><input type="text" x-model="editData.data.textColor"></div></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Size</label><select x-model="editData.data.size"><option value="small">Small</option><option value="medium">Medium</option><option value="large">Large</option></select></div>
                        <div class="form-group"><label>Full Width</label><select x-model="editData.data.fullWidth"><option value="false">No</option><option value="true">Yes</option></select></div>
                    </div>
                `,
                icon: `
                    <div class="form-row">
                        <div class="form-group"><label>Emoji/Icon</label><input type="text" x-model="editData.data.emoji" style="font-size:24px"></div>
                        <div class="form-group"><label>Size (px)</label><input type="number" x-model="editData.data.size" min="16" max="120"></div>
                    </div>
                `,
                spacer: `<div class="form-group"><label>Height (px)</label><input type="number" x-model="editData.data.height" min="10" max="200"></div>`,
                divider: `
                    <div class="form-row">
                        <div class="form-group"><label>Color</label><div class="color-input"><input type="color" x-model="editData.data.color"><input type="text" x-model="editData.data.color"></div></div>
                        <div class="form-group"><label>Thickness (px)</label><input type="number" x-model="editData.data.thickness" min="1" max="10"></div>
                    </div>
                `,
                html: `<div class="form-group"><label>Custom HTML</label><textarea x-model="editData.data.code" rows="10" style="font-family:monospace;font-size:11px"></textarea></div>`,
            };
            return forms[this.editData.type] || '';
        },

        addPrebuilt(type) {
            const prebuilts = {
                hero: {
                    layout: '50-50',
                    bgColor: '#f4fcf6',
                    padding: '100',
                    columns: [
                        { width: 50, elements: [
                            { id: Date.now(), type: 'heading', data: { text: 'Get Groceries in 10 Minutes', size: '42', color: '#1a1a1a', align: 'left', weight: '800' }},
                            { id: Date.now()+1, type: 'text', data: { content: 'Fresh groceries, daily essentials, and more delivered to your doorstep faster than ever.', size: '18', color: '#666', align: 'left' }},
                            { id: Date.now()+2, type: 'button', data: { text: 'Download App', url: '#', bgColor: '#0C831F', textColor: '#fff', size: 'large', fullWidth: false }}
                        ]},
                        { width: 50, elements: [
                            { id: Date.now()+3, type: 'image', data: { src: 'https://via.placeholder.com/500x400', alt: 'App', width: '100%', borderRadius: '16' }}
                        ]}
                    ]
                },
                features: {
                    layout: '33-33-33',
                    bgColor: '#ffffff',
                    padding: '80',
                    columns: [
                        { width: 33, elements: [
                            { id: Date.now(), type: 'icon', data: { emoji: '⚡', size: '48' }},
                            { id: Date.now()+1, type: 'heading', data: { text: 'Fast Delivery', size: '20', color: '#1a1a1a', align: 'center', weight: '700' }},
                            { id: Date.now()+2, type: 'text', data: { content: 'Get your order in 10 minutes', size: '14', color: '#666', align: 'center' }}
                        ]},
                        { width: 33, elements: [
                            { id: Date.now()+3, type: 'icon', data: { emoji: '🌿', size: '48' }},
                            { id: Date.now()+4, type: 'heading', data: { text: 'Fresh Products', size: '20', color: '#1a1a1a', align: 'center', weight: '700' }},
                            { id: Date.now()+5, type: 'text', data: { content: 'Only the freshest groceries', size: '14', color: '#666', align: 'center' }}
                        ]},
                        { width: 33, elements: [
                            { id: Date.now()+6, type: 'icon', data: { emoji: '💰', size: '48' }},
                            { id: Date.now()+7, type: 'heading', data: { text: 'Best Prices', size: '20', color: '#1a1a1a', align: 'center', weight: '700' }},
                            { id: Date.now()+8, type: 'text', data: { content: 'Competitive prices always', size: '14', color: '#666', align: 'center' }}
                        ]}
                    ]
                },
                cta: {
                    layout: '100',
                    bgColor: '#0C831F',
                    padding: '80',
                    columns: [{ width: 100, elements: [
                        { id: Date.now(), type: 'heading', data: { text: 'Ready to Order?', size: '36', color: '#ffffff', align: 'center', weight: '700' }},
                        { id: Date.now()+1, type: 'text', data: { content: 'Download our app and get 20% off your first order!', size: '18', color: '#ffffff', align: 'center' }},
                        { id: Date.now()+2, type: 'button', data: { text: 'Get Started', url: '#', bgColor: '#ffffff', textColor: '#0C831F', size: 'large', fullWidth: false }}
                    ]}]
                },
                footer: {
                    layout: '100',
                    bgColor: '#121212',
                    padding: '60',
                    columns: [{ width: 100, elements: [
                        { id: Date.now(), type: 'heading', data: { text: 'InAllCart', size: '24', color: '#ffffff', align: 'center', weight: '700' }},
                        { id: Date.now()+1, type: 'text', data: { content: '© 2024 InAllCart. All rights reserved.', size: '14', color: '#888888', align: 'center' }}
                    ]}]
                }
            };
            if (prebuilts[type]) {
                this.rows.push({ id: Date.now(), ...prebuilts[type] });
                this.generateSource();
            }
        },

        generateSource() {
            localStorage.setItem('quixko_page_rows', JSON.stringify(this.rows));
            let html = this.buildHTML();
            this.sourceCode = html;
        },

        buildHTML() {
            let body = '';
            this.rows.forEach(row => {
                const colsHTML = row.columns.map(col => {
                    const elsHTML = col.elements.map(el => this.elementToHTML(el)).join('');
                    return `<div style="flex:0 0 ${col.width}%;padding:0 15px">${elsHTML}</div>`;
                }).join('');
                body += `<section style="background:${row.bgColor};padding:${row.padding}px 0"><div style="max-width:1200px;margin:0 auto;padding:0 20px;display:flex;flex-wrap:wrap;align-items:center">${colsHTML}</div></section>`;
            });

            return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>InAllCart</title>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>*{margin:0;padding:0;box-sizing:border-box;font-family:'Manrope',sans-serif}a{text-decoration:none}</style>
</head>
<body>
${body}
</body>
</html>`;
        },

        elementToHTML(el) {
            const d = el.data;
            const html = {
                heading: `<h2 style="font-size:${d.size}px;font-weight:${d.weight};color:${d.color};text-align:${d.align};margin-bottom:15px">${d.text}</h2>`,
                text: `<p style="font-size:${d.size}px;color:${d.color};text-align:${d.align};line-height:1.6;margin-bottom:20px">${d.content}</p>`,
                image: `<img src="${d.src}" alt="${d.alt}" style="width:${d.width};border-radius:${d.borderRadius}px;display:block">`,
                button: `<a href="${d.url}" style="display:${d.fullWidth === 'true' ? 'block' : 'inline-block'};background:${d.bgColor};color:${d.textColor};padding:${d.size === 'large' ? '16px 32px' : d.size === 'small' ? '8px 16px' : '12px 24px'};border-radius:50px;font-weight:700;text-align:center">${d.text}</a>`,
                icon: `<div style="font-size:${d.size}px;text-align:center;margin-bottom:15px">${d.emoji}</div>`,
                spacer: `<div style="height:${d.height}px"></div>`,
                divider: `<hr style="border:none;border-top:${d.thickness}px solid ${d.color};margin:20px 0">`,
                html: d.code || ''
            };
            return html[el.type] || '';
        },

        showPreview() {
            const w = window.open('', '_blank');
            w.document.write(this.sourceCode);
            w.document.close();
        },

        async savePage() {
            this.saving = true;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.landing-page.update") }}';
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="content">`;
            form.querySelector('[name="content"]').value = this.sourceCode;
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endsection
