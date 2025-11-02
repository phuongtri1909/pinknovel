<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class AdminGuideController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guides = Guide::latest()->paginate(15);
        return view('admin.pages.guide.index', compact('guides'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pages.guide.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'content.required' => 'Nội dung là bắt buộc.',
            'meta_description.max' => 'Mô tả meta không được vượt quá 255 ký tự.',
            'meta_keywords.max' => 'Từ khóa meta không được vượt quá 255 ký tự.',
        ]);

        // Generate slug from title
        $slug = Str::slug($validated['title']);
        $originalSlug = $slug;
        $counter = 1;
        while (Guide::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        $validated['slug'] = $slug;

        $validated['is_published'] = $request->has('is_published');

        // Process images in content - move from temp to main folder
        $content = $this->processContentImages($validated['content']);
        $validated['content'] = $content;

        $guide = Guide::create($validated);

        return redirect()->route('admin.guides.index')->with('success', 'Hướng dẫn đã được tạo thành công.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $guide = Guide::findOrFail($id);
        return view('admin.pages.guide.show', compact('guide'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $guide = Guide::findOrFail($id);
        return view('admin.pages.guide.edit', compact('guide'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $guide = Guide::findOrFail($id);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:guides,slug,' . $guide->id,
            'content' => 'required',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
            'content.required' => 'Nội dung là bắt buộc.',
            'meta_description.max' => 'Mô tả meta không được vượt quá 255 ký tự.',
            'meta_keywords.max' => 'Từ khóa meta không được vượt quá 255 ký tự.',
        ]);
        
        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $slug = Str::slug($validated['title']);
            $originalSlug = $slug;
            $counter = 1;
            while (Guide::where('slug', $slug)->where('id', '!=', $guide->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        $validated['is_published'] = $request->has('is_published');

        // Process images in content - move from temp to main folder
        $content = $this->processContentImages($validated['content'], $guide->id);
        $validated['content'] = $content;

        // Get old images before update
        $oldContent = $guide->content;
        $oldImages = $this->extractImagesFromContent($oldContent);
        $newImages = $this->extractImagesFromContent($validated['content']);

        $guide->update($validated);

        // Delete unused images
        $this->deleteUnusedImages($oldImages, $newImages);

        return redirect()->route('admin.guides.index')->with('success', 'Hướng dẫn đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $guide = Guide::findOrFail($id);
        
        // Extract images from content and delete them
        $images = $this->extractImagesFromContent($guide->content);
        foreach ($images as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $guide->delete();

        return redirect()->route('admin.guides.index')->with('success', 'Hướng dẫn đã được xóa thành công.');
    }

    /**
     * Upload image for CKEditor (temporary storage)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'upload' => 'required|image|max:5120', // 5MB max
        ]);

        try {
            $image = $request->file('upload');
            $now = \Carbon\Carbon::now();
            $yearMonth = $now->format('Y/m');
            $timestamp = $now->format('YmdHis');
            $randomString = Str::random(8);
            $fileName = "{$timestamp}_{$randomString}";

            // Create directories if they don't exist
            Storage::disk('public')->makeDirectory("guides/temp/{$yearMonth}");

            // Process and save image with reduced quality
            $img = Image::make($image->getRealPath());
            
            // Resize if too large (max width 1200px)
            if ($img->width() > 1200) {
                $img->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            // Convert to WebP with 85% quality
            $img->encode('webp', 85);
            
            $tempPath = "guides/temp/{$yearMonth}/{$fileName}.webp";
            Storage::disk('public')->put($tempPath, $img->stream());

            $url = Storage::url($tempPath);
            $fullUrl = asset($url);

            return response()->json([
                'uploaded' => true,
                'url' => $fullUrl,
                'tempPath' => $tempPath // Store temp path for later processing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'uploaded' => false,
                'error' => [
                    'message' => 'Có lỗi xảy ra khi upload hình ảnh: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Process content images - move from temp to main folder
     */
    private function processContentImages($content, $guideId = null)
    {
        // Extract all image URLs from content
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        if (empty($matches[1])) {
            return $content;
        }

        $now = \Carbon\Carbon::now();
        $yearMonth = $now->format('Y/m');
        $guideFolder = $guideId ? "guides/{$guideId}" : "guides/{$now->format('YmdHis')}";

        foreach ($matches[1] as $imageUrl) {
            // Check if image is in temp folder
            if (strpos($imageUrl, '/storage/guides/temp/') !== false) {
                // Extract temp path
                $tempPath = str_replace(asset('/storage/'), '', $imageUrl);
                $tempPath = str_replace('/storage/', '', $tempPath);
                $tempFullPath = storage_path('app/public/' . $tempPath);

                if (Storage::disk('public')->exists($tempPath)) {
                    // Create main directory
                    Storage::disk('public')->makeDirectory("{$guideFolder}/{$yearMonth}");
                    
                    // Get filename from temp path
                    $fileName = basename($tempPath);
                    $newPath = "{$guideFolder}/{$yearMonth}/{$fileName}";
                    
                    // Move image from temp to main folder
                    Storage::disk('public')->move($tempPath, $newPath);
                    
                    // Update content with new URL
                    $newUrl = Storage::url($newPath);
                    $content = str_replace($imageUrl, asset($newUrl), $content);
                }
            }
        }

        return $content;
    }

    /**
     * Extract image paths from content
     */
    private function extractImagesFromContent($content)
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        $images = [];
        foreach ($matches[1] as $url) {
            // Extract path from URL
            if (strpos($url, '/storage/') !== false) {
                $path = str_replace(asset('/storage/'), '', $url);
                $path = str_replace('/storage/', '', $path);
                if (!empty($path)) {
                    $images[] = $path;
                }
            }
        }
        
        return $images;
    }

    /**
     * Delete unused images
     */
    private function deleteUnusedImages($oldImages, $newImages)
    {
        $imagesToDelete = array_diff($oldImages, $newImages);
        
        foreach ($imagesToDelete as $imagePath) {
            // Don't delete temp images
            if (strpos($imagePath, 'guides/temp/') === false && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
                
                // Try to delete parent directory if empty
                $dir = dirname($imagePath);
                if (count(Storage::disk('public')->files($dir)) === 0 && count(Storage::disk('public')->directories($dir)) === 0) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }
        }
    }
}
