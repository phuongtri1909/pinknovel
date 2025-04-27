<?php

namespace App\Http\Controllers;

use App\Models\Donate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class DonateController extends Controller
{
    /**
     * Show the form for editing the donation information.
     */
    public function edit()
    {
        // Get the first donate record or create a new one if none exists
        $donate = Donate::first() ?? new Donate();
        
        return view('admin.pages.donate.edit', compact('donate'));
    }

    /**
     * Update the donation information.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_qr' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'about_us' => 'nullable|string',
        ],[
            'image_qr.image' => 'Hình ảnh phải là định dạng ảnh',
            'image_qr.mimes' => 'Hình ảnh phải là định dạng jpeg, png, jpg hoặc gif',
            'image_qr.max' => 'Hình ảnh không được lớn hơn 2MB',
            'title.required' => 'Tiêu đề không được để trống',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'description.string' => 'Mô tả phải là chuỗi ký tự',
            'about_us.string' => 'Về chúng tôi phải là chuỗi ký tự',
        ]);
        
        // Get existing record or create new
        $donate = Donate::first();
        if (!$donate) {
            $donate = new Donate();
        }
        
        // Update text fields
        $donate->title = $validatedData['title'];
        $donate->description = $validatedData['description'];
        $donate->about_us = $validatedData['about_us'];
        
        // Handle QR code image upload
        if ($request->hasFile('image_qr')) {
            // Delete old image if exists
            if ($donate->image_qr) {
                Storage::delete('public/' . $donate->image_qr);
            }
            
            // Process and save new image
            $qrPath = $this->processQRImage($request->file('image_qr'));
            $donate->image_qr = $qrPath;
        }
        
        $donate->save();
        
        return redirect()->route('donate.edit')->with('success', 'Thông tin donate đã được cập nhật thành công');
    }
    
    /**
     * Process and optimize the uploaded QR code image
     */
    private function processQRImage($image)
    {
        // Create directory if not exists
        if (!Storage::exists('public/donate')) {
            Storage::makeDirectory('public/donate');
        }
        
        $filename = 'qr_code_' . time() . '.webp';
        $path = 'donate/' . $filename;
        
        // Process image - keep reasonable size for QR code readability
        $img = Image::make($image->getRealPath())
            ->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 90); // Convert to WebP with 90% quality for QR readability
        
        Storage::put('public/' . $path, $img);
        
        return $path;
    }
}