<?php
namespace App\Controllers;
use App\Models\SupplierModel;
class Suppliers extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'purchasing', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat daftar supplier.')) return $r;
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $builder = $db->table('suppliers');
        if ($q !== '') {
            $builder->groupStart()
                ->like('code', $q, 'both', null, true)
                ->orLike('name', $q, 'both', null, true)
                ->orLike('phone', $q, 'both', null, true)
                ->orLike('email', $q, 'both', null, true)
            ->groupEnd();
        }
        $total = $builder->countAllResults(false);
        $perPage = 10;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        if ($page > $lastPage) $page = $lastPage;

        $items = $builder->orderBy('code', 'ASC')->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $pager = [
            'page' => $page,
            'lastPage' => $lastPage,
            'total' => $total,
            'perPage' => $perPage,
            'from' => $total ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($total, $page * $perPage),
        ];

        return view('suppliers/index', ['title' => 'Pemasok', 'items' => $items, 'q' => $q, 'pager' => $pager]);
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
        $messages = [
            'code' => ['required' => 'Kode pemasok wajib diisi.', 'max_length' => 'Kode pemasok maksimal 20 karakter.', 'is_unique' => 'Kode pemasok {value} sudah dipakai.'],
            'name' => ['required' => 'Nama pemasok wajib diisi.', 'min_length' => 'Nama pemasok minimal 2 karakter.', 'max_length' => 'Nama pemasok maksimal 150 karakter.'],
            'phone' => ['max_length' => 'Nomor telepon maksimal 30 karakter.'],
            'email' => ['valid_email' => 'Format email tidak valid.', 'max_length' => 'Email maksimal 150 karakter.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        (new SupplierModel())->insert($this->request->getPost(['code', 'name', 'phone', 'email', 'address']));
        return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        $item = (new SupplierModel())->find($id);
        if (! $item) return redirect()->to('/suppliers')->with('error', 'Pemasok tidak ditemukan.');

        $hasTransactions = \Config\Database::connect()->table('purchase_orders')->where('supplier_id', $id)->countAllResults() > 0;

        return view('suppliers/form', [
            'title' => 'Ubah Pemasok',
            'item' => $item,
            'hasTransactions' => $hasTransactions,
        ]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        $item = (new SupplierModel())->find($id);
        if (! $item) return redirect()->to('/suppliers')->with('error', 'Pemasok tidak ditemukan.');

        $hasTransactions = \Config\Database::connect()->table('purchase_orders')->where('supplier_id', $id)->countAllResults() > 0;

        $rules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'phone' => 'permit_empty|max_length[30]',
            'email' => 'permit_empty|valid_email|max_length[150]',
            'address' => 'permit_empty',
        ];
        $messages = [
            'name' => ['required' => 'Nama pemasok wajib diisi.', 'min_length' => 'Nama pemasok minimal 2 karakter.', 'max_length' => 'Nama pemasok maksimal 150 karakter.'],
            'phone' => ['max_length' => 'Nomor telepon maksimal 30 karakter.'],
            'email' => ['valid_email' => 'Format email tidak valid.', 'max_length' => 'Email maksimal 150 karakter.'],
        ];

        if (! $hasTransactions) {
            $rules['code'] = 'required|max_length[20]|is_unique[suppliers.code,id,' . $id . ']';
            $messages['code'] = ['required' => 'Kode pemasok wajib diisi.', 'max_length' => 'Kode pemasok maksimal 20 karakter.', 'is_unique' => 'Kode pemasok {value} sudah dipakai.'];
        }

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = $this->request->getPost(['name', 'phone', 'email', 'address']);
        if (! $hasTransactions) {
            $updateData['code'] = $this->request->getPost('code');
        }

        (new SupplierModel())->update($id, $updateData);
        return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/suppliers', 'Hanya Staff Purchasing dan Admin yang bisa mengelola supplier.')) return $r;
        
        $hasTransactions = \Config\Database::connect()->table('purchase_orders')->where('supplier_id', $id)->countAllResults() > 0;
        if ($hasTransactions) {
            return redirect()->to('/suppliers')->with('error', 'Pemasok tidak bisa dihapus karena sudah memiliki riwayat transaksi pesanan pembelian (PO).');
        }

        try {
            (new SupplierModel())->delete($id);
            return redirect()->to('/suppliers')->with('success', 'Pemasok berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/suppliers')->with('error', 'Pemasok tidak bisa dihapus karena masih terikat dengan data lain.');
        }
    }
}
