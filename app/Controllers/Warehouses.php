<?php
namespace App\Controllers;
use App\Models\WarehouseModel;
class Warehouses extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat daftar gudang.')) return $r;
        return view('warehouses/index', ['title' => 'Gudang', 'items' => (new WarehouseModel())->orderBy('code', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        return view('warehouses/form', ['title' => 'Tambah Gudang', 'item' => null]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $code = strtoupper(preg_replace('/\s+/', '', trim((string) $this->request->getPost('code'))) ?? '');
        $this->request->setGlobal('post', array_merge($this->request->getPost(), ['code' => $code]));
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

        $db = \Config\Database::connect();
        $hasTransactions = $db->table('stock_movements')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('goods_receipts')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('warehouse_id', $id)->countAllResults() > 0;

        return view('warehouses/form', [
            'title' => 'Ubah Gudang',
            'item' => $item,
            'hasTransactions' => $hasTransactions,
        ]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola gudang.')) return $r;
        $item = (new WarehouseModel())->find($id);
        if (! $item) return redirect()->to('/warehouses')->with('error', 'Gudang tidak ditemukan.');

        $db = \Config\Database::connect();
        $hasTransactions = $db->table('stock_movements')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('goods_receipts')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('warehouse_id', $id)->countAllResults() > 0;

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'location' => 'permit_empty|max_length[150]',
        ];
        $messages = [
            'name' => ['required' => 'Nama gudang wajib diisi.', 'min_length' => 'Nama gudang minimal 2 karakter.', 'max_length' => 'Nama gudang maksimal 100 karakter.'],
        ];

        if (! $hasTransactions) {
            $code = strtoupper(preg_replace('/\s+/', '', trim((string) $this->request->getPost('code'))) ?? '');
            $this->request->setGlobal('post', array_merge($this->request->getPost(), ['code' => $code]));
            $rules['code'] = 'required|max_length[20]|alpha_dash|is_unique[warehouses.code,id,' . $id . ']';
            $messages['code'] = ['required' => 'Kode gudang wajib diisi.', 'max_length' => 'Kode gudang maksimal 20 karakter.', 'alpha_dash' => 'Kode gudang hanya boleh huruf, angka, tanda hubung, dan garis bawah.', 'is_unique' => 'Kode gudang {value} sudah dipakai.'];
        }

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $isDefault = (bool) $this->request->getPost('is_default');
        if ($item['is_default'] && ! $isDefault) {
            return redirect()->back()->withInput()->with('error', 'Gudang utama tidak bisa dicabut status utamanya. Jadikan gudang lain sebagai utama dulu.');
        }

        $db->transBegin();
        try {
            if ($isDefault) {
                $db->table('warehouses')->where('id <>', (int) $id)->update(['is_default' => false]);
            }
            $updateData = ['name' => $this->request->getPost('name'), 'location' => $this->request->getPost('location'), 'is_default' => $isDefault];
            if (! $hasTransactions) {
                $updateData['code'] = strtoupper((string) $this->request->getPost('code'));
            }
            (new WarehouseModel())->update($id, $updateData);
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

        $db = \Config\Database::connect();
        $hasTransactions = $db->table('stock_movements')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('goods_receipts')->where('warehouse_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('warehouse_id', $id)->countAllResults() > 0;

        if ($hasTransactions) {
            return redirect()->to('/warehouses')->with('error', 'Gudang tidak bisa dihapus karena sudah memiliki riwayat mutasi stok fisik atau transaksi.');
        }

        try {
            (new WarehouseModel())->delete($id);
            return redirect()->to('/warehouses')->with('success', 'Gudang berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/warehouses')->with('error', 'Gudang tidak bisa dihapus karena masih terikat dengan data lain.');
        }
    }
}
