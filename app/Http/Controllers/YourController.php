<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Newsletter;
use Illuminate\Support\Facades\Storage;

class YourController extends Controller
{
    public function createPixelImage()
    {
        // Tạo hình ảnh pixel từ dữ liệu base64
        $pixelData = 'R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        $pixelImage = base64_decode($pixelData);

        // Kiểm tra xem thư mục public/imgs đã tồn tại chưa, nếu chưa thì tạo mới
        $imageDirectory = public_path('imgs');
        if (!File::exists($imageDirectory)) {
            File::makeDirectory($imageDirectory, 0777, true);
        }

        // Lưu hình ảnh vào thư mục public/imgs với tên là pixel.png
        $imagePath = public_path('imgs/pixel.png');
        file_put_contents($imagePath, $pixelImage);

        return response()->json(['message' => 'Hình ảnh pixel đã được tạo và lưu thành công.']);
    }
}


