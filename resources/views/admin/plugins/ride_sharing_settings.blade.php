@extends('admin.layouts.app')

@section('title', 'Ride Sharing Plugin Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 text-gray-800 font-weight-bold mb-1">Ride Sharing Plugin Settings</h1>
            <p class="text-muted mb-0">Configure Ride Sharing module icons, active status, and ride fare rules for the User App.</p>
        </div>
        <a href="{{ route('admin.plugins.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Plugins
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.ride-sharing.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Module Details Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-primary">Module Identity & Display Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Module Active Toggle -->
                            <div class="col-12 mb-2">
                                <div class="form-check form-switch p-0">
                                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded border">
                                        <div>
                                            <label class="form-check-label font-weight-bold mb-0" for="is_active">Enable Ride Sharing Plugin</label>
                                            <div class="text-muted small">Show cab/ride booking module in User App home screen & header tabs</div>
                                        </div>
                                        <input class="form-check-input ms-3" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ $settings['is_active'] == '1' ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
                                    </div>
                                </div>
                            </div>

                            <!-- Module Name -->
                            <div class="col-md-6">
                                <label for="module_name" class="form-label font-weight-bold">Module Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="module_name" name="module_name" value="{{ old('module_name', $settings['module_name']) }}" required placeholder="e.g. Ride Sharing">
                            </div>

                            <!-- Module Description -->
                            <div class="col-md-6">
                                <label for="module_description" class="form-label font-weight-bold">Module Description</label>
                                <input type="text" class="form-control" id="module_description" name="module_description" value="{{ old('module_description', $settings['module_description']) }}" placeholder="e.g. Book cabs & parcel rides instantly">
                            </div>

                            <!-- Module Icon Upload -->
                            <div class="col-12 mt-3">
                                <label class="form-label font-weight-bold">Module Icon (User App Display)</label>
                                <div class="p-3 bg-light rounded border">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="icon-preview-box bg-white p-2 border rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                            @if(!empty($settings['module_icon']))
                                                <img id="iconPreview" src="{{ $settings['module_icon'] }}" alt="Module Icon" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                            @else
                                                <i id="iconPlaceholder" class="bi bi-car-front-fill text-primary" style="font-size: 2rem;"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1 font-weight-bold">Upload Custom Module Icon</h6>
                                            <p class="text-muted small mb-0">Supported formats: PNG, SVG, JPG, WebP. Recommended size: 128x128 px.</p>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <input type="file" class="form-control" name="module_icon_file" accept="image/*" onchange="previewIconImage(this)">
                                        </div>
                                        <div class="col-md-6">
                                            <input type="url" class="form-control" name="module_icon_url" value="{{ old('module_icon_url', $settings['module_icon']) }}" placeholder="Or paste image URL (e.g. https://...)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Rules Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="m-0 font-weight-bold text-primary">Default Ride Fare Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="base_fare" class="form-label font-weight-bold">Base Fare</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ \App\Models\Setting::get('currency_symbol', '₹') }}</span>
                                    <input type="number" step="0.01" class="form-control" id="base_fare" name="base_fare" value="{{ old('base_fare', $settings['base_fare']) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="per_km_rate" class="form-label font-weight-bold">Per KM Rate</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ \App\Models\Setting::get('currency_symbol', '₹') }}</span>
                                    <input type="number" step="0.01" class="form-control" id="per_km_rate" name="per_km_rate" value="{{ old('per_km_rate', $settings['per_km_rate']) }}">
                                    <span class="input-group-text">/ km</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold">
                        <i class="bi bi-save me-2"></i> Save Module Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewIconImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('iconPreview');
            if (!img) {
                var box = document.querySelector('.icon-preview-box');
                box.innerHTML = '<img id="iconPreview" src="' + e.target.result + '" style="max-width: 100%; max-height: 100%; object-fit: contain;">';
            } else {
                img.src = e.target.result;
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
