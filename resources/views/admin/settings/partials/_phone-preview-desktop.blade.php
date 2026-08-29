{{-- Desktop Phone Preview — real app style with backgrounds, grid media, hover-edit --}}
<div class="flex flex-col items-center">

    {{-- ══ iPhone Frame ══ --}}
    <div class="relative select-none"
         style="width:270px;background:#111827;border-radius:44px;
                border:3px solid #1f2937;
                box-shadow:0 0 0 1px #374151,0 32px 80px rgba(0,0,0,.55),inset 0 1px 0 rgba(255,255,255,.08);">

        {{-- Side buttons --}}
        <div style="position:absolute;left:-5px;top:88px;width:4px;height:28px;background:#374151;border-radius:2px 0 0 2px;"></div>
        <div style="position:absolute;left:-5px;top:124px;width:4px;height:48px;background:#374151;border-radius:2px 0 0 2px;"></div>
        <div style="position:absolute;left:-5px;top:180px;width:4px;height:48px;background:#374151;border-radius:2px 0 0 2px;"></div>
        <div style="position:absolute;right:-5px;top:140px;width:4px;height:64px;background:#374151;border-radius:0 2px 2px 0;"></div>

        {{-- Screen --}}
        <div style="border-radius:42px;overflow:hidden;background:#f5f5f5;">

            {{-- Dynamic Island --}}
            <div style="position:absolute;top:10px;left:50%;transform:translateX(-50%);width:96px;height:26px;background:#000;border-radius:20px;z-index:20;display:flex;align-items:center;justify-content:space-between;padding:0 10px;">
                <div style="width:10px;height:10px;background:#1a1a1a;border-radius:50%;"></div>
                <div style="width:12px;height:12px;background:#0a0a0a;border-radius:50%;border:1px solid #2a2a2a;"></div>
            </div>

            {{-- Status bar --}}
            <div style="height:44px;background:#fff;display:flex;align-items:flex-end;padding:0 20px 6px;justify-content:space-between;">
                <span style="font-size:12px;font-weight:700;color:#111;letter-spacing:-.3px;">9:41</span>
                <div style="display:flex;align-items:center;gap:5px;">
                    <svg width="17" height="12" viewBox="0 0 17 12" fill="none"><rect x="0" y="6" width="3" height="6" rx="1" fill="#111"/><rect x="4.5" y="4" width="3" height="8" rx="1" fill="#111"/><rect x="9" y="2" width="3" height="10" rx="1" fill="#111"/><rect x="13.5" y="0" width="3" height="12" rx="1" fill="#111"/></svg>
                    <svg width="16" height="12" viewBox="0 0 16 12" fill="none"><path d="M8 9.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3z" fill="#111"/><path d="M3.5 6.5C4.9 5.1 6.35 4.4 8 4.4s3.1.7 4.5 2.1" stroke="#111" stroke-width="1.5" stroke-linecap="round" fill="none"/><path d="M1 4C3 2 5.4 1 8 1s5 1 7 3" stroke="#111" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
                    <svg width="25" height="12" viewBox="0 0 25 12" fill="none"><rect x=".5" y=".5" width="21" height="11" rx="3.5" stroke="#111"/><rect x="2" y="2" width="17" height="8" rx="2" fill="#111"/><path d="M23 4v4a2 2 0 000-4z" fill="#111"/></svg>
                </div>
            </div>

            {{-- App Header --}}
            <div style="background:#fff;padding:8px 12px 10px;border-bottom:1px solid #f0f0f0;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                    <div style="display:flex;align-items:center;gap:4px;">
                        <svg width="11" height="13" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2.5"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        <span style="font-size:11px;font-weight:700;color:#111;">New York</span>
                        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <div style="width:26px;height:26px;background:#f5f5f5;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                        </div>
                        <div style="width:26px;height:26px;background:#f5f5f5;border-radius:50%;display:flex;align-items:center;justify-content:center;position:relative;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            <div style="position:absolute;top:-1px;right:-1px;width:8px;height:8px;background:#f97316;border-radius:50%;border:1px solid #fff;font-size:5px;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;">2</div>
                        </div>
                    </div>
                </div>
                <div style="background:#f5f5f5;border-radius:8px;padding:6px 10px;display:flex;align-items:center;gap:6px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <span style="color:#aaa;font-size:10.5px;">Search products, stores...</span>
                </div>
            </div>

            {{-- Delivery badge --}}
            <div style="background:#fff;padding:5px 12px;display:flex;align-items:center;gap:5px;border-bottom:1px solid #f0f0f0;">
                <div style="background:#0c8a4b;border-radius:4px;padding:2px 6px;display:flex;align-items:center;gap:3px;">
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span style="font-size:9px;font-weight:700;color:#fff;">10 min delivery</span>
                </div>
                <span style="font-size:9px;color:#888;">Fresh groceries at your door</span>
            </div>

            {{-- ══ Scrollable widgets ══ --}}
            <div class="phone-scroll-area" style="height:352px;overflow-y:auto;background:#f5f5f5;">

                <template x-for="content in contents.filter(c => c.is_active)" :key="'pv-'+content.id">

                    {{-- Outer widget wrapper — hover shows edit button --}}
                    <div class="pv-widget" style="position:relative;margin-top:8px;">

                        {{-- ── Hover edit button ── --}}
                        <button class="pv-edit-btn" @click.stop="editContent(content)"
                                title="Edit widget"
                                style="position:absolute;top:6px;right:6px;z-index:30;
                                       width:22px;height:22px;border-radius:6px;border:none;cursor:pointer;
                                       background:rgba(249,115,22,.92);display:flex;align-items:center;justify-content:center;
                                       box-shadow:0 2px 6px rgba(0,0,0,.25);">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>

                        {{-- ══ MEDIA ══ --}}
                        <template x-if="content.type === 'media'">
                            <div>
                                {{-- Media section header (title/subtitle) --}}
                                <template x-if="(content.show_title && content.title) || (content.show_subtitle && content.subtitle) || content.show_view_all">
                                    <div style="padding:10px 12px 6px;display:flex;align-items:center;justify-content:space-between;">
                                        <div>
                                            <div x-show="content.show_title && content.title" style="font-size:13px;font-weight:800;color:#111;" x-text="content.title"></div>
                                            <div x-show="content.show_subtitle && content.subtitle" style="font-size:9.5px;margin-top:1px;color:#888;" x-text="content.subtitle"></div>
                                        </div>
                                        <div x-show="content.show_view_all" style="font-size:9.5px;font-weight:700;color:#f97316;">View All</div>
                                    </div>
                                </template>
                            <div style="padding:0 8px;">

                                <template x-if="content.media_items && content.media_items.filter(m=>m).length > 0">
                                    <div>
                                        {{-- style_1: Full-width slider --}}
                                        <template x-if="content.style === 'style_1'">
                                            <div :style="`height:${Math.min((content.media_height||160)/1.5|0,110)}px;overflow:hidden;border-radius:10px;position:relative;`">
                                                <template x-if="content.media_items[0] && content.media_items[0].type==='video'">
                                                    <video :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" autoplay muted loop playsinline></video>
                                                </template>
                                                <template x-if="content.media_items[0] && content.media_items[0].type!=='video'">
                                                    <img :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" alt="">
                                                </template>
                                                <template x-if="content.media_items.filter(m=>m).length > 1">
                                                    <div style="position:absolute;bottom:6px;left:50%;transform:translateX(-50%);display:flex;gap:3px;">
                                                        <template x-for="(m,di) in content.media_items.filter(m=>m)" :key="di">
                                                            <div :style="`width:${di===0?12:4}px;height:4px;border-radius:2px;background:${di===0?'#f97316':'rgba(255,255,255,.6)'};`"></div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- style_2: Padded — renders full grid --}}
                                        <template x-if="content.style === 'style_2'">
                                            <div style="padding:0 2px;">
                                                <template x-if="(content.grid_columns||1) <= 1 && (content.grid_rows||1) <= 1">
                                                    <div :style="`height:${Math.min((content.media_height||160)/1.5|0,110)}px;overflow:hidden;border-radius:10px;`">
                                                        <template x-if="content.media_items[0] && content.media_items[0].type!=='video'"><img :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                        <template x-if="content.media_items[0] && content.media_items[0].type==='video'"><video :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" autoplay muted loop playsinline></video></template>
                                                    </div>
                                                </template>
                                                <template x-if="(content.grid_columns||1) > 1 || (content.grid_rows||1) > 1">
                                                    <div :style="`display:grid;grid-template-columns:repeat(${content.grid_columns||2},1fr);gap:3px;border-radius:10px;overflow:hidden;`">
                                                        <template x-for="(mi,mii) in content.media_items.filter(m=>m).slice(0,(content.grid_columns||2)*(content.grid_rows||1))" :key="mii">
                                                            <div :style="`height:${Math.max(Math.min((content.media_height||160)/1.5|0,110)/Math.max(content.grid_rows||1,1),30)}px;overflow:hidden;background:#e5e7eb;`">
                                                                <template x-if="mi.type!=='video'"><img :src="mi.url" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="mi.type==='video'"><video :src="mi.url" style="width:100%;height:100%;object-fit:cover;" autoplay muted loop playsinline></video></template>
                                                            </div>
                                                        </template>
                                                        {{-- Empty slots --}}
                                                        <template x-for="ei in Math.max(0,(content.grid_columns||2)*(content.grid_rows||1)-content.media_items.filter(m=>m).length)" :key="'e'+ei">
                                                            <div :style="`height:${Math.max(Math.min((content.media_height||160)/1.5|0,110)/Math.max(content.grid_rows||1,1),30)}px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;`">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="1.5"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- style_3: Rounded — same as padded but with large border-radius --}}
                                        <template x-if="content.style === 'style_3'">
                                            <div>
                                                <template x-if="(content.grid_columns||1) <= 1 && (content.grid_rows||1) <= 1">
                                                    <div :style="`height:${Math.min((content.media_height||160)/1.5|0,110)}px;overflow:hidden;border-radius:18px;`">
                                                        <template x-if="content.media_items[0] && content.media_items[0].type!=='video'"><img :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                        <template x-if="content.media_items[0] && content.media_items[0].type==='video'"><video :src="content.media_items[0].url" style="width:100%;height:100%;object-fit:cover;" autoplay muted loop playsinline></video></template>
                                                    </div>
                                                </template>
                                                <template x-if="(content.grid_columns||1) > 1 || (content.grid_rows||1) > 1">
                                                    <div :style="`display:grid;grid-template-columns:repeat(${content.grid_columns||2},1fr);gap:3px;border-radius:18px;overflow:hidden;`">
                                                        <template x-for="(mi,mii) in content.media_items.filter(m=>m).slice(0,(content.grid_columns||2)*(content.grid_rows||1))" :key="mii">
                                                            <div :style="`height:${Math.max(Math.min((content.media_height||160)/1.5|0,110)/Math.max(content.grid_rows||1,1),30)}px;overflow:hidden;background:#e5e7eb;`">
                                                                <template x-if="mi.type!=='video'"><img :src="mi.url" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="mi.type==='video'"><video :src="mi.url" style="width:100%;height:100%;object-fit:cover;" autoplay muted loop playsinline></video></template>
                                                            </div>
                                                        </template>
                                                        <template x-for="ei in Math.max(0,(content.grid_columns||2)*(content.grid_rows||1)-content.media_items.filter(m=>m).length)" :key="'e'+ei">
                                                            <div :style="`height:${Math.max(Math.min((content.media_height||160)/1.5|0,110)/Math.max(content.grid_rows||1,1),30)}px;background:#e5e7eb;`"></div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- No media yet --}}
                                <template x-if="!content.media_items || content.media_items.filter(m=>m).length === 0">
                                    <div :style="`height:${Math.min((content.media_height||160)/1.5|0,110)}px;
                                                  border-radius:${content.style==='style_3'?18:10}px;
                                                  background:linear-gradient(135deg,#a855f7,#9333ea);
                                                  display:flex;align-items:center;justify-content:center;`">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="1.5"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                </template>
                            </div>{{-- /padding --}}
                            </div>{{-- /media outer --}}
                        </template>

                        {{-- ══ Non-media widgets — background + section header ══ --}}
                        <template x-if="content.type !== 'media'">

                            {{-- Background wrapper --}}
                            <div :style="
                                content.enable_background && content.background_type === 'color' && content.background_color
                                    ? 'background:' + content.background_color + ';border-radius:10px;overflow:hidden;margin:0 4px;'
                                    : (content.enable_background && content.background_type !== 'color' && content.background_media_url
                                        ? 'background-image:url(' + content.background_media_url + ');background-size:cover;background-position:center;border-radius:10px;overflow:hidden;margin:0 4px;position:relative;'
                                        : 'background:#fff;')
                            ">
                                {{-- BG video overlay --}}
                                <template x-if="content.enable_background && content.background_type === 'video' && content.background_media_url">
                                    <video :src="content.background_media_url" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;" autoplay muted loop playsinline></video>
                                </template>

                                {{-- Content (above background) --}}
                                <div style="position:relative;z-index:1;">

                                    {{-- Section header --}}
                                    <div style="padding:10px 12px 6px;display:flex;align-items:center;justify-content:space-between;">
                                        <div>
                                            {{-- Title: show custom title if show_title on, else fallback to type label --}}
                                            <div style="font-size:13px;font-weight:800;color:#111;"
                                                 x-text="content.show_title ? (content.title || ({'product':'Products','category':'Categories','brand':'Brands','store':'Stores'}[content.type]||'')) : ({'product':'Products','category':'Categories','brand':'Brands','store':'Stores'}[content.type]||'')"></div>
                                            <div x-show="content.show_subtitle && content.subtitle"
                                                 style="font-size:9.5px;margin-top:1px;color:#888;"
                                                 x-text="content.subtitle"></div>
                                        </div>
                                        <div x-show="content.type !== 'category' && content.show_view_all"
                                             style="font-size:9.5px;font-weight:700;color:#f97316;">
                                            View All
                                        </div>
                                    </div>

                                    {{-- ══ PRODUCT ══ --}}
                                    <template x-if="content.type === 'product'">
                                        <div style="padding:0 8px 10px;">

                                            {{-- style_1: Grid --}}
                                            <template x-if="content.style === 'style_1'">
                                                <div :style="`display:grid;grid-template-columns:repeat(${content.grid_columns||2},1fr);gap:6px;`">
                                                    <template x-for="(item,pi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,(content.grid_columns||2)*(content.grid_rows||2)) : Array((content.grid_columns||2)*(content.grid_rows||2)).fill(null))" :key="pi">
                                                        <div style="background:#fff;border-radius:10px;border:1px solid #f0f0f0;overflow:hidden;position:relative;">
                                                            <div style="position:absolute;top:5px;left:5px;background:#256fef;color:#fff;font-size:7px;font-weight:800;padding:2px 4px;border-radius:4px;z-index:2;" x-text="(15+pi*3)+'%'"></div>
                                                            <div style="height:68px;display:flex;align-items:center;justify-content:center;padding:8px;background:#fff;">
                                                                <template x-if="item && item.image">
                                                                    <img :src="item.image" style="max-height:56px;max-width:100%;object-fit:contain;" alt="">
                                                                </template>
                                                                <template x-if="!item || !item.image">
                                                                    <div :style="`width:50px;height:50px;border-radius:8px;background:${getPreviewGradient(pi+1)};display:flex;align-items:center;justify-content:center;`">
                                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                            <div style="padding:4px 5px 6px;border-top:1px solid #f5f5f5;">
                                                                <div style="font-size:8px;color:#888;margin-bottom:1px;">500 g</div>
                                                                <div style="font-size:9px;font-weight:600;color:#111;line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;" x-text="item ? item.name : 'Product Name'"></div>
                                                                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px;">
                                                                    <div>
                                                                        <div style="font-size:9.5px;font-weight:800;color:#111;" x-text="item ? '₹'+parseFloat(item.price||0).toFixed(0) : '₹99'"></div>
                                                                        <div style="font-size:8px;color:#aaa;text-decoration:line-through;" x-text="item ? '₹'+Math.round(parseFloat(item.price||0)*1.2) : '₹120'"></div>
                                                                    </div>
                                                                    <div style="width:26px;height:26px;border:1.5px solid #0c8a4b;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                                                        <span style="font-size:14px;font-weight:700;color:#0c8a4b;line-height:1;">+</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- style_2: Horizontal Scroll --}}
                                            <template x-if="content.style === 'style_2'">
                                                <div style="display:flex;gap:7px;overflow-x:auto;padding-bottom:2px;" class="phone-h-scroll">
                                                    <template x-for="(item,pi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,6) : Array(5).fill(null))" :key="pi">
                                                        <div style="flex-shrink:0;width:88px;background:#fff;border-radius:10px;border:1px solid #f0f0f0;overflow:hidden;position:relative;">
                                                            <div style="position:absolute;top:4px;left:4px;background:#256fef;color:#fff;font-size:6.5px;font-weight:800;padding:1.5px 3.5px;border-radius:3px;z-index:2;" x-text="(12+pi*4)+'%'"></div>
                                                            <div style="height:62px;display:flex;align-items:center;justify-content:center;padding:6px;background:#fff;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-height:52px;max-width:100%;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item || !item.image"><div :style="`width:44px;height:44px;border-radius:6px;background:${getPreviewGradient(pi+1)};`"></div></template>
                                                            </div>
                                                            <div style="padding:3px 5px 5px;border-top:1px solid #f5f5f5;">
                                                                <div style="font-size:7.5px;color:#888;">500 g</div>
                                                                <div style="font-size:8px;font-weight:600;color:#111;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" x-text="item ? item.name : 'Product'"></div>
                                                                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:3px;">
                                                                    <div style="font-size:8.5px;font-weight:800;color:#111;" x-text="item ? '₹'+parseFloat(item.price||0).toFixed(0) : '₹99'"></div>
                                                                    <div style="width:22px;height:22px;border:1.5px solid #0c8a4b;border-radius:5px;display:flex;align-items:center;justify-content:center;"><span style="font-size:13px;font-weight:700;color:#0c8a4b;line-height:1;">+</span></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- style_3: List --}}
                                            <template x-if="content.style === 'style_3'">
                                                <div style="display:flex;flex-direction:column;gap:6px;">
                                                    <template x-for="(item,pi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,4) : Array(3).fill(null))" :key="pi">
                                                        <div style="display:flex;gap:8px;background:#fff;border-radius:10px;border:1px solid #f0f0f0;padding:7px;align-items:center;">
                                                            <div style="width:52px;height:52px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:#f9f9f9;border-radius:8px;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-width:44px;max-height:44px;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item || !item.image"><div :style="`width:38px;height:38px;border-radius:6px;background:${getPreviewGradient(pi+1)};`"></div></template>
                                                            </div>
                                                            <div style="flex:1;min-width:0;">
                                                                <div style="font-size:9.5px;font-weight:700;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item ? item.name : 'Product Name'"></div>
                                                                <div style="font-size:8px;color:#888;margin-top:1px;">500 g · 4.7 ★</div>
                                                                <div style="display:flex;align-items:center;gap:5px;margin-top:3px;">
                                                                    <span style="font-size:10px;font-weight:800;color:#111;" x-text="item ? '₹'+parseFloat(item.price||0).toFixed(0) : '₹99'"></span>
                                                                    <span style="font-size:8px;color:#aaa;text-decoration:line-through;" x-text="item ? '₹'+Math.round(parseFloat(item.price||0)*1.2) : '₹120'"></span>
                                                                </div>
                                                            </div>
                                                            <div style="width:30px;height:30px;border:1.5px solid #0c8a4b;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                                <span style="font-size:16px;font-weight:700;color:#0c8a4b;line-height:1;">+</span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- ══ CATEGORY ══ --}}
                                    <template x-if="content.type === 'category'">
                                        <div style="padding:0 8px 10px;">

                                            {{-- style_1: Circle Row --}}
                                            <template x-if="content.style === 'style_1'">
                                                <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;" class="phone-h-scroll">
                                                    <template x-for="(item,ci) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,8) : Array(6).fill(null))" :key="ci">
                                                        <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:4px;width:54px;">
                                                            <div style="width:50px;height:50px;border-radius:50%;overflow:hidden;border:2px solid #f0f0f0;background:#f5f5f5;display:flex;align-items:center;justify-content:center;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="!item || !item.image"><div :style="`width:34px;height:34px;border-radius:50%;background:${getPreviewGradient(ci+1)};`"></div></template>
                                                            </div>
                                                            <span style="font-size:8px;font-weight:600;color:#333;text-align:center;line-height:1.2;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;max-width:52px;" x-text="item ? item.name : 'Category'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- style_2: Card Grid --}}
                                            <template x-if="content.style === 'style_2'">
                                                <div :style="`display:grid;grid-template-columns:repeat(${content.grid_columns||3},1fr);gap:6px;`">
                                                    <template x-for="(item,ci) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,(content.grid_columns||3)*(content.grid_rows||2)) : Array((content.grid_columns||3)*(content.grid_rows||2)).fill(null))" :key="ci">
                                                        <div style="border-radius:10px;overflow:hidden;display:flex;flex-direction:column;align-items:center;padding:7px 4px 5px;background:rgba(255,255,255,0.85);">
                                                            <div style="width:46px;height:46px;display:flex;align-items:center;justify-content:center;margin-bottom:4px;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-width:42px;max-height:42px;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item || !item.image"><div :style="`width:36px;height:36px;border-radius:8px;background:${getPreviewGradient(ci+1)};`"></div></template>
                                                            </div>
                                                            <span style="font-size:8.5px;font-weight:700;color:#111;text-align:center;line-height:1.2;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;" x-text="item ? item.name : 'Category'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>

                                            {{-- style_3: Tabs + Products (enhanced) --}}
                                            <template x-if="content.style === 'style_3'">
                                                <div>
                                                    {{-- Category tab pills with images --}}
                                                    <div style="display:flex;gap:6px;overflow-x:auto;padding-bottom:8px;" class="phone-h-scroll">
                                                        <template x-for="(item,ci) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,6) : Array(4).fill(null))" :key="ci">
                                                            <div style="flex-shrink:0;display:flex;align-items:center;gap:4px;padding:4px 9px 4px 4px;border-radius:20px;cursor:pointer;"
                                                                 :style="ci===0 ? 'background:#f97316;' : 'background:#f0f0f0;'">
                                                                {{-- Small category image in tab --}}
                                                                <div style="width:20px;height:20px;border-radius:50%;overflow:hidden;flex-shrink:0;"
                                                                     :style="`background:${ci===0?'rgba(255,255,255,.3)':'#e0e0e0'};`">
                                                                    <template x-if="item && item.image">
                                                                        <img :src="item.image" style="width:100%;height:100%;object-fit:cover;" alt="">
                                                                    </template>
                                                                    <template x-if="!item || !item.image">
                                                                        <div :style="`width:100%;height:100%;background:${getPreviewGradient(ci+1)};`"></div>
                                                                    </template>
                                                                </div>
                                                                <span :style="`font-size:9px;font-weight:700;${ci===0?'color:#fff;':'color:#555;'}`"
                                                                      x-text="item ? item.name : 'Tab'"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    {{-- Product grid below active tab (placeholder products) --}}
                                                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;">
                                                        <template x-for="pi in 3" :key="pi">
                                                            <div style="background:#fff;border-radius:8px;border:1px solid #f0f0f0;overflow:hidden;position:relative;">
                                                                <div style="position:absolute;top:3px;left:3px;background:#256fef;color:#fff;font-size:6px;font-weight:800;padding:1.5px 3px;border-radius:3px;z-index:2;" x-text="(10+pi*5)+'%'"></div>
                                                                <div style="height:50px;display:flex;align-items:center;justify-content:center;padding:5px;background:#fff;">
                                                                    <div :style="`width:36px;height:36px;border-radius:6px;background:${getPreviewGradient(pi+1)};`"></div>
                                                                </div>
                                                                <div style="padding:3px 4px 4px;border-top:1px solid #f5f5f5;">
                                                                    <div style="font-size:7.5px;font-weight:600;color:#111;">Product</div>
                                                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:2px;">
                                                                        <span style="font-size:8px;font-weight:800;color:#111;">₹99</span>
                                                                        <div style="width:18px;height:18px;border:1.5px solid #0c8a4b;border-radius:4px;display:flex;align-items:center;justify-content:center;"><span style="font-size:11px;font-weight:700;color:#0c8a4b;line-height:1;">+</span></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- style_4: Highlight rows (3-col + 4-col) --}}
                                            <template x-if="content.style === 'style_4'">
                                                <div style="display:flex;flex-direction:column;gap:5px;">
                                                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:5px;">
                                                        <template x-for="(item,ci) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,3) : Array(3).fill(null))" :key="ci">
                                                            <div style="border-radius:8px;overflow:hidden;padding:6px 3px 5px;display:flex;flex-direction:column;align-items:center;background:rgba(255,255,255,0.85);">
                                                                <div style="width:38px;height:38px;display:flex;align-items:center;justify-content:center;margin-bottom:3px;">
                                                                    <template x-if="item && item.image"><img :src="item.image" style="max-width:34px;max-height:34px;object-fit:contain;" alt=""></template>
                                                                    <template x-if="!item||!item.image"><div :style="`width:28px;height:28px;border-radius:6px;background:${getPreviewGradient(ci+1)};`"></div></template>
                                                                </div>
                                                                <span style="font-size:7.5px;font-weight:700;color:#111;text-align:center;line-height:1.2;" x-text="item ? item.name : 'Cat'"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;">
                                                        <template x-for="(item,ci) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(3,7) : Array(4).fill(null))" :key="ci">
                                                            <div style="border-radius:7px;overflow:hidden;padding:5px 2px 4px;display:flex;flex-direction:column;align-items:center;background:rgba(255,255,255,0.85);">
                                                                <div style="width:30px;height:30px;display:flex;align-items:center;justify-content:center;margin-bottom:2px;">
                                                                    <template x-if="item && item.image"><img :src="item.image" style="max-width:26px;max-height:26px;object-fit:contain;" alt=""></template>
                                                                    <template x-if="!item||!item.image"><div :style="`width:22px;height:22px;border-radius:5px;background:${getPreviewGradient(ci+4)};`"></div></template>
                                                                </div>
                                                                <span style="font-size:7px;font-weight:700;color:#111;text-align:center;line-height:1.2;overflow:hidden;max-width:100%;text-overflow:ellipsis;white-space:nowrap;" x-text="item ? item.name : 'Cat'"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- ══ BRAND ══ --}}
                                    <template x-if="content.type === 'brand'">
                                        <div style="padding:0 8px 10px;">
                                            {{-- style_1: Circle Row --}}
                                            <template x-if="content.style === 'style_1'">
                                                <div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:2px;" class="phone-h-scroll">
                                                    <template x-for="(item,bi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,7) : Array(5).fill(null))" :key="bi">
                                                        <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:4px;width:52px;">
                                                            <div style="width:48px;height:48px;border-radius:50%;border:1.5px solid #f0f0f0;display:flex;align-items:center;justify-content:center;background:#fafafa;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-width:38px;max-height:38px;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item||!item.image"><div :style="`width:32px;height:32px;border-radius:50%;background:${getPreviewGradient(bi+1)};`"></div></template>
                                                            </div>
                                                            <span style="font-size:7.5px;font-weight:600;color:#333;text-align:center;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:50px;" x-text="item ? item.name : 'Brand'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- style_2: Card Grid --}}
                                            <template x-if="content.style === 'style_2'">
                                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                                                    <template x-for="(item,bi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,6) : Array(6).fill(null))" :key="bi">
                                                        <div style="background:#fafafa;border:1px solid #f0f0f0;border-radius:10px;padding:8px 4px 6px;display:flex;flex-direction:column;align-items:center;gap:4px;">
                                                            <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-width:36px;max-height:36px;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item||!item.image"><div :style="`width:30px;height:30px;border-radius:6px;background:${getPreviewGradient(bi+1)};`"></div></template>
                                                            </div>
                                                            <span style="font-size:8px;font-weight:700;color:#111;text-align:center;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;width:100%;padding:0 2px;" x-text="item ? item.name : 'Brand'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- style_3: Banner list --}}
                                            <template x-if="content.style === 'style_3'">
                                                <div style="display:flex;flex-direction:column;gap:5px;">
                                                    <template x-for="(item,bi) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,3) : Array(3).fill(null))" :key="bi">
                                                        <div style="height:42px;background:rgba(255,255,255,.9);border:1px solid #f0f0f0;border-radius:10px;display:flex;align-items:center;gap:9px;padding:0 10px;">
                                                            <div style="width:32px;height:32px;flex-shrink:0;background:#f0f0f0;border-radius:7px;display:flex;align-items:center;justify-content:center;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="max-width:28px;max-height:28px;object-fit:contain;" alt=""></template>
                                                                <template x-if="!item||!item.image"><div :style="`width:22px;height:22px;border-radius:4px;background:${getPreviewGradient(bi+1)};`"></div></template>
                                                            </div>
                                                            <span style="flex:1;font-size:10px;font-weight:700;color:#111;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" x-text="item ? item.name : 'Brand Name'"></span>
                                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#aaa" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- ══ STORE ══ --}}
                                    <template x-if="content.type === 'store'">
                                        <div style="padding:0 8px 10px;">
                                            {{-- style_1: U-Shape --}}
                                            <template x-if="content.style === 'style_1'">
                                                <div style="display:flex;flex-direction:column;gap:5px;">
                                                    <template x-for="(item,si) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,1) : Array(1).fill(null))" :key="si">
                                                        <div style="height:80px;border-radius:10px;overflow:hidden;position:relative;background:#f0f0f0;">
                                                            <template x-if="item && item.banner"><img :src="item.banner" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                            <template x-if="!item||!item.banner"><div :style="`width:100%;height:100%;background:${getPreviewGradient(1)};`"></div></template>
                                                            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);"></div>
                                                            <div style="position:absolute;bottom:0;left:0;right:0;padding:6px 8px;display:flex;align-items:center;gap:6px;">
                                                                <div style="width:24px;height:24px;border-radius:6px;overflow:hidden;background:#fff;flex-shrink:0;">
                                                                    <template x-if="item && item.image"><img :src="item.image" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                </div>
                                                                <div>
                                                                    <div style="font-size:9.5px;font-weight:700;color:#fff;" x-text="item ? item.name : 'Store'"></div>
                                                                    <div style="font-size:7.5px;color:rgba(255,255,255,.8);">Open · 4.7 ★ · 10 min</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;">
                                                        <template x-for="(item,si) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(1,3) : Array(2).fill(null))" :key="si">
                                                            <div style="height:62px;border-radius:10px;overflow:hidden;position:relative;background:#f0f0f0;">
                                                                <template x-if="item && item.banner"><img :src="item.banner" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="!item||!item.banner"><div :style="`width:100%;height:100%;background:${getPreviewGradient(si+2)};`"></div></template>
                                                                <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.5),transparent);"></div>
                                                                <div style="position:absolute;bottom:4px;left:6px;right:6px;">
                                                                    <div style="font-size:8.5px;font-weight:700;color:#fff;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" x-text="item ? item.name : 'Store'"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                            {{-- style_2: Cover Grid --}}
                                            <template x-if="content.style === 'style_2'">
                                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:5px;">
                                                    <template x-for="(item,si) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,4) : Array(4).fill(null))" :key="si">
                                                        <div :style="`border-radius:10px;overflow:hidden;position:relative;background:#f0f0f0;height:${si===0?95:70}px;`">
                                                            <template x-if="item && item.banner"><img :src="item.banner" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                            <template x-if="!item||!item.banner"><div :style="`width:100%;height:100%;background:${getPreviewGradient(si+1)};`"></div></template>
                                                            <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.55),transparent);"></div>
                                                            <div style="position:absolute;bottom:5px;left:6px;right:6px;">
                                                                <div style="font-size:8.5px;font-weight:700;color:#fff;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" x-text="item ? item.name : 'Store'"></div>
                                                                <div style="font-size:7px;color:rgba(255,255,255,.75);">4.8 ★ · 12 min</div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- style_3: Horizontal scroll --}}
                                            <template x-if="content.style === 'style_3'">
                                                <div style="display:flex;gap:7px;overflow-x:auto;padding-bottom:2px;" class="phone-h-scroll">
                                                    <template x-for="(item,si) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,5) : Array(4).fill(null))" :key="si">
                                                        <div style="flex-shrink:0;width:100px;border-radius:10px;border:1px solid #f0f0f0;overflow:hidden;background:#fff;">
                                                            <div style="height:58px;overflow:hidden;background:#f0f0f0;">
                                                                <template x-if="item && item.banner"><img :src="item.banner" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="!item||!item.banner"><div :style="`width:100%;height:100%;background:${getPreviewGradient(si+1)};`"></div></template>
                                                            </div>
                                                            <div style="padding:4px 6px 5px;display:flex;align-items:center;gap:4px;">
                                                                <div style="width:18px;height:18px;border-radius:5px;overflow:hidden;background:#f0f0f0;flex-shrink:0;">
                                                                    <template x-if="item && item.image"><img :src="item.image" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                </div>
                                                                <div>
                                                                    <div style="font-size:8px;font-weight:700;color:#111;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:66px;" x-text="item ? item.name : 'Store'"></div>
                                                                    <div style="font-size:7px;color:#aaa;">10 min · 4.7★</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            {{-- style_4: Feed Cards --}}
                                            <template x-if="content.style === 'style_4'">
                                                <div style="display:flex;flex-direction:column;gap:6px;">
                                                    <template x-for="(item,si) in (previewItems[content.id]&&previewItems[content.id].length ? previewItems[content.id].slice(0,3) : Array(3).fill(null))" :key="si">
                                                        <div style="display:flex;gap:8px;background:rgba(255,255,255,.9);border:1px solid #f0f0f0;border-radius:10px;padding:7px;align-items:center;">
                                                            <div style="width:46px;height:46px;border-radius:9px;overflow:hidden;flex-shrink:0;background:#f0f0f0;">
                                                                <template x-if="item && item.image"><img :src="item.image" style="width:100%;height:100%;object-fit:cover;" alt=""></template>
                                                                <template x-if="!item||!item.image"><div :style="`width:100%;height:100%;background:${getPreviewGradient(si+1)};`"></div></template>
                                                            </div>
                                                            <div style="flex:1;min-width:0;">
                                                                <div style="font-size:10px;font-weight:800;color:#111;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" x-text="item ? item.name : 'Store Name'"></div>
                                                                <div style="font-size:8px;color:#888;margin-top:1px;">Open · 4.7 ★ · 1.2 km</div>
                                                                <div style="display:flex;gap:4px;margin-top:3px;">
                                                                    <div style="background:#e8f5e9;color:#2e7d32;font-size:7px;font-weight:700;padding:1.5px 5px;border-radius:4px;">10 min</div>
                                                                    <div style="background:#fff3e0;color:#e65100;font-size:7px;font-weight:700;padding:1.5px 5px;border-radius:4px;">Free delivery</div>
                                                                </div>
                                                            </div>
                                                            <div style="flex-shrink:0;">
                                                                <div style="font-size:8px;font-weight:700;color:#0c8a4b;background:#e8f5e9;padding:4px 7px;border-radius:6px;">Order</div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                </div>{{-- /z-index content --}}
                            </div>{{-- /background wrapper --}}
                        </template>{{-- /non-media --}}

                    </div>{{-- /pv-widget --}}
                </template>

                {{-- Empty state --}}
                <template x-if="contents.filter(c => c.is_active).length === 0">
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:40px 20px;text-align:center;">
                        <div style="width:52px;height:52px;background:#f0f0f0;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="1.5"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        </div>
                        <p style="font-size:11px;font-weight:700;color:#555;">No active widgets</p>
                        <p style="font-size:9.5px;color:#aaa;margin-top:2px;">Add widgets from the left panel</p>
                    </div>
                </template>

            </div>{{-- /scroll --}}

            {{-- ══ Bottom Nav ══ --}}
            <div style="height:50px;background:#fff;border-top:1.5px solid #f0f0f0;display:flex;align-items:center;justify-content:space-around;padding:0 4px;">
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2.5"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span style="font-size:8px;font-weight:700;color:#f97316;">Home</span>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                    <span style="font-size:8px;color:#bbb;">Explore</span>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;position:relative;">
                    <div style="position:absolute;top:-2px;right:-2px;width:9px;height:9px;background:#f97316;border-radius:50%;border:1.5px solid #fff;display:flex;align-items:center;justify-content:center;font-size:5.5px;font-weight:800;color:#fff;">2</div>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span style="font-size:8px;color:#bbb;">Cart</span>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span style="font-size:8px;color:#bbb;">Orders</span>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#bbb" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="font-size:8px;color:#bbb;">Profile</span>
                </div>
            </div>

            {{-- Home indicator --}}
            <div style="height:18px;background:#fff;display:flex;align-items:center;justify-content:center;">
                <div style="width:90px;height:4px;background:#e5e7eb;border-radius:4px;"></div>
            </div>

        </div>{{-- /screen --}}
    </div>{{-- /frame --}}

    <p style="margin-top:8px;font-size:9px;color:#9ca3af;letter-spacing:.5px;font-weight:600;">LIVE PREVIEW</p>
</div>

<style>
.phone-scroll-area::-webkit-scrollbar,.phone-h-scroll::-webkit-scrollbar{display:none;}
.phone-scroll-area,.phone-h-scroll{-ms-overflow-style:none;scrollbar-width:none;}

/* Hover edit button — hidden by default, visible on widget hover */
.pv-widget .pv-edit-btn { opacity:0; transition:opacity .15s ease; pointer-events:none; }
.pv-widget:hover .pv-edit-btn { opacity:1; pointer-events:auto; }
.pv-edit-btn:hover { transform:scale(1.1); }
</style>
