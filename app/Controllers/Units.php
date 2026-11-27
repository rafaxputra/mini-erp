<?php
namespace App\Controllers;

use App\Models\UnitModel;

class Units extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan mengelola satuan.')) return $r;
        return view('units/index', [
            'title' => 'Satuan Produk',
            'items' => (new UnitModel())->orderBy('id', 'ASC')->findAll(),
            'isAdmin' => in_array(session()->get('user_role'), ['admin'], true),
        ]);
    }

    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa menambah satuan.')) return $r;
        return view('units/form', ['title' => 'Tambah Satuan', 'item' => null]);
    }

    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa menambah satuan.')) return $r;
        $code = strtolower(preg_replace('/\s+/', '', trim((string) $this->request->getPost('code'))) ?? '');
        $this->request->setGlobal('post', array_merge($this->request->getPost(), ['code' => $code]));

        $messages = [
            'code' => [
                'required' => 'Kode satuan wajib diisi.',
                'alpha_dash' => 'Kode satuan hanya boleh huruf, angka, tanda hubung, dan garis bawah.',
                'max_length' => 'Kode satuan maksimal 20 karakter.',
                'is_unique' => 'Kode satuan {value} sudah digunakan.',
            ],
            'name' => [
                'required' => 'Nama satuan wajib diisi.',
                'min_length' => 'Nama satuan minimal 2 karakter.',
                'max_length' => 'Nama satuan maksimal 100 karakter.',
            ],
        ];

        if (! $this->validate([
            'code' => 'required|alpha_dash|max_length[20]|is_unique[units.code]',
            'name' => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
        ], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new UnitModel())->insert([
            'code' => strtolower(trim((string) $this->request->getPost('code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
        ]);

        return redirect()->to('/units')->with('success', 'Satuan baru berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengubah satuan.')) return $r;
        $item = (new UnitModel())->find($id);
        if (! $item) return redirect()->to('/units')->with('error', 'Satuan tidak ditemukan.');
        return view('units/form', ['title' => 'Ubah Satuan', 'item' => $item]);
    }

    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengubah satuan.')) return $r;
        $code = strtolower(preg_replace('/\s+/', '', trim((string) $this->request->getPost('code'))) ?? '');
        $this->request->setGlobal('post', array_merge($this->request->getPost(), ['code' => $code]));

        $messages = [
            'code' => [
                'required' => 'Kode satuan wajib diisi.',
                'alpha_dash' => 'Kode satuan hanya boleh huruf, angka, tanda hubung, dan garis bawah.',
                'max_length' => 'Kode satuan maksimal 20 karakter.',
                'is_unique' => 'Kode satuan {value} sudah digunakan.',
            ],
            'name' => [
                'required' => 'Nama satuan wajib diisi.',
                'min_length' => 'Nama satuan minimal 2 karakter.',
                'max_length' => 'Nama satuan maksimal 100 karakter.',
            ],
        ];

        if (! $this->validate([
            'code' => 'required|alpha_dash|max_length[20]|is_unique[units.code,id,' . $id . ']',
            'name' => 'required|min_length[2]|max_length[100]',
            'description' => 'permit_empty|max_length[255]',
        ], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = strtolower(trim((string) $this->request->getPost('code')));
        (new UnitModel())->update($id, [
            'code' => $code,
            'name' => trim((string) $this->request->getPost('name')),
            'description' => trim((string) $this->request->getPost('description')),
        ]);

        // Sinkronisasi teks unit di tabel products yang terhubung
        \Config\Database::connect()->table('products')->where('unit_id', $id)->update(['unit' => $code]);

        return redirect()->to('/units')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function delete($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa menghapus satuan.')) return $r;

        $hasProducts = \Config\Database::connect()->table('products')->where('unit_id', $id)->countAllResults() > 0;
        if ($hasProducts) {
            return redirect()->to('/units')->with('error', 'Satuan tidak bisa dihapus karena masih digunakan oleh produk di katalog.');
        }

        try {
            (new UnitModel())->delete($id);
            return redirect()->to('/units')->with('success', 'Satuan berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/units')->with('error', 'Satuan tidak bisa dihapus karena masih terikat data lain.');
        }
    }
}
