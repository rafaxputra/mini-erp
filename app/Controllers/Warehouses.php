<?php
namespace App\Controllers;
use App\Models\WarehouseModel;
class Warehouses extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        return view('warehouses/index', ['title' => 'Gudang', 'items' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        return view('warehouses/form', ['title' => 'Tambah Gudang', 'item' => null]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $messages = ['code' => ['required' => 'Kode gudang wajib diisi.', 'max_length' => 'Kode gudang maksimal 20 karakter.', 'alpha_dash' => 'Kode gudang hanya boleh huruf, angka, tanda hubung, dan garis bawah.', 'is_unique' => 'Kode gudang {value} sudah dipakai.'], 'name' => ['required' => 'Nama gudang wajib diisi.', 'min_length' => 'Nama gudang minimal 2 karakter.', 'max_length' => 'Nama gudang maksimal 100 karakter.']];
        if (! $this->validate(['code' => 'required|max_length[20]|alpha_dash|is_unique[warehouses.code]', 'name' => 'required|min_length[2]|max_length[100]', 'location' => 'permit_empty|max_length[150]'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $isDefault = (bool) $this->request->getPost('is_default');
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if ($isDefault) {
                $db->table('warehouses')->update(['is_default' => false]);
            }
            (new WarehouseModel())->insert(['code' => strtoupper((string) $this->request->getPost('code')), 'name' => $this->request->getPost('name'), 'location' => $this->request->getPost('location'), 'is_default' => $isDefault]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan gudang.');
        }
        return redirect()->to('/warehouses')->with('success', 'Gudang berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $item = (new WarehouseModel())->find($id);
        if (! $item) return redirect()->to('/warehouses')->with('error', 'Gudang tidak ditemukan.');
        return view('warehouses/form', ['title' => 'Ubah Gudang', 'item' => $item]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $item = (new WarehouseModel())->find($id);
        if (! $item) return redirect()->to('/warehouses')->with('error', 'Gudang tidak ditemukan.');
        $messages = ['code' => ['required' => 'Kode gudang wajib diisi.', 'max_length' => 'Kode gudang maksimal 20 karakter.', 'alpha_dash' => 'Kode gudang hanya boleh huruf, angka, tanda hubung, dan garis bawah.', 'is_unique' => 'Kode gudang {value} sudah dipakai.'], 'name' => ['required' => 'Nama gudang wajib diisi.', 'min_length' => 'Nama gudang minimal 2 karakter.', 'max_length' => 'Nama gudang maksimal 100 karakter.']];
        if (! $this->validate(['code' => 'required|max_length[20]|alpha_dash|is_unique[warehouses.code,id,' . $id . ']', 'name' => 'required|min_length[2]|max_length[100]', 'location' => 'permit_empty|max_length[150]'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $isDefault = (bool) $this->request->getPost('is_default');
        if ($item['is_default'] && ! $isDefault) {
            return redirect()->back()->withInput()->with('error', 'Gudang utama tidak bisa dicabut status utamanya. Jadikan gudang lain sebagai utama dulu.');
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            if ($isDefault) {
                $db->table('warehouses')->where('id <>', (int) $id)->update(['is_default' => false]);
            }
            (new WarehouseModel())->update($id, ['code' => strtoupper((string) $this->request->getPost('code')), 'name' => $this->request->getPost('name'), 'location' => $this->request->getPost('location'), 'is_default' => $isDefault]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal mengubah gudang.');
        }
        return redirect()->to('/warehouses')->with('success', 'Gudang berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $item = (new WarehouseModel())->find($id);
        if (! $item) return redirect()->to('/warehouses')->with('error', 'Gudang tidak ditemukan.');
        if ($item['is_default']) {
            return redirect()->to('/warehouses')->with('error', 'Gudang utama tidak bisa dihapus.');
        }
        try {
            (new WarehouseModel())->delete($id);
            return redirect()->to('/warehouses')->with('success', 'Gudang berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/warehouses')->with('error', 'Gudang tidak bisa dihapus karena sudah dipakai transaksi atau mutasi stok.');
        }
    }
}
