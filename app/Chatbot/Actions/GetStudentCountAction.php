<?php

namespace App\Chatbot\Actions;

use App\Chatbot\Action;

class GetStudentCountAction extends Action
{
    public function name(): string
    {
        return 'get_student_count';
    }

    public function description(): string
    {
        return 'Mengambil jumlah data siswa terdaftar. Dapat difilter berdasarkan kelas atau jurusan jika user menyebutkannya.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'kelas' => [
                    'type' => 'string',
                    'description' => 'Tingkat kelas (contoh: 10, 11, 12). Kosongkan jika user menanyakan total semua kelas.',
                ],
                'jurusan' => [
                    'type' => 'string',
                    'description' => 'Nama jurusan (contoh: RPL, TKJ, Akuntansi). Kosongkan jika tidak spesifik jurusan.',
                ],
            ],
            'required' => [],
        ];
    }

    public function handle(array $params): mixed
    {
        $kelas = $params['kelas'] ?? 'Semua Kelas';
        $jurusan = $params['jurusan'] ?? 'Semua Jurusan';

        // CONTOH DATA DUMMY:
        // Di aplikasi nyata, kamu bisa ganti dengan query DB, misalnya:
        // Student::when($kelas, ...)->count();
        $total = match (strtoupper($jurusan)) {
            'RPL' => 75,
            'TKJ' => 64,
            default => ($kelas !== 'Semua Kelas') ? 35 : 320,
        };

        return [
            'total_siswa' => $total,
            'kelas' => $kelas,
            'jurusan' => $jurusan,
            'status' => 'aktif',
            'tahun_ajaran' => '2025/2026',
        ];
    }
}
