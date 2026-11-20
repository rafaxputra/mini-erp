<?php
namespace App\Controllers;
use App\Models\CategoryModel;
use App\Models\ProductModel;
class Products extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $items = $db->table('products p')->select('p.*, c.name AS category_name')->join('categories c', 'c.id = p.category_id')->orderBy('p.code', 'ASC')->get()->getResultArray();
        return view('products/index', ['title' => 'Produk', 'items' => $items]);
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
        (new ProductModel())->insert($this->request->getPost(['code', 'name', 'category_id', 'unit', 'purchase_price', 'selling_price', 'stock_qty', 'min_stock']));
        return redirect()->to('/products')->with('success', 'Produk berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $item = (new ProductModel())->find($id);
        if (! $item) return redirect()->to('/products')->with('error', 'Produk tidak ditemukan.');
        return view('products/form', ['title' => 'Ubah Produk', 'item' => $item, 'categories' => (new CategoryModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        $rules = [
            'code' => 'required|max_length[20]|is_unique[products.code,id,' . $id . ']',
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
        (new ProductModel())->update($id, $this->request->getPost(['code', 'name', 'category_id', 'unit', 'purchase_price', 'selling_price', 'stock_qty', 'min_stock']));
        return redirect()->to('/products')->with('success', 'Produk berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/products', 'Hanya Staff Purchasing dan Admin yang bisa mengelola produk.')) return $r;
        try {
            (new ProductModel())->delete($id);
            return redirect()->to('/products')->with('success', 'Produk berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/products')->with('error', 'Produk tidak bisa dihapus karena sudah dipakai transaksi.');
        }
    }
}
