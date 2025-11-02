<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chapter Password Encryption Key
    |--------------------------------------------------------------------------
    |
    | Key này dùng để mã hóa/giải mã mật khẩu chương. Key này độc lập
    | với APP_KEY, nên khi đổi APP_KEY thì vẫn decrypt được mật khẩu cũ.
    |
    | Lưu ý: Không được đổi key này sau khi đã có dữ liệu, nếu không sẽ
    | không thể giải mã mật khẩu cũ.
    |
    | Độ dài key nên là 32 ký tự (256 bit) để đảm bảo bảo mật tốt nhất.
    | Có thể generate bằng: openssl rand -hex 32
    |
    */

    'password_key' => env('CHAPTER_PASSWORD_KEY', ''),
    
    'cipher' => 'AES-256-CBC',
];

