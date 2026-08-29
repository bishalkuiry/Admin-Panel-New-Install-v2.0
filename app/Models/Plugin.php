<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plugin extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'version',
        'author',
        'author_url',
        'plugin_url',
        'is_active',
        'is_installed',
        'path',
        'namespace',
        'requires_php',
        'requires_laravel',
        'requires_quixko',
        'dependencies',
        'license_key',
        'license_status',
        'license_verified_at',
        'settings',
        'metadata',
        'hooks',
        'installed_at',
        'activated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
        'dependencies' => 'array',
        'settings' => 'array',
        'metadata' => 'array',
        'hooks' => 'array',
        'license_verified_at' => 'datetime',
        'installed_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    /**
     * Get plugin metadata/manifest array.
     * Falls back to reading plugin.json from disk if DB metadata is null or empty.
     */
    public function getMetadataAttribute($value): array
    {
        if ($value) {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (!empty($decoded) && is_array($decoded)) {
                return $decoded;
            }
        }

        $manifestPath = $this->getManifestPath();
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (is_array($manifest)) {
                return $manifest;
            }
        }

        return [];
    }

    /**
     * Get the hooks for the plugin.
     */
    public function pluginHooks(): HasMany
    {
        return $this->hasMany(PluginHook::class);
    }

    /**
     * Get the settings for the plugin.
     */
    public function pluginSettings(): HasMany
    {
        return $this->hasMany(PluginSetting::class);
    }

    /**
     * Scope a query to only include active plugins.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include installed plugins.
     */
    public function scopeInstalled($query)
    {
        return $query->where('is_installed', true);
    }

    /**
     * Check if plugin is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if plugin is installed.
     */
    public function isInstalled(): bool
    {
        return $this->is_installed;
    }

    /**
     * Check if plugin has valid license.
     */
    public function hasValidLicense(): bool
    {
        return $this->license_status === 'valid';
    }

    /**
     * Get plugin directory path.
     */
    public function getDirectoryPath(): string
    {
        return base_path($this->path);
    }

    /**
     * Get plugin manifest path.
     */
    public function getManifestPath(): string
    {
        return $this->getDirectoryPath() . '/plugin.json';
    }

    /**
     * Check if plugin has dependencies.
     */
    public function hasDependencies(): bool
    {
        return !empty($this->dependencies);
    }

    /**
     * Get plugin dependencies.
     */
    public function getDependencies(): array
    {
        return $this->dependencies ?? [];
    }

    /**
     * Get a plugin setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->pluginSettings()->where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($setting->value, true),
            'encrypted' => decrypt($setting->value),
            default => $setting->value,
        };
    }

    /**
     * Set a plugin setting value.
     */
    public function setSetting(string $key, $value, string $type = 'string'): void
    {
        $processedValue = match ($type) {
            'json' => json_encode($value),
            'encrypted' => encrypt($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };

        $this->pluginSettings()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $processedValue,
                'type' => $type,
            ]
        );
    }

    /**
     * Get all plugin settings as array.
     */
    public function getAllSettings(): array
    {
        $settings = [];
        
        foreach ($this->pluginSettings as $setting) {
            $settings[$setting->key] = match ($setting->type) {
                'integer' => (int) $setting->value,
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                'encrypted' => decrypt($setting->value),
                default => $setting->value,
            };
        }
        
        return $settings;
    }

    /**
     * Check if plugin meets system requirements.
     */
    public function meetsRequirements(): array
    {
        $errors = [];

        // Check PHP version
        if ($this->requires_php && version_compare(PHP_VERSION, $this->requires_php, '<')) {
            $errors[] = "Requires PHP {$this->requires_php} or higher (current: " . PHP_VERSION . ")";
        }

        // Check Laravel version
        if ($this->requires_laravel) {
            $laravelVersion = app()->version();
            if (version_compare($laravelVersion, $this->requires_laravel, '<')) {
                $errors[] = "Requires Laravel {$this->requires_laravel} or higher (current: {$laravelVersion})";
            }
        }

        // Check InAllCart version
        if ($this->requires_quixko) {
            $quixkoVersion = config('app.version', '2.0.0');
            if (version_compare($quixkoVersion, $this->requires_quixko, '<')) {
                $errors[] = "Requires InAllCart {$this->requires_quixko} or higher (current: {$quixkoVersion})";
            }
        }

        return $errors;
    }

    /**
     * Check if all dependencies are installed and active.
     */
    public function checkDependencies(): array
    {
        $errors = [];

        foreach ($this->getDependencies() as $dependency) {
            $dependencyPlugin = static::where('name', $dependency)->first();

            if (!$dependencyPlugin) {
                $errors[] = "Required plugin '{$dependency}' is not installed";
            } elseif (!$dependencyPlugin->isActive()) {
                $errors[] = "Required plugin '{$dependency}' is not active";
            }
        }

        return $errors;
    }
}
