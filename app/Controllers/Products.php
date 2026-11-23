<?php
namespace App\Controllers;
use App\Models\CategoryModel;
use App\Models\ProductModel;
class Products extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $builder = $db->table('products p')->select('p.*, c.name AS category_name')->join('categories c', 'c.id = p.category_id');

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.code', $q, 'both', null, true)
                ->orLike('p.name', $q, 'both', null, true)
                ->orLike('c.name', $q, 'both', null, true)
            ->groupEnd();
        }

        $total = $builder->countAllResults(false);
        $perPage = 10;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        if ($page > $lastPage) $page = $lastPage;

        $items = $builder->orderBy('p.code', 'ASC')->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        $pager = [
            'page' => $page,
            'lastPage' => $lastPage,
            'total' => $total,
            'perPage' => $perPage,
            'from' => $total ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($total, $page * $perPage),
        ];

        return view('products/index', ['title' => 'Produk', 'items' => $items, 'q' => $q, 'pager' => $pager]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        return view('products/form', ['title' => 'Tambah Produk', 'item' => null, 'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $rules = [
            'code' => 'required|max_length[20]|is_unique[products.code]',
            'name' => 'required|min_length[2]|max_length[150]',
            'category_id' => 'required|integer',
            'unit' => 'required|max_length[20]',
            'purchase_price' => 'required|numeric|greater_than_equal_to[0]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'stock_qty' => 'required|integer|greater_than_equal_to[0]',
            'min_stock' => 'required|integer|greater_than_equal_to[0]',
        ];
        $messages = [
            'code' => ['required' => 'Kode produk wajib diisi.', 'max_length' => 'Kode produk maksimal 20 karakter.', 'is_unique' => 'Kode produk {value} sudah dipakai produk lain.'],
            'name' => ['required' => 'Nama produk wajib diisi.', 'min_length' => 'Nama produk minimal 2 karakter.', 'max_length' => 'Nama produk maksimal 150 karakter.'],
            'category_id' => ['required' => 'Kategori wajib dipilih.', 'integer' => 'Kategori tidak valid.'],
            'unit' => ['required' => 'Satuan wajib diisi.', 'max_length' => 'Satuan maksimal 20 karakter.'],
            'purchase_price' => ['required' => 'Harga beli wajib diisi.', 'numeric' => 'Harga beli harus berupa angka.', 'greater_than_equal_to' => 'Harga beli tidak boleh negatif.'],
            'selling_price' => ['required' => 'Harga jual wajib diisi.', 'numeric' => 'Harga jual harus berupa angka.', 'greater_than_equal_to' => 'Harga jual tidak boleh negatif.'],
            'stock_qty' => ['required' => 'Stok wajib diisi.', 'integer' => 'Stok harus berupa bilangan bulat.', 'greater_than_equal_to' => 'Stok tidak boleh negatif.'],
            'min_stock' => ['required' => 'Stok minimum wajib diisi.', 'integer' => 'Stok minimum harus berupa bilangan bulat.', 'greater_than_equal_to' => 'Stok minimum tidak boleh negatif.'],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $stockQty = (int) $this->request->getPost('stock_qty');
        $id = (new ProductModel())->insert($this->request->getPost(['code', 'name', 'category_id', 'unit', 'purchase_price', 'selling_price', 'stock_qty', 'min_stock']));
        
        // Jika produk baru didaftarkan dengan saldo stok awal, catat ke mutasi stok gudang utama
        if ($stockQty > 0 && $id) {
            $db = \Config\Database::connect();
            $whId = (new \App\Libraries\StockService())->defaultWarehouseId();
            $db->table('stock_movements')->insert([
                'product_id' => $id,
                'warehouse_id' => $whId,
                'movement_type' => 'ADJUSTMENT',
                'reference_type' => 'STOCK_AWAL',
                'reference_id' => 0,
                'qty' => $stockQty,
                'qty_before' => 0,
                'qty_after' => $stockQty,
                'notes' => 'Saldo awal saat pendaftaran produk baru',
                'created_by' => $this->userId(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/products')->with('success', 'Produk berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $item = (new ProductModel())->find($id);
        if (! $item) return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');

        $db = \Config\Database::connect();
        $hasTransactions = $db->table('stock_movements')->where('product_id', $id)->countAllResults() > 0
            || $db->table('purchase_orders')->where('product_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('product_id', $id)->countAllResults() > 0;

        return view('products/form', [
            'title' => 'Ubah Produk',
            'item' => $item,
            'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll(),
            'hasTransactions' => $hasTransactions,
        ]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $item = (new ProductModel())->find($id);
        if (! $item) return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');

        $db = \Config\Database::connect();
        $hasTransactions = $db->table('stock_movements')->where('product_id', $id)->countAllResults() > 0
            || $db->table('purchase_orders')->where('product_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('product_id', $id)->countAllResults() > 0;

        $rules = [
            'name' => 'required|min_length[2]|max_length[150]',
            'category_id' => 'required|integer',
            'unit' => 'required|max_length[20]',
            'purchase_price' => 'required|numeric|greater_than_equal_to[0]',
            'selling_price' => 'required|numeric|greater_than_equal_to[0]',
            'min_stock' => 'required|integer|greater_than_equal_to[0]',
        ];
        $messages = [
            'name' => ['required' => 'Nama produk wajib diisi.', 'min_length' => 'Nama produk minimal 2 karakter.', 'max_length' => 'Nama produk maksimal 150 karakter.'],
            'category_id' => ['required' => 'Kategori wajib dipilih.', 'integer' => 'Kategori tidak valid.'],
            'unit' => ['required' => 'Satuan wajib diisi.', 'max_length' => 'Satuan maksimal 20 karakter.'],
            'purchase_price' => ['required' => 'Harga beli wajib diisi.', 'numeric' => 'Harga beli harus berupa angka.', 'greater_than_equal_to' => 'Harga beli tidak boleh negatif.'],
            'selling_price' => ['required' => 'Harga jual wajib diisi.', 'numeric' => 'Harga jual harus berupa angka.', 'greater_than_equal_to' => 'Harga jual tidak boleh negatif.'],
            'min_stock' => ['required' => 'Stok minimum wajib diisi.', 'integer' => 'Stok minimum harus berupa bilangan bulat.', 'greater_than_equal_to' => 'Stok minimum tidak boleh negatif.'],
        ];

        // Jika belum ada riwayat transaksi, kode produk boleh diedit
        if (! $hasTransactions) {
            $rules['code'] = 'required|max_length[20]|is_unique[products.code,id,' . $id . ']';
            $messages['code'] = ['required' => 'Kode produk wajib diisi.', 'max_length' => 'Kode produk maksimal 20 karakter.', 'is_unique' => 'Kode produk {value} sudah dipakai produk lain.'];
        }

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = $this->request->getPost(['name', 'category_id', 'unit', 'purchase_price', 'selling_price', 'min_stock']);
        if (! $hasTransactions) {
            $updateData['code'] = $this->request->getPost('code');
        }

        (new ProductModel())->update($id, $updateData);
        return redirect()->to('/products')->with('success', 'Produk berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $prod = (new ProductModel())->find($id);
        if (! $prod) return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');

        $db = \Config\Database::connect();
        // 1. Cek riwayat transaksi / mutasi terlebih dahulu
        $hasTransactions = $db->table('stock_movements')->where('product_id', $id)->countAllResults() > 0
            || $db->table('purchase_orders')->where('product_id', $id)->countAllResults() > 0
            || $db->table('sales_orders')->where('product_id', $id)->countAllResults() > 0;

        if ($hasTransactions) {
            return redirect()->to('/products')->with('error', 'Produk tidak bisa dihapus karena sudah memiliki riwayat transaksi atau mutasi stok fisik. Data ini harus dipertahankan untuk jejak audit pembukuan.');
        }

        // 2. Jika belum pernah ada transaksi, pastikan saldo stok fisik = 0
        if ((int) $prod['stock_qty'] > 0) {
            return redirect()->to('/products')->with('error', 'Produk tidak bisa dihapus karena masih tercatat memiliki saldo stok fisik (' . $prod['stock_qty'] . ' ' . $prod['unit'] . ').');
        }

        try {
            (new ProductModel())->delete($id);
            return redirect()->to('/products')->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/products')->with('error', 'Produk tidak bisa dihapus karena masih memiliki keterikatan data lain.');
        }
    }
}
