@extends('admin.layouts.app')
@section('title', 'Add Zone')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
<style>
    #map { height: 550px; width: 100%; z-index: 1; border-radius: 0.75rem; border: 1px solid #cbd5e1; }
    .leaflet-container { font-family: inherit; }
    .pac-container { z-index: 10000 !important; }
</style>
@endpush

@section('content')
<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.zones.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Add Zone</h1>
                <p class="text-sm text-gray-500 mt-1">Define delivery area boundary and pricing</p>
            </div>
        </div>
    </div>

    <form id="zone-form" action="{{ route('admin.zones.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        
        <!-- Left Column: Map & Basic Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Details -->
            <div class="card">
                <div class="card-header"><h3 class="card-title">Zone Details</h3></div>
                <div class="card-body grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="label">Zone Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" class="input" placeholder="e.g. Downtown Area" required>
                    </div>
                    <div class="form-group">
                        <label class="label">City <span class="text-red-500">*</span></label>
                        <input type="text" name="city" value="{{ old('city') }}" class="input" placeholder="e.g. New York" required>
                    </div>
                    <div class="form-group">
                        <label class="label">State</label>
                        <input type="text" name="state" value="{{ old('state') }}" class="input" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label class="label">Country</label>
                        <input type="text" name="country" value="{{ old('country', 'India') }}" class="input">
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="label">Currency</label>
                        <select name="currency" class="input">
                            <option value="">Use Global Default ({{ \App\Models\Setting::get('default_currency', 'INR') }})</option>
                            <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee (₹)</option>
                            <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                            <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                            <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                            <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham (د.إ)</option>
                            <option value="SAR" {{ old('currency') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal (﷼)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Map Editor Card -->
            <div class="card">
                <div class="card-header flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h3 class="card-title">Draw Coverage Area</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Use the map toolbar on the top-left of map to draw or edit polygon boundary</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative w-64">
                            <input type="text" id="search-address" class="input py-1 text-xs" placeholder="Search address/city...">
                        </div>
                        <button type="button" onclick="searchLocation()" class="btn-secondary py-1 px-3 text-xs">Search</button>
                    </div>
                </div>
                <div class="card-body p-0 relative">
                    <div id="map"></div>
                    <input type="hidden" name="coordinates" id="coordinates" value="{{ old('coordinates') }}" required>
                    @error('coordinates')
                        <p class="text-red-600 text-sm p-4 bg-red-50">Please draw a delivery zone boundary on the map.</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Right Column: Pricing & Config -->
        <div class="space-y-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Standard Pricing</h3></div>
                <div class="card-body space-y-4">
                    <div class="form-group">
                        <label class="label">Base Delivery Fee</label>
                        <div class="flex items-center gap-2">
                             <span class="text-gray-500 text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                             <input type="number" name="base_delivery_fee" value="{{ old('base_delivery_fee', 0) }}" class="input" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="label">Fee Per KM</label>
                        <div class="flex items-center gap-2">
                             <span class="text-gray-500 text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                             <input type="number" name="per_km_fee" value="{{ old('per_km_fee', 0) }}" class="input" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="label">Min Order Amount</label>
                        <div class="flex items-center gap-2">
                             <span class="text-gray-500 text-sm">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                             <input type="number" name="min_order_amount" value="{{ old('min_order_amount', 0) }}" class="input" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Surge Pricing -->
            <div class="card" x-data="{ enableSurge: {{ old('surge_status') ? 'true' : 'false' }} }">
                <div class="card-header flex justify-between items-center">
                    <h3 class="card-title flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Surge Pricing
                    </h3>
                    <label class="toggle">
                        <input type="checkbox" name="surge_status" value="1" x-model="enableSurge">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                <div class="card-body space-y-4" x-show="enableSurge" x-transition>
                    <div class="form-group">
                        <label class="label">Surge Type</label>
                        <select name="surge_type" class="input">
                            <option value="percent" {{ old('surge_type') == 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('surge_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="label">Surge Value</label>
                        <input type="number" name="surge_value" value="{{ old('surge_value', 10) }}" class="input" step="0.01" min="0">
                        <p class="text-xs text-gray-500 mt-1">Extra fee to be added during surge</p>
                    </div>
                    <div class="form-group">
                        <label class="label">Message for User</label>
                        <input type="text" name="surge_message" value="{{ old('surge_message', 'High Demand') }}" class="input" placeholder="e.g. Rain Surge">
                    </div>
                </div>
            </div>

             <!-- Status -->
             <div class="card">
                <div class="card-body">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="checkbox">
                        <span class="text-sm font-medium text-gray-700">Zone Active</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center text-base py-3">Create Zone</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
    let map;
    let drawnItems;
    let coordinatesInput = document.getElementById('coordinates');

    document.addEventListener("DOMContentLoaded", function () {
        initLeafletMap();
    });

    function initLeafletMap() {
        // Default center: Bangalore (12.9716, 77.5946)
        map = L.map('map').setView([12.9716, 77.5946], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        let drawControl = new L.Control.Draw({
            draw: {
                polygon: {
                    allowIntersection: false,
                    drawError: { color: '#e1e708', message: '<strong>Oh snap!<strong> shape edges cannot intersect!' },
                    shapeOptions: { color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.4 }
                },
                rectangle: {
                    shapeOptions: { color: '#ea580c', fillColor: '#f97316', fillOpacity: 0.4 }
                },
                polyline: false,
                circle: false,
                marker: false,
                circlemarker: false
            },
            edit: {
                featureGroup: drawnItems,
                remove: true
            }
        });
        map.addControl(drawControl);

        // Created Event
        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            let layer = e.layer;
            drawnItems.addLayer(layer);
            updateCoordinatesFromLayer(layer);
        });

        // Edited Event
        map.on(L.Draw.Event.EDITED, function (e) {
            e.layers.eachLayer(function (layer) {
                updateCoordinatesFromLayer(layer);
            });
        });

        // Deleted Event
        map.on(L.Draw.Event.DELETED, function (e) {
            coordinatesInput.value = '';
        });
    }

    function updateCoordinatesFromLayer(layer) {
        let latLngs = layer.getLatLngs()[0];
        let coordinates = latLngs.map(ll => [ll.lng, ll.lat]);
        if (coordinates.length > 0) {
            coordinates.push(coordinates[0]);
        }

        let geoJson = {
            type: "Polygon",
            coordinates: [coordinates]
        };

        coordinatesInput.value = JSON.stringify(geoJson);
    }

    function searchLocation() {
        let query = document.getElementById('search-address').value;
        if (!query) return;

        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    let lat = parseFloat(data[0].lat);
                    let lon = parseFloat(data[0].lon);
                    map.setView([lat, lon], 14);
                } else {
                    alert('Location not found. Please try another address.');
                }
            })
            .catch(err => {
                console.error(err);
            });
    }
</script>
@endpush
