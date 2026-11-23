<?php
namespace App\Controllers;
use App\Libraries\StockService;
use App\Models\ProductModel;
use App\Models\WarehouseModel;
class Inventory extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $builder = $db->table('products p')->select('p.*, c.name AS category_name')->join('categories c', 'c.id = p.category_id');
        if ($q !== '') {
            $builder->like('p.name', $q);
        }
        $items = $builder->orderBy('p.code', 'ASC')->get()->getResultArray();
        $total = 0;
        foreach ($items as $it) {
            $total += (int) $it['stock_qty'] * (float) $it['purchase_price'];
        }
        return view('inventory/index', ['title' => 'Stok Produk', 'items' => $items, 'totalValue' => $total, 'q' => $q]);
    }
    public function movements()
    {
        $db = \Config\Database::connect();
        $productId = (int) ($this->request->getGet('product_id') ?? 0);
        $start = $this->request->getGet('start_date') ?? '';
        $end = $this->request->getGet('end_date') ?? '';
        $builder = $db->table('stock_movements sm')->select('sm.*, p.code, p.name AS product_name, u.name AS user_name, w.name AS warehouse_name')->join('products p', 'p.id = sm.product_id')->join('users u', 'u.id = sm.created_by')->join('warehouses w', 'w.id = sm.warehouse_id');
        if ($productId > 0) $builder->where('sm.product_id', $productId);
        if ($start !== '') $builder->where('DATE(sm.created_at) >=', $start);
        if ($end !== '') $builder->where('DATE(sm.created_at) <=', $end);
        $items = $builder->orderBy('sm.created_at', 'DESC')->orderBy('sm.id', 'DESC')->limit(200)->get()->getResultArray();
        return view('inventory/movements', ['title' => 'Riwayat Stok', 'items' => $items, 'products' => (new ProductModel())->orderBy('code', 'ASC')->findAll(), 'product_id' => $productId, 'start_date' => $start, 'end_date' => $end]);
    }
    public function adjust()
    {
        if ($r = $this->requireRole(['admin'], '/inventory', 'Hanya admin yang bisa mencatat penyesuaian stok.')) return $r;
        return view('inventory/adjust', ['title' => 'Koreksi Stok', 'products' => (new ProductModel())->orderBy('code', 'ASC')->findAll(), 'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function adjustPost()
    {
        if ($r = $this->requireRole(['admin'], '/inventory', 'Hanya admin yang bisa mencatat penyesuaian stok.')) return $r;
        $productId = (int) $this->request->getPost('product_id');
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $direction = $this->request->getPost('direction');
        $qty = (int) $this->request->getPost('qty');
        $notes = trim((string) $this->request->getPost('notes'));
        $errors = [];
        if ($productId <= 0) $errors[] = 'Produk wajib dipilih.';
        $warehouseId = $this->resolveWarehouseId($warehouseId);
        if ($direction !== 'IN' && $direction !== 'OUT') $errors[] = 'Arah penyesuaian wajib dipilih (Masuk atau Keluar).';
        if ($qty <= 0) $errors[] = 'Jumlah penyesuaian harus lebih dari nol, diisi ' . $qty . '.';
        if ($notes === '') $errors[] = 'Alasan penyesuaian wajib diisi.';
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }
        try {
            (new StockService())->adjustStock($productId, $qty, $direction, $this->userId(), $notes, $warehouseId);
            return redirect()->to('/inventory/movements?product_id=' . $productId)->with('success', 'Penyesuaian stok berhasil disimpan dan tercatat sebagai mutasi.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
