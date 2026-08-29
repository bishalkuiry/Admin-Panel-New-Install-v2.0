<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function __construct(private StorageService $storage) {}

    /**
     * Upload media file (for TinyMCE editor and media library)
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200|mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm,m4v,mov,avi,mkv,mp3,wav,ogg,pdf,doc,docx,xls,xlsx,zip'
        ]);

        $file = $request->file('file');
        $mimeType = $file->getMimeType();

        $folder = match(true) {
            str_starts_with($mimeType, 'image/') => 'uploads/images',
            str_starts_with($mimeType, 'video/') => 'uploads/videos',
            str_starts_with($mimeType, 'audio/') => 'uploads/audio',
            default                               => 'uploads/files',
        };

        $path = $this->storage->store($file, $folder);

        return response()->json([
            'location'      => $this->storage->url($path),
            'path'          => $path,
            'filename'      => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'mime_type'     => $mimeType,
        ]);
    }

    /**
     * List uploaded media files from local public disk only (prevents AWS S3 latency/hanging).
     */
    public function index(Request $request)
    {
        $selectMode = $request->get('select', false);
        $type = $request->get('type', 'all');
        $files = [];

        // Always list local media from local 'public' disk
        $disk = Storage::disk('public');

        // Fetch files from upload folders
        $rawFiles = array_merge(
            $disk->allFiles('uploads'),
            $disk->allFiles('products'),
            $disk->allFiles('stores'),
            $disk->allFiles('categories'),
            $disk->allFiles('app-settings'),
            $disk->allFiles('onboarding')
        );

        if (empty($rawFiles)) {
            try {
                $rawFiles = $disk->allFiles();
            } catch (\Throwable $e) {
                $rawFiles = [];
            }
        }

        $allowedCategories = [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'videos' => ['mp4', 'webm', 'm4v', 'mov', 'avi', 'mkv'],
            'audio'  => ['mp3', 'wav', 'ogg'],
            'files'  => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'],
        ];

        foreach ($rawFiles as $file) {
            $filename = basename($file);
            if (str_starts_with($filename, '.')) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            $itemType = null;
            foreach ($allowedCategories as $cat => $exts) {
                if (in_array($ext, $exts, true)) {
                    $itemType = $cat;
                    break;
                }
            }

            if (!$itemType) {
                continue;
            }

            if ($type !== 'all' && $type !== $itemType) {
                continue;
            }

            try {
                $size = $disk->size($file);
            } catch (\Throwable $e) {
                $size = 0;
            }

            try {
                $modified = $disk->lastModified($file);
            } catch (\Throwable $e) {
                $modified = 0;
            }

            $files[] = [
                'name'     => $filename,
                'path'     => $file,
                'url'      => $disk->url($file),
                'size'     => $size,
                'type'     => $itemType,
                'folder'   => dirname($file) === '.' ? 'root' : dirname($file),
                'modified' => $modified,
            ];
        }

        usort($files, fn($a, $b) => $b['modified'] - $a['modified']);

        if ($request->wantsJson()) {
            return response()->json(['files' => $files]);
        }

        if ($selectMode) {
            return view('admin.media.select', compact('files'));
        }

        return view('admin.media.index', compact('files', 'type'));
    }

    /**
     * Delete a media file from the local disk
     */
    public function destroy(Request $request)
    {
        $request->validate(['path' => 'required|string']);

        $path = $this->storage->normalisePath($request->path);

        // Guard against path traversal — only allow paths inside known upload folders
        $allowed = ['uploads/', 'products/', 'stores/', 'categories/', 'app-settings/', 'onboarding/'];
        $isAllowed = collect($allowed)->contains(fn($prefix) => str_starts_with($path, $prefix));

        if (!$isAllowed) {
            return response()->json(['success' => false, 'message' => 'Invalid file path'], 422);
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true]);
        }

        if ($this->storage->exists($path)) {
            $this->storage->delete($path);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'File not found'], 404);
    }
}
