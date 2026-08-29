/**
 * App Content Builder — Alpine.js component.
 *
 * Extracted from resources/views/admin/settings/app-content.blade.php so that:
 *   - Business logic and UI state live in a dedicated asset.
 *   - The Blade view only configures the component and renders markup.
 *
 * Configuration (CSRF token, route URLs, initial state) is provided by the
 * Blade view via the global `window.QxAppContentConfig` object.
 */
(function () {
    'use strict';

    function getConfig() {
        var cfg = window.QxAppContentConfig || {};
        cfg.routes = cfg.routes || {};
        cfg.urls   = cfg.urls   || {};
        return cfg;
    }

    function endpointFor(linkType, routes) {
        switch (linkType) {
            case 'product':  return routes.products;
            case 'category': return routes.categories;
            case 'brand':    return routes.brands;
            case 'store':    return routes.stores;
            default:         return null;
        }
    }

    window.appContentManager = function appContentManager() {
        var config = getConfig();
        var csrfToken = config.csrfToken || '';
        var routes = config.routes;
        var urls = config.urls;

        return {
            selectedTabId: config.selectedTabId || null,
            contents: config.contents || [],
            showModal: false,
            mobileLibraryOpen: false,
            modalType: 'product',
            editingContent: null,
            saving: false,
            searchQuery: '',
            searchResults: [],
            sortableInstance: null,
            sortableInstanceMobile: null,
            mediaPreview: null,
            mediaFile: null,
            uploadProgress: 0,
            linkOptions: [],
            backgroundMediaFile: null,
            backgroundMediaPreview: null,
            backgroundUploadProgress: 0,
            mediaItemLinkOptions: {},
            mediaItemSearch: {},
            dropTargetIndex: null,
            previewItems: {},
            formData: {
                title: '', show_title: true, subtitle: '', show_subtitle: false,
                style: 'style_1', source: 'featured',
                enable_background: false, background_type: 'color', background_color: '#ffffff', background_media_url: '',
                grid_columns: 2, grid_rows: 2, enable_horizontal_animation: false,
                show_on_tracking: false, show_view_all: false,
                media_type: 'image', media_height: 200, media_width: null, media_url: '',
                link_type: 'none', link_id: '', link_url: '',
                custom_items: [], media_items: []
            },

            init: function () {
                var self = this;
                this.$nextTick(function () {
                    self.initSortable();
                    self.initLibrarySortable();
                });
                if (this.selectedTabId) {
                    this.selectTab(this.selectedTabId);
                }
            },

            initLibrarySortable: function () {
                var containers = this.$el.querySelectorAll('.element-library-group');
                containers.forEach(function (container) {
                    Sortable.create(container, {
                        group: { name: 'page-builder', pull: 'clone', put: false },
                        sort: false,
                        animation: 150,
                        draggable: '.element-card',
                        ghostClass: 'element-ghost',
                    });
                });
            },

            initSortable: function () {
                var self = this;
                if (this.sortableInstance) this.sortableInstance.destroy();
                var desktopList = this.$refs.sortableList;
                if (!desktopList) return;
                this.sortableInstance = new Sortable(desktopList, {
                    animation: 180,
                    easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                    handle: '.drag-handle',
                    draggable: '.widget-card[data-id]',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    dragClass: 'sortable-drag',
                    group: { name: 'page-builder', put: true },
                    onAdd: function (evt) {
                        var type  = evt.item.getAttribute('data-type');
                        var style = evt.item.getAttribute('data-style') || 'style_1';
                        if (type && evt.item.classList.contains('element-card')) {
                            self.dropTargetIndex = evt.newIndex;
                            var isQuick = evt.item.classList.contains('element-quick');
                            var quickOptions = {};
                            if (isQuick) {
                                try { quickOptions = JSON.parse(evt.item.getAttribute('data-quick-options') || '{}'); }
                                catch (e) { /* ignore malformed preset */ }
                            }
                            evt.item.parentNode.removeChild(evt.item);
                            self.$nextTick(function () {
                                if (isQuick) self.openQuickModal(type, quickOptions, true);
                                else         self.openAddModal(type, style, true);
                            });
                        }
                    },
                    onEnd: function (evt) {
                        if (!evt.item.classList.contains('element-card')) {
                            self.handleReorder(evt, 'desktop');
                        }
                    }
                });
            },

            moveUp: function (id) {
                var index = this.contents.findIndex(function (c) { return c.id === id; });
                if (index > 0) {
                    var tmp = this.contents[index - 1];
                    this.contents[index - 1] = this.contents[index];
                    this.contents[index] = tmp;
                    this.contents.forEach(function (item, idx) { item.sort_order = idx; });
                    this.saveReorder();
                }
            },

            moveDown: function (id) {
                var index = this.contents.findIndex(function (c) { return c.id === id; });
                if (index < this.contents.length - 1) {
                    var tmp = this.contents[index];
                    this.contents[index] = this.contents[index + 1];
                    this.contents[index + 1] = tmp;
                    this.contents.forEach(function (item, idx) { item.sort_order = idx; });
                    this.saveReorder();
                }
            },

            saveReorder: async function () {
                var items = this.contents.map(function (content, index) {
                    return { id: content.id, sort_order: index };
                });
                await fetch(routes.reorder, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ contents: items })
                });
            },

            selectTab: async function (tabId) {
                this.selectedTabId = tabId;
                var url = new URL(routes.byTab, window.location.origin);
                if (tabId) url.searchParams.set('tab_id', tabId);
                var res = await fetch(url);
                var data = await res.json();
                this.contents = data.data;
                var self = this;
                this.$nextTick(function () { self.initSortable(); });
                this.loadPreviewItems(tabId);
            },

            loadPreviewItems: async function (tabId) {
                if (!tabId) return;
                try {
                    var url = new URL(routes.previewItems, window.location.origin);
                    url.searchParams.set('tab_id', tabId);
                    var res = await fetch(url);
                    var data = await res.json();
                    if (data.success) this.previewItems = data.data;
                } catch (e) { /* preview falls back to placeholders */ }
            },

            getPreviewGradient: function (i) {
                var g = [
                    'linear-gradient(135deg,#f97316,#ea580c)',
                    'linear-gradient(135deg,#3b82f6,#2563eb)',
                    'linear-gradient(135deg,#22c55e,#16a34a)',
                    'linear-gradient(135deg,#a855f7,#9333ea)',
                    'linear-gradient(135deg,#f43f5e,#e11d48)',
                    'linear-gradient(135deg,#eab308,#ca8a04)',
                ];
                return g[((i - 1) % g.length + g.length) % g.length];
            },

            openAddModal: function (type, style, fromDrag) {
                if (!fromDrag) this.dropTargetIndex = null;
                this.modalType = type;
                this.editingContent = null;
                this.mediaPreview = null;
                this.mediaFile = null;
                this.uploadProgress = 0;
                this.backgroundMediaFile = null;
                this.backgroundMediaPreview = null;
                this.backgroundUploadProgress = 0;
                this.linkOptions = [];
                this.mediaItemLinkOptions = {};
                this.mediaItemSearch = {};
                this.searchQuery = '';
                this.searchResults = [];

                var initialMediaItems = [];
                if (type === 'media') { initialMediaItems = [null]; }

                this.formData = {
                    title: '', show_title: true, subtitle: '', show_subtitle: false,
                    style: style || 'style_1',
                    source: 'featured',
                    enable_background: false, background_type: 'color', background_color: '#ffffff', background_media_url: '',
                    grid_columns: 3, grid_rows: 1,
                    enable_horizontal_animation: false, show_on_tracking: false, show_view_all: false,
                    media_type: 'image', media_height: 200, media_width: null, media_url: '',
                    link_type: 'none', link_id: '', link_url: '',
                    custom_items: [], media_items: initialMediaItems
                };
                if (type === 'store') {
                    this.formData.source = 'custom';
                    this.formData.grid_columns = 3;
                }
                this.showModal = true;
                if (['product', 'category', 'brand', 'store'].includes(type)) {
                    this.searchItems();
                }
            },

            openQuickModal: function (type, options, fromDrag) {
                options = options || {};
                this.openAddModal(type, options.style || null, fromDrag);
                if (options.source !== undefined)                      this.formData.source = options.source;
                if (options.enable_horizontal_animation !== undefined) this.formData.enable_horizontal_animation = options.enable_horizontal_animation;
                if (options.enable_background !== undefined)           this.formData.enable_background = options.enable_background;
                if (options.background_type !== undefined)             this.formData.background_type = options.background_type;
                if (options.grid_columns !== undefined)                this.formData.grid_columns = options.grid_columns;
                if (options.grid_rows !== undefined)                   this.formData.grid_rows = options.grid_rows;
            },

            closeModal: function () {
                this.showModal = false;
                this.dropTargetIndex = null;
            },

            editContent: function (content) {
                this.modalType = content.type;
                this.editingContent = content;
                this.mediaPreview = null;
                this.mediaFile = null;
                this.uploadProgress = 0;
                this.backgroundMediaFile = null;
                this.backgroundMediaPreview = null;
                this.backgroundUploadProgress = 0;
                this.linkOptions = [];
                this.mediaItemLinkOptions = {};
                this.mediaItemSearch = {};
                this.searchQuery = '';
                this.searchResults = [];

                var mediaItems = content.media_items || [];
                if (content.type === 'media' && mediaItems.length === 0) {
                    mediaItems = [null];
                } else {
                    mediaItems = mediaItems.map(function (item) {
                        if (item && item.link_id) { return Object.assign({}, item, { link_id: String(item.link_id) }); }
                        return item;
                    });
                }

                this.formData = JSON.parse(JSON.stringify(Object.assign({}, content, {
                    custom_items: content.custom_items || [],
                    media_url: content.media_url || '',
                    link_id: content.link_id || '',
                    link_url: content.link_url || '',
                    enable_background: content.enable_background || false,
                    background_type: content.background_type || 'color',
                    background_color: content.background_color || '#ffffff',
                    background_media_url: content.background_media_url || '',
                    grid_columns: content.grid_columns || 1,
                    grid_rows: content.grid_rows || 1,
                    enable_horizontal_animation: content.enable_horizontal_animation || false,
                    show_on_tracking: content.show_on_tracking || false,
                    show_view_all: content.show_view_all || false,
                    media_items: mediaItems
                })));

                if (content.background_media_url && content.background_type !== 'color') {
                    this.backgroundMediaPreview = content.background_media_url;
                }
                var self = this;
                if (this.formData.media_items && this.formData.media_items.length > 0) {
                    this.formData.media_items.forEach(function (item, index) {
                        if (item && ['product', 'category', 'brand', 'store'].includes(item.link_type)) {
                            self.handleMediaItemLinkChange(index, true).then(function () {
                                var opts = self.mediaItemLinkOptions[index] || [];
                                var selected = opts.find(function (o) { return String(o.id) === String(item.link_id); });
                                if (selected) {
                                    var merged = {};
                                    Object.assign(merged, self.mediaItemSearch);
                                    merged[index] = selected.name;
                                    self.mediaItemSearch = merged;
                                }
                            });
                        }
                    });
                }
                if (['product', 'category', 'brand', 'store'].includes(content.link_type)) {
                    this.loadLinkOptions(content.link_type);
                }
                if (['product', 'category', 'brand', 'store'].includes(content.type)) {
                    this.searchItems();
                }
                this.showModal = true;
            },

            handleMediaSelect: function (event) {
                var file = event.target.files[0];
                if (file) this.processMediaFile(file);
            },

            handleMediaDrop: function (event) {
                event.target.classList.remove('border-orange-500', 'bg-orange-50');
                var file = event.dataTransfer.files[0];
                if (file) this.processMediaFile(file);
            },

            processMediaFile: function (file) {
                var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
                if (!validTypes.includes(file.type)) { alert('Invalid file type. Please upload JPG, PNG, GIF, MP4, or WebM.'); return; }
                if (file.size > 20 * 1024 * 1024) { alert('File too large. Maximum size is 20MB.'); return; }
                this.mediaFile = file;
                if (file.type.startsWith('video/')) this.formData.media_type = 'video';
                else if (file.type === 'image/gif') this.formData.media_type = 'gif';
                else this.formData.media_type = 'image';
                var self = this;
                var reader = new FileReader();
                reader.onload = function (e) { self.mediaPreview = e.target.result; };
                reader.readAsDataURL(file);
            },

            removeMedia: function () {
                this.mediaPreview = null; this.mediaFile = null; this.formData.media_url = '';
            },

            uploadMedia: async function (contentId) {
                if (!this.mediaFile) return null;
                var formData = new FormData();
                formData.append('file', this.mediaFile);
                formData.append('type', this.formData.media_type);
                var self = this;
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', urls.appContentBase + '/' + contentId + '/media');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.upload.onprogress = function (e) { if (e.lengthComputable) self.uploadProgress = Math.round((e.loaded / e.total) * 100); };
                    xhr.onload = function () { if (xhr.status === 200) resolve(JSON.parse(xhr.responseText)); else reject(new Error('Upload failed')); };
                    xhr.onerror = function () { reject(new Error('Upload failed')); };
                    xhr.send(formData);
                });
            },

            handleLinkTypeChange: async function () {
                this.formData.link_id = ''; this.linkOptions = [];
                if (['product', 'category', 'brand', 'store'].includes(this.formData.link_type)) {
                    await this.loadLinkOptions(this.formData.link_type);
                }
            },

            loadLinkOptions: async function (type) {
                var endpoint = endpointFor(type, routes);
                if (!endpoint) return;
                var res = await fetch(endpoint);
                var data = await res.json();
                this.linkOptions = data.data || [];
            },

            saveContent: async function () {
                this.saving = true;
                var payload = Object.assign({}, this.formData, { type: this.modalType, header_tab_id: this.selectedTabId });
                if (payload.media_items && Array.isArray(payload.media_items)) {
                    payload.media_items = payload.media_items
                        .filter(function (item) { return item !== null && item !== undefined; })
                        .filter(function (item) { return !item.file; })
                        .map(function (item) {
                            return {
                                url: item.url, type: item.type,
                                link_type: item.link_type || 'none',
                                link_id: item.link_id || null,
                                link_url: item.link_url || null
                            };
                        });
                }
                try {
                    var url = this.editingContent
                        ? urls.appContentBase + '/' + this.editingContent.id + '/update'
                        : routes.store;
                    var method = 'POST';
                    var res = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    });
                    if (!res.ok) throw new Error('Server returned ' + res.status + ': ' + res.statusText);
                    var contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) throw new Error('Server returned HTML instead of JSON.');
                    var data = await res.json();
                    if (data.success) {
                        var savedContent = data.data;
                        if (this.modalType === 'media' && this.mediaFile) {
                            try {
                                var uploadResult = await this.uploadMedia(savedContent.id);
                                if (uploadResult.success) {
                                    savedContent.media_url = uploadResult.data.media_url;
                                    savedContent.media_type = uploadResult.data.media_type;
                                }
                            } catch (e) { console.error('Media upload failed:', e); }
                        }
                        if (this.modalType === 'media' && this.formData.media_items && this.formData.media_items.length > 0) {
                            try {
                                var uploadedItems = [];
                                for (var i = 0; i < this.formData.media_items.length; i++) {
                                    var item = this.formData.media_items[i];
                                    if (item && item.file) {
                                        var uploadedUrl = await this.uploadMediaItem(savedContent.id, item, i);
                                        uploadedItems.push({
                                            url: uploadedUrl, type: item.type,
                                            link_type: item.link_type || 'none',
                                            link_id: item.link_id || null,
                                            link_url: item.link_url || null
                                        });
                                    } else if (item && item.url && !item.file) {
                                        uploadedItems.push({
                                            url: item.url, type: item.type,
                                            link_type: item.link_type || 'none',
                                            link_id: item.link_id || null,
                                            link_url: item.link_url || null
                                        });
                                    }
                                }
                                if (uploadedItems.length > 0) {
                                    savedContent.media_items = uploadedItems;
                                    var updateRes = await fetch(urls.appContentBase + '/' + savedContent.id + '/update', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken,
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: JSON.stringify({ media_items: uploadedItems })
                                    });
                                    var updateData = await updateRes.json();
                                    if (updateData.success && updateData.data) { savedContent = updateData.data; }
                                }
                            } catch (e) {
                                console.error('Media items upload failed:', e);
                                alert('Some media items failed to upload: ' + e.message);
                            }
                        }
                        if (['product', 'category', 'brand', 'store'].includes(this.modalType) && this.formData.enable_background && this.formData.background_type !== 'color' && this.backgroundMediaFile) {
                            try {
                                var bgResult = await this.uploadBackgroundMedia(savedContent.id);
                                if (bgResult && bgResult.success) {
                                    savedContent.background_media_url = bgResult.data.background_media_url;
                                    savedContent.background_type = bgResult.data.background_type;
                                    savedContent.enable_background = true;
                                    this.formData.background_media_url = bgResult.data.background_media_url;
                                }
                            } catch (e) {
                                console.error('Background media upload failed:', e);
                                alert('Background media upload failed: ' + e.message);
                            }
                        }
                        if (['product', 'category', 'brand'].includes(this.modalType) && this.formData.enable_background) {
                            if (!savedContent.background_media_url && this.formData.background_media_url) { savedContent.background_media_url = this.formData.background_media_url; }
                            if (!savedContent.background_type && this.formData.background_type) { savedContent.background_type = this.formData.background_type; }
                        }
                        if (this.editingContent) {
                            var idx = this.contents.findIndex((function (editing) {
                                return function (c) { return c.id === editing.id; };
                            })(this.editingContent));
                            if (idx !== -1) this.contents[idx] = savedContent;
                        } else {
                            if (this.dropTargetIndex !== null && this.dropTargetIndex >= 0 && this.dropTargetIndex <= this.contents.length) {
                                this.contents.splice(this.dropTargetIndex, 0, savedContent);
                                var selfInner = this;
                                this.$nextTick(function () {
                                    selfInner.contents.forEach(function (item, i) { item.sort_order = i; });
                                    selfInner.saveReorder();
                                });
                            } else {
                                this.contents.push(savedContent);
                            }
                            this.dropTargetIndex = null;
                        }
                        this.showModal = false;
                        this.mediaFile = null; this.mediaPreview = null; this.uploadProgress = 0;
                        this.backgroundMediaFile = null; this.backgroundMediaPreview = null; this.backgroundUploadProgress = 0;
                        this.loadPreviewItems(this.selectedTabId);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save content'));
                    }
                } catch (error) {
                    console.error('Save error:', error);
                    alert('Failed to save content: ' + error.message);
                } finally {
                    this.saving = false;
                }
            },

            toggleActive: async function (content) {
                var res = await fetch(urls.appContentBase + '/' + content.id + '/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ is_active: !content.is_active })
                });
                if ((await res.json()).success) content.is_active = !content.is_active;
            },

            duplicateContent: async function (content) {
                var res = await fetch(urls.appContentBase + '/' + content.id + '/duplicate', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                var data = await res.json();
                if (data.success) this.contents.push(data.data);
            },

            deleteContent: async function (content) {
                if (!confirm('Delete this widget?')) return;
                var res = await fetch(urls.appContentBase + '/' + content.id + '/delete', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                // Guard against non-JSON responses (e.g. HTML 403 error pages)
                var contentType = res.headers.get('Content-Type') || '';
                if (!contentType.includes('application/json')) {
                    if (res.status === 403) {
                        alert('Access denied. You do not have permission to delete widgets.');
                    } else {
                        alert('Failed to delete widget. Server returned an unexpected response (HTTP ' + res.status + ').');
                    }
                    return;
                }

                var data = await res.json();
                if (data.success) {
                    this.contents = this.contents.filter(function (c) { return c.id !== content.id; });
                } else {
                    alert('Failed to delete widget: ' + (data.message || 'Unknown error'));
                }
            },

            handleReorder: async function (evt, source) {
                var listRef = this.$refs.sortableList;
                var items = Array.from(listRef.querySelectorAll('[data-id]'))
                    .map(function (el, index) { return { id: parseInt(el.dataset.id, 10), sort_order: index }; });
                var self = this;
                items.forEach(function (item) {
                    var content = self.contents.find(function (c) { return c.id === item.id; });
                    if (content) content.sort_order = item.sort_order;
                });
                var sorted = items
                    .map(function (item) { return self.contents.find(function (c) { return c.id === item.id; }); })
                    .filter(Boolean);
                this.contents.splice.apply(this.contents, [0, this.contents.length].concat(sorted));
                await fetch(routes.reorder, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ contents: items })
                });
            },

            searchItems: async function () {
                var endpoint = endpointFor(this.modalType, routes);
                if (!endpoint) return;
                var res = await fetch(endpoint + '?search=' + encodeURIComponent(this.searchQuery || ''));
                this.searchResults = (await res.json()).data;
            },

            handleBackgroundMediaSelect: function (event) {
                var file = event.target.files[0];
                if (!file) return;
                this.backgroundMediaFile = file;
                if (file.type.startsWith('video/')) this.formData.background_type = 'video';
                else if (file.type === 'image/gif') this.formData.background_type = 'gif';
                else if (file.type.startsWith('image/')) this.formData.background_type = 'image';
                var self = this;
                var reader = new FileReader();
                reader.onload = function (e) { self.backgroundMediaPreview = e.target.result; };
                reader.readAsDataURL(file);
            },

            uploadBackgroundMedia: async function (contentId) {
                if (!this.backgroundMediaFile) return null;
                var formData = new FormData();
                formData.append('file', this.backgroundMediaFile);
                formData.append('type', this.formData.background_type);
                var self = this;
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', urls.appContentBase + '/' + contentId + '/background-media');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.upload.onprogress = function (e) { if (e.lengthComputable) self.backgroundUploadProgress = Math.round((e.loaded / e.total) * 100); };
                    xhr.onload = function () { if (xhr.status === 200) resolve(JSON.parse(xhr.responseText)); else reject(new Error('Upload failed')); };
                    xhr.onerror = function () { reject(new Error('Upload failed')); };
                    xhr.send(formData);
                });
            },

            isVideo: function (content) {
                if (content.media_type === 'video') return true;
                if (content.media_url && content.media_url.match(/\.(mp4|webm|mov)$/i)) return true;
                return false;
            },

            getMediaHtml: function (content, size) {
                if (!content.media_url) return '';
                if (this.isVideo(content)) {
                    return '<video src="' + content.media_url + '" class="w-full h-full object-cover" autoplay muted loop playsinline></video>';
                }
                return '<img src="' + content.media_url + '" class="w-full h-full object-cover" alt="">';
            },

            getTypeIcon: function (type) {
                var icons = (config.typeIcons || {});
                return icons[type] || icons.product;
            },

            getTypeLabel: function (type) {
                return { product: 'Product', category: 'Category', brand: 'Brand', media: 'Media', store: 'Store' }[type] || type;
            },

            getStyleName: function (type, style) {
                var names = {
                    product:  { style_1: 'Grid', style_2: 'Horizontal', style_3: 'Large Card' },
                    category: { style_1: 'Circle', style_2: 'Card Grid', style_3: 'Tabs+Products', style_4: 'Checkmark' },
                    brand:    { style_1: 'Circle', style_2: 'Card Grid', style_3: 'Banner' },
                    media:    { style_1: 'Full Width', style_2: 'Padded', style_3: 'Rounded' },
                    store:    { style_1: 'U-Shape Grid', style_2: 'Cover Grid', style_3: 'Horizontal', style_4: 'Feed Cards' }
                };
                return (names[type] && names[type][style]) || style;
            },

            toggleStoreSelection: function (storeId) {
                var id = Number(storeId);
                var idx = this.formData.custom_items.map(Number).indexOf(id);
                if (idx === -1) {
                    this.formData.custom_items = this.formData.custom_items.concat([id]);
                } else {
                    this.formData.custom_items = this.formData.custom_items.filter(function (i) { return Number(i) !== id; });
                }
            },

            getAvailableStyles: function (type) {
                var styles = {
                    product: ['style_1', 'style_2', 'style_3'],
                    category: ['style_1', 'style_2', 'style_3', 'style_4'],
                    brand: ['style_1', 'style_2', 'style_3'],
                    media: ['style_1', 'style_2', 'style_3'],
                    store: ['style_1', 'style_2', 'style_3', 'style_4']
                };
                return styles[type] || ['style_1', 'style_2', 'style_3'];
            },

            handleStyleChange: function () {
                if (this.modalType !== 'media') return;
                if (this.formData.style === 'style_1') {
                    if (!this.formData.media_items || this.formData.media_items.length === 0) this.formData.media_items = [null];
                } else {
                    this.updateMediaItemsGrid();
                }
            },

            updateMediaItemsGrid: function () {
                var totalSlots = (this.formData.grid_columns || 1) * (this.formData.grid_rows || 1);
                if (!this.formData.media_items) this.formData.media_items = [];
                var currentItems = this.formData.media_items;
                if (currentItems.length < totalSlots) {
                    while (this.formData.media_items.length < totalSlots) this.formData.media_items.push(null);
                } else if (currentItems.length > totalSlots) {
                    this.formData.media_items = currentItems.slice(0, totalSlots);
                }
            },

            addMediaSlot: function () {
                if (!this.formData.media_items) this.formData.media_items = [];
                this.formData.media_items.push(null);
            },

            removeMediaSlot: function (index) {
                if (this.formData.media_items && this.formData.media_items.length > 1) {
                    this.formData.media_items.splice(index, 1);
                    if (this.mediaItemLinkOptions[index]) delete this.mediaItemLinkOptions[index];
                } else {
                    alert('At least one slot is required');
                }
            },

            handleMediaItemUpload: async function (event, index) {
                var file = event.target.files[0];
                if (!file) return;
                var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
                if (!validTypes.includes(file.type)) { alert('Invalid file type.'); return; }
                if (file.size > 20 * 1024 * 1024) { alert('File too large. Maximum size is 20MB.'); return; }
                var mediaType = 'image';
                if (file.type.startsWith('video/')) mediaType = 'video';
                else if (file.type === 'image/gif') mediaType = 'gif';
                var self = this;
                var reader = new FileReader();
                reader.onload = function (e) {
                    if (!self.formData.media_items) self.formData.media_items = [];
                    if (self.formData.style === 'style_1') {
                        while (self.formData.media_items.length <= index) self.formData.media_items.push(null);
                    } else {
                        var totalSlots = (self.formData.grid_columns || 1) * (self.formData.grid_rows || 1);
                        while (self.formData.media_items.length < totalSlots) self.formData.media_items.push(null);
                    }
                    self.formData.media_items[index] = {
                        url: e.target.result, type: mediaType,
                        link_type: 'none', link_id: null, link_url: null, file: file
                    };
                };
                reader.readAsDataURL(file);
            },

            removeMediaItem: function (index) {
                if (this.formData.media_items && this.formData.media_items[index]) {
                    this.formData.media_items[index] = null;
                    if (this.mediaItemLinkOptions[index]) delete this.mediaItemLinkOptions[index];
                }
            },

            handleMediaItemLinkChange: async function (index, preserveLinkId) {
                if (!this.formData.media_items || !this.formData.media_items[index]) return;
                var linkType = this.formData.media_items[index].link_type;
                var currentLinkId = this.formData.media_items[index].link_id;
                if (!preserveLinkId) {
                    this.formData.media_items[index].link_id = null;
                    var cleared = Object.assign({}, this.mediaItemSearch);
                    cleared[index] = '';
                    this.mediaItemSearch = cleared;
                }
                var clearedOpts = Object.assign({}, this.mediaItemLinkOptions);
                delete clearedOpts[index];
                this.mediaItemLinkOptions = clearedOpts;
                if (['product', 'category', 'brand', 'store'].includes(linkType)) {
                    var endpoint = endpointFor(linkType, routes);
                    if (!endpoint) return;
                    try {
                        var res = await fetch(endpoint);
                        var data = await res.json();
                        var next = Object.assign({}, this.mediaItemLinkOptions);
                        next[index] = data.data || [];
                        this.mediaItemLinkOptions = next;
                        if (preserveLinkId && currentLinkId) {
                            await this.$nextTick();
                            var items = this.formData.media_items.slice();
                            items[index] = Object.assign({}, items[index], { link_id: String(currentLinkId) });
                            this.formData.media_items = items;
                        }
                    } catch (e) {
                        console.error('Failed to load link options:', e);
                        var err = Object.assign({}, this.mediaItemLinkOptions);
                        err[index] = [];
                        this.mediaItemLinkOptions = err;
                    }
                } else {
                    var newOptions = Object.assign({}, this.mediaItemLinkOptions);
                    delete newOptions[index];
                    this.mediaItemLinkOptions = newOptions;
                }
            },

            _mediaItemSearchTimers: {},

            searchMediaItemLink: function (index, query) {
                var self = this;
                if (this._mediaItemSearchTimers[index]) clearTimeout(this._mediaItemSearchTimers[index]);
                this._mediaItemSearchTimers[index] = setTimeout(async function () {
                    if (!self.formData.media_items || !self.formData.media_items[index]) return;
                    var linkType = self.formData.media_items[index].link_type;
                    if (!['product', 'category', 'brand', 'store'].includes(linkType)) return;
                    var endpoint = endpointFor(linkType, routes);
                    if (!endpoint) return;
                    try {
                        var res = await fetch(endpoint + '?search=' + encodeURIComponent(query || ''));
                        var data = await res.json();
                        var merged = Object.assign({}, self.mediaItemLinkOptions);
                        merged[index] = data.data || [];
                        self.mediaItemLinkOptions = merged;
                    } catch (e) { console.error('Search failed:', e); }
                }, 300);
            },

            uploadMediaItem: function (contentId, item, index) {
                if (!item.file) return Promise.resolve(item.url);
                var formData = new FormData();
                formData.append('file', item.file);
                formData.append('type', item.type);
                formData.append('index', index);
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', urls.appContentBase + '/' + contentId + '/media-item');
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            var result = JSON.parse(xhr.responseText);
                            resolve(result.data.url);
                        } else {
                            reject(new Error('Upload failed'));
                        }
                    };
                    xhr.onerror = function () { reject(new Error('Upload failed')); };
                    xhr.send(formData);
                });
            },

            getStylePreview: function (type, style) {
                var previews = {
                    product:  { style_1: '⊞', style_2: '⇄', style_3: '▣' },
                    category: { style_1: '○○○', style_2: '⊞', style_3: '⊟⇄', style_4: '⊞⊞' },
                    brand:    { style_1: '○○○', style_2: '⊞', style_3: '▭' },
                    media:    { style_1: '▭', style_2: '▭', style_3: '▭' },
                    store:    { style_1: '⊞', style_2: '○○○', style_3: '≡', style_4: '▣' }
                };
                return (previews[type] && previews[type][style]) || '▭';
            }
        };
    };
})();
