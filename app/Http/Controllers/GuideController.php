<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GuideController extends Controller
{
    /**
     * Display the guide page.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $guide = Cache::remember('guide', 3600, function () {
            return Guide::published()->latest()->first();
        });

        return view('pages.guide.show', compact('guide'));
    }

    /**
     * Show the form for editing the guide.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $guide = Guide::latest()->first();
        
        return view('admin.pages.guide.edit', compact('guide'));
    }

    /**
     * Update the guide in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'meta_description' => 'nullable|string|max:255',
            'meta_keywords' => 'nullable|string|max:255',
        ],[
            'title.required' => 'Tiêu đề là bắt buộc.',
            'title.string' => 'Tiêu đề phải là chuỗi ký tự.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'content.required' => 'Nội dung là bắt buộc.',
            'meta_description.max' => 'Mô tả meta không được vượt quá 255 ký tự.',
            'meta_keywords.max' => 'Từ khóa meta không được vượt quá 255 ký tự.',
        ]);

        $validated['is_published'] = $request->has('is_published');
        
        $guide = Guide::latest()->first();
        
        if ($guide) {
            $guide->update($validated);
        } else {
            Guide::create($validated);
        }

        // Clear the cache
        Cache::forget('guide');
        
        return redirect()->route('admin.guide.edit')->with('success', 'Hướng dẫn đã được cập nhật thành công.');
    }
    
    /**
     * Get the guide content.
     *
     * @return mixed
     */
    public static function getGuide()
    {
        return Cache::remember('guide', 3600, function () {
            return Guide::published()->latest()->first();
        });
    }
} 