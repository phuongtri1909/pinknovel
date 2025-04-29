<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class BannerController extends Controller
{



    /**
     * Handle banner click and redirect
     */
    public function click(Request $request, Banner $banner)
    {
        // Check if banner has a story
        if ($banner->story_id) {
            // If banner has a story, redirect to story page
            $story = Story::find($banner->story_id);
            if ($story) {
                return redirect()->route('show.page.story', $story->slug);
            }
        }

        // If no story or story not found, use the banner link
        if (!empty($banner->link)) {
            // Direct redirect to external link
            return redirect()->away($banner->link);
        }

        // Fallback to homepage if neither exists
        return redirect()->route('home');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banners = Banner::with('story')->paginate(10);
        return view('admin.pages.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stories = Story::orderBy('title')->get();
        return view('admin.pages.banners.create', compact('stories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $this->validateBanner($request);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validatedData['image'] = $this->processImage($request->file('image'));
        }

        // Handle link requirement based on story_id
        if (empty($validatedData['story_id']) && empty($validatedData['link'])) {
            return back()->withInput()->withErrors(['link' => 'Link là bắt buộc khi không chọn truyện']);
        }

        Banner::create($validatedData);

        return redirect()->route('banners.index')->with('success', 'Banner đã được tạo thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(Banner $banner)
    {
        return view('admin.pages.banners.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Banner $banner)
    {
        $stories = Story::orderBy('title')->get();
        return view('admin.pages.banners.edit', compact('banner', 'stories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Banner $banner)
    {
        $validatedData = $this->validateBanner($request, $banner->id);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image) {
                Storage::delete('public/' . $banner->image);
            }

            $validatedData['image'] = $this->processImage($request->file('image'));
        }

        // Handle link requirement based on story_id
        if (empty($validatedData['story_id']) && empty($validatedData['link'])) {
            return back()->withInput()->withErrors(['link' => 'Link là bắt buộc khi không chọn truyện']);
        }

        $banner->update($validatedData);

        return redirect()->route('banners.index')->with('success', 'Banner đã được cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Banner $banner)
    {
        // Delete image if exists
        if ($banner->image) {
            Storage::delete('public/' . $banner->image);
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner đã được xóa thành công');
    }

    /**
     * Validate banner data
     */
    private function validateBanner(Request $request, $id = null)
    {
        $rules = [
            'image' => $id ? 'nullable|image|mimes:jpeg,png,jpg,gif' : 'required|image|mimes:jpeg,png,jpg,gif',
            'link' => 'nullable|url|max:255',
            'story_id' => 'nullable|exists:stories,id',
            'status' => 'required|boolean',
            'link_aff' => 'nullable|url',
        ];

        $messages = [
            'image.required' => 'Hình ảnh là bắt buộc',
            'image.image' => 'Tập tin phải là hình ảnh',
            'image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg hoặc gif',
            'link.url' => 'Link không hợp lệ',
            'link.max' => 'Link không được vượt quá 255 ký tự',
            'story_id.exists' => 'Truyện không tồn tại',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.boolean' => 'Trạng thái không hợp lệ',
        ];

        return $request->validate($rules, $messages);
    }

    /**
     * Process and optimize the uploaded image
     */
    private function processImage($image)
    {
        $filename = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();
        $path = 'banners/' . $filename;

        // Desktop version (original size)
        $desktopImg = Image::make($image->getRealPath())
            ->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80); // 80% quality

        Storage::put('public/banners/desktop_' . $filename, $desktopImg);

        // Mobile version
        $mobileImg = Image::make($image->getRealPath())
            ->resize(767, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 70); // 70% quality

        Storage::put('public/banners/mobile_' . $filename, $mobileImg);

        // Save original with reduced quality
        $mainImg = Image::make($image->getRealPath())
            ->encode('webp', 90); // 90% quality

        Storage::put('public/' . $path, $mainImg);

        return $path;
    }
}
