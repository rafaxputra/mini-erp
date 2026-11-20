<?php
namespace App\Controllers;
use App\Models\CustomerModel;
class Customers extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'sales', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat daftar customer.')) return $r;
        return view('customers/index', ['title' => 'Pelanggan', 'items' => (new CustomerModel())->orderBy('code', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/customers', 'Hanya Staff Sales dan Admin yang bisa mengelola customer.')) return $r;
        return view('customers/form', ['title' => 'Tambah Pelanggan', 'item' => null]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/customers', 'Hanya Staff Sales dan Admin yang bisa mengelola customer.')) return $r;
        $rules = ['code' => 'required|max_length[20]|is_unique[customers.code]', 'name' => 'required|min_length[2]|max_length[150]', 'phone' => 'permit_empty|max_length[30]', 'email' => 'permit_empty|valid_email|max_length[150]', 'address' => 'permit_empty'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian. Kode harus unik dan email harus valid.');
        }
        (new CustomerModel())->insert($this->request->getPost(['code', 'name', 'phone', 'email', 'address']));
        return redirect()->to('/customers')->with('success', 'Pelanggan berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/customers', 'Hanya Staff Sales dan Admin yang bisa mengelola customer.')) return $r;
        $item = (new CustomerModel())->find($id);
        if (! $item) return redirect()->to('/customers')->with('error', 'Pelanggan tidak ditemukan.');
        return view('customers/form', ['title' => 'Ubah Pelanggan', 'item' => $item]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/customers', 'Hanya Staff Sales dan Admin yang bisa mengelola customer.')) return $r;
        $rules = ['code' => 'required|max_length[20]|is_unique[customers.code,id,' . $id . ']', 'name' => 'required|min_length[2]|max_length[150]', 'phone' => 'permit_empty|max_length[30]', 'email' => 'permit_empty|valid_email|max_length[150]', 'address' => 'permit_empty'];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Periksa kembali isian. Kode harus unik dan email harus valid.');
        }
        (new CustomerModel())->update($id, $this->request->getPost(['code', 'name', 'phone', 'email', 'address']));
        return redirect()->to('/customers')->with('success', 'Pelanggan berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/customers', 'Hanya Staff Sales dan Admin yang bisa mengelola customer.')) return $r;
        try {
            (new CustomerModel())->delete($id);
            return redirect()->to('/customers')->with('success', 'Pelanggan berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/customers')->with('error', 'Pelanggan tidak bisa dihapus karena sudah dipakai transaksi.');
        }
    }
}
