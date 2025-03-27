<?php

namespace Src\App\Models;

use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $table = 'stream';

    public static function generateCode() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x10); // set version 1
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set variant to RFC4122
        $uuid = vsprintf(
            '%08s-%04s-%04s-%04s-%12s',
            unpack('H8a/H4b/H4c/H4d/H12e', $data)
        );
    
        return $uuid;
    }
}