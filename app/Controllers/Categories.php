<?php
namespace App\Controllers;
use App\Models\CategoryModel;
class Categories extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
        return view('categories/index', ['title' => 'Kategori', 'items' => (new CategoryModel())->orderBy('id', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
        return view('categories/form', ['title' => 'Tambah Kategori', 'item' => null]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
                $messages = ['name' => ['required' => 'Nama kategori wajib diisi.', 'min_length' => 'Nama kategori minimal 2 karakter.', 'max_length' => 'Nama kategori maksimal 100 karakter.', 'is_unique' => 'Nama kategori {value} sudah dipakai.']];
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]|is_unique[categories.name]', 'description' => 'permit_empty'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $catName = trim((string) $this->request->getPost('name'));
        if ((new CategoryModel())->where('LOWER(name)', strtolower($catName))->first()) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Nama kategori ' . $catName . ' sudah dipakai.']);
        }
        (new CategoryModel())->insert(['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description')]);
        return redirect()->to('/categories')->with('success', 'Kategori berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
        $item = (new CategoryModel())->find($id);
        if (! $item) return redirect()->to('/categories')->with('error', 'Kategori tidak ditemukan.');
        return view('categories/form', ['title' => 'Ubah Kategori', 'item' => $item]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
                $messages = ['name' => ['required' => 'Nama kategori wajib diisi.', 'min_length' => 'Nama kategori minimal 2 karakter.', 'max_length' => 'Nama kategori maksimal 100 karakter.', 'is_unique' => 'Nama kategori {value} sudah dipakai.']];
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[100]|is_unique[categories.name,id,' . $id . ']', 'description' => 'permit_empty'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $catName = trim((string) $this->request->getPost('name'));
        if ((new CategoryModel())->where('LOWER(name)', strtolower($catName))->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Nama kategori ' . $catName . ' sudah dipakai.']);
        }
        (new CategoryModel())->update($id, ['name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description')]);
        return redirect()->to('/categories')->with('success', 'Kategori berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola kategori.')) return $r;
        $hasProducts = \Config\Database::connect()->table('products')->where('category_id', $id)->countAllResults() > 0;
        if ($hasProducts) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh produk di katalog.');
        }

        try {
            (new CategoryModel())->delete($id);
            return redirect()->to('/categories')->with('success', 'Kategori berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/categories')->with('error', 'Kategori tidak bisa dihapus karena masih terikat data lain.');
        }
    }
}
