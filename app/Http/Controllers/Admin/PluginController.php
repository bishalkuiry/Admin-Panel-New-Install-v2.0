<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PluginController extends Controller
{
    protected PluginService $pluginService;

    public function __construct(PluginService $pluginService)
    {
        $this->pluginService = $pluginService;
    }

    /**
     * Display list of all plugins
     */
    public function index(Request $request)
    {
        $query = Plugin::query();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('display_name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $plugins = $query->orderBy('display_name')->paginate(20);

        return view('admin.plugins.index', compact('plugins'));
    }

    /**
     * Show plugin upload form
     */
    public function create()
    {
        return view('admin.plugins.upload');
    }

    /**
     * Upload and install plugin
     */
    public function store(Request $request)
    {
        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:51200', // 50MB max
            'license_key' => 'nullable|string|max:255',
            'overwrite'   => 'nullable|boolean',
        ]);

        try {
            $result = $this->pluginService->uploadPlugin(
                $request->file('plugin_file'),
                $request->license_key,
                $request->boolean('overwrite')
            );

            // Auto-install after upload
            $this->pluginService->installPlugin($result['plugin']);

            $msg = $result['message'] ?? 'Plugin uploaded and installed successfully.';

            return redirect()
                ->route('admin.plugins.show', $result['plugin'])
                ->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Plugin upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Plugin upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Show plugin details
     */
    public function show(Plugin $plugin)
    {
        $plugin->load('pluginHooks', 'pluginSettings');
        
        // Get requirements status
        $requirementErrors = $plugin->meetsRequirements();
        $dependencyErrors = $plugin->checkDependencies();

        return view('admin.plugins.show', compact('plugin', 'requirementErrors', 'dependencyErrors'));
    }

    /**
     * Show plugin settings
     */
    public function edit(Plugin $plugin)
    {
        if (!$plugin->isActive()) {
            return redirect()
                ->route('admin.plugins.show', $plugin)
                ->with('error', 'Plugin must be activated before configuring settings.');
        }

        $settings = $plugin->getAllSettings();
        $manifest = $plugin->metadata;

        return view('admin.plugins.settings', compact('plugin', 'settings', 'manifest'));
    }

    /**
     * Update plugin settings
     */
    public function update(Request $request, Plugin $plugin)
    {
        try {
            $settings = $request->input('settings', []);

            foreach ($settings as $key => $value) {
                // Determine type based on value
                $type = match (true) {
                    is_bool($value) => 'boolean',
                    is_int($value) => 'integer',
                    is_array($value) => 'json',
                    str_starts_with($key, 'secret_') || str_starts_with($key, 'password_') => 'encrypted',
                    default => 'string',
                };

                $plugin->setSetting($key, $value, $type);
            }

            return back()->with('success', 'Plugin settings updated successfully.');

        } catch (\Exception $e) {
            Log::error('Plugin settings update failed', [
                'plugin' => $plugin->name,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Activate plugin
     */
    public function activate(Plugin $plugin)
    {
        try {
            $result = $this->pluginService->activatePlugin($plugin);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Plugin activation failed', [
                'plugin' => $plugin->name,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Activation failed: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate plugin
     */
    public function deactivate(Plugin $plugin)
    {
        try {
            $result = $this->pluginService->deactivatePlugin($plugin);

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            Log::error('Plugin deactivation failed', [
                'plugin' => $plugin->name,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Deactivation failed: ' . $e->getMessage());
        }
    }

    /**
     * Uninstall plugin
     */
    public function destroy(Plugin $plugin)
    {
        try {
            $pluginName = $plugin->display_name;
            
            $result = $this->pluginService->uninstallPlugin($plugin, true);

            return redirect()
                ->route('admin.plugins.index')
                ->with('success', "Plugin '{$pluginName}' uninstalled successfully.");

        } catch (\Exception $e) {
            Log::error('Plugin uninstallation failed', [
                'plugin' => $plugin->name,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Uninstallation failed: ' . $e->getMessage());
        }
    }

    /**
     * Update license key
     */
    public function updateLicense(Request $request, Plugin $plugin)
    {
        $request->validate([
            'license_key' => 'required|string|max:255',
        ]);

        try {
            $plugin->update([
                'license_key' => $request->license_key,
                'license_status' => 'valid', // In production, verify with license server
                'license_verified_at' => now(),
            ]);

            return back()->with('success', 'License key updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update license: ' . $e->getMessage());
        }
    }

    /**
     * Sync plugins from filesystem
     */
    public function sync()
    {
        try {
            $synced = $this->pluginService->syncPlugins();

            return back()->with('success', count($synced) . ' plugin(s) synced successfully.');

        } catch (\Exception $e) {
            Log::error('Plugin sync failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Proxy live marketplace feed page from store.oblifo.com
     */
    public function marketplaceFeed(Request $request)
    {
        $url = 'https://store.oblifo.com/inallcart/allplugin';

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(15)
                ->get($url);

            if ($response->successful()) {
                $html = $response->body();
                
                // Inject base tag so relative URLs load from store.oblifo.com
                if (!str_contains($html, '<base')) {
                    $html = str_replace('<head>', "<head>\n<base href=\"https://store.oblifo.com/\">", $html);
                }
                
                return response($html)
                    ->header('Content-Type', 'text/html; charset=UTF-8');
            }
        } catch (\Exception $e) {
            Log::warning('Marketplace feed proxy error', ['error' => $e->getMessage()]);
        }

        return response("
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: system-ui, sans-serif; text-align: center; padding: 60px 20px; background: #f8fafc; color: #334155; }
                    .card { background: white; padding: 40px; border-radius: 16px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
                    a { display: inline-block; margin-top: 20px; padding: 12px 24px; background: #f97316; color: white; border-radius: 8px; text-decoration: none; font-weight: bold; }
                </style>
            </head>
            <body>
                <div class='card'>
                    <h2>Unable to load marketplace feed</h2>
                    <p>Could not fetch live content from {$url}.</p>
                    <a href='{$url}' target='_blank'>Open {$url} in New Tab</a>
                </div>
            </body>
            </html>
        ", 200)->header('Content-Type', 'text/html');
    }
}
