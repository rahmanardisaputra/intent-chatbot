# 🤖 Laravel AI Chatbot Starter Pack

Starter pack chatbot modular berbasis **Laravel** dan **9Router (OpenAI-compatible)** menggunakan arsitektur **Native Tool / Function Calling**. 

Sangat simpel, minim kode, dan mudah di-clone untuk kebutuhan berbagai project (sekolah, e-commerce, rumah sakit, dll).

---

## 🚀 Fitur Utama

1. **Zero External AI SDK**: Hanya menggunakan HTTP Client bawaan Laravel (`Illuminate\Support\Facades\Http`).
2. **Modular Actions**: Tambah kemampuan baru chatbot cukup buat 1 file Action class dan daftarkan di `config/chatbot.php`.
3. **Automatic Function Calling**: AI otomatis memilih action yang relevan, mengekstrak parameter, dan menerjemahkan data JSON menjadi bahasa alami manusia.
4. **Ready-to-use Chat UI**: Dilengkapi tampilan web chat yang modern, responsif, dan rapi tanpa perlu kompilasi build step (Tailwind CDN + Alpine.js).

---

## 🛠️ Cara Mulai Cepat

### 1. Clone & Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 2. Atur Kredensial AI di `.env`
Buka file `.env` dan sesuaikan:
```env
NINEROUTER_API_KEY=your_api_key_here
NINEROUTER_BASE_URL=https://api.9router.com/v1 # atau url endpoint 9router kamu
NINEROUTER_MODEL=gpt-4o-mini # atau model yang kamu gunakan
CHATBOT_SYSTEM_PROMPT="Kamu adalah asisten AI yang ramah, sopan, dan solutif."
```

### 3. Jalankan Aplikasi
```bash
php artisan serve
```
Buka browser di `http://localhost:8000`.

---

## 🧩 Cara Menambah Kemampuan / Action Baru

Setiap kemampuan baru dibuat sebagai class turunan dari `App\Chatbot\Action`.

### 1. Buat File Action di `app/Chatbot/Actions/`
Contoh: `app/Chatbot/Actions/CekStokBarangAction.php`

```php
<?php

namespace App\Chatbot\Actions;

use App\Chatbot\Action;

class CekStokBarangAction extends Action
{
    // Nama fungsi yang dikenali AI
    public function name(): string
    {
        return 'cek_stok_barang';
    }

    // Deskripsi agar AI tahu kapan harus memanggil fungsi ini
    public function description(): string
    {
        return 'Mengecek sisa stok produk berdasarkan nama barang.';
    }

    // Parameter yang perlu diekstrak oleh AI dari chat user
    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'nama_barang' => [
                    'type' => 'string',
                    'description' => 'Nama produk/barang yang ingin dicari',
                ],
            ],
            'required' => ['nama_barang'],
        ];
    }

    // Eksekusi data (bisa panggil Model Eloquent / Database Query)
    public function handle(array $params): mixed
    {
        $nama = $params['nama_barang'];
        
        // Contoh query:
        // $produk = \App\Models\Product::where('name', 'like', "%$nama%")->first();

        return [
            'nama_barang' => $nama,
            'stok' => 12,
            'lokasi_rak' => 'Gudang A-2',
        ];
    }
}
```

### 2. Daftarkan di `config/chatbot.php`
```php
'actions' => [
    \App\Chatbot\Actions\GetStudentCountAction::class,
    \App\Chatbot\Actions\CekStokBarangAction::class, // <-- Tambahkan di sini
],
```

**Selesai!** AI sekarang otomatis dapat menjawab pertanyaan seputar stok barang dan merangkum hasilnya ke bahasa manusia yang ramah.
