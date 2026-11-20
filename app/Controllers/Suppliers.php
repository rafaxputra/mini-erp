<?php
namespace App\Controllers;
use App\Models\SupplierModel;
class Suppliers extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'purchasing', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat daftar supplier.')) return $r;
        return view('suppliers/index', ['title' => 'Pemasok', 'items' => (new SupplierModel())->orderBy('code', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        return view('suppliers/form', ['title' => 'Tambah Pemasok', 'item' => null]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        $rules = ['code' => 'required|max_length[20]|is_unique[suppliers.code]', 'name' => 'required|min_length[2]|max_length[150]', 'phone' => 'permit_empty|max_length[30]', 'email' => 'permit_empty|valid_email|max_length[150]', 'address' => 'permit_empty'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian. Kode harus unik dan email harus valid.');
        }
        (new SupplierModel())->insert($this->request->getPost(['code', 'name', 'phone', 'email', 'address']));
        return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        $item = (new SupplierModel())->find($id);
        if (! $item) return redirect()->to('/suppliers')->with('error', 'Pemasok tidak ditemukan.');
        return view('suppliers/form', ['title' => 'Ubah Pemasok', 'item' => $item]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        $rules = ['code' => 'required|max_length[20]|is_unique[suppliers.code,id,' . $id . ']', 'name' => 'required|min_length[2]|max_length[150]', 'phone' => 'permit_empty|max_length[30]', 'email' => 'permit_empty|valid_email|max_length[150]', 'address' => 'permit_empty'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian. Kode harus unik dan email harus valid.');
        }
        (new SupplierModel())->update($id, $this->request->getPost(['code', 'name', 'phone', 'email', 'address']));
        return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        try {
            (new SupplierModel())->delete($id);
            return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers')->with('error', 'Pemasok tidak bisa dihapus karena sudah dipakai transaksi.');
        }
    }
}
