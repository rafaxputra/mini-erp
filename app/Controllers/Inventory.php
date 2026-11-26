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
        $warehouseId = (int) ($this->request->getGet('warehouse_id') ?? 0);
        $categoryId = (int) ($this->request->getGet('category_id') ?? 0);
        $stockStatus = trim((string) ($this->request->getGet('stock_status') ?? ''));

        $warehouses = (new WarehouseModel())->orderBy('id', 'ASC')->findAll();
        $categories = (new \App\Models\CategoryModel())->orderBy('name', 'ASC')->findAll();

        $builder = $db->table('products p')->select('p.*, c.name AS category_name')->join('categories c', 'c.id = p.category_id');
        if ($q !== '') {
            $builder->groupStart()
                ->like('p.name', $q, 'both', null, true)
                ->orLike('p.code', $q, 'both', null, true)
                ->orLike('c.name', $q, 'both', null, true)
            ->groupEnd();
        }
        if ($categoryId > 0) {
            $builder->where('p.category_id', $categoryId);
        }
        $items = $builder->orderBy('p.code', 'ASC')->get()->getResultArray();

        // Saldo terkini per (product_id, warehouse_id) dari mutasi stok terakhir
        $stockRows = $db->query('SELECT DISTINCT ON (warehouse_id, product_id) warehouse_id, product_id, qty_after FROM stock_movements ORDER BY warehouse_id, product_id, created_at DESC, id DESC')->getResultArray();
        $stocksMap = [];
        foreach ($stockRows as $sr) {
            $stocksMap[(int) $sr['product_id']][(int) $sr['warehouse_id']] = (int) $sr['qty_after'];
        }

        // Filter berdasarkan Status Stok (Aman / Menipis / Habis)
        if ($stockStatus !== '') {
            $items = array_values(array_filter($items, function ($it) use ($warehouseId, $stocksMap, $stockStatus) {
                $qty = $warehouseId > 0 ? ($stocksMap[$it['id']][$warehouseId] ?? 0) : (int) $it['stock_qty'];
                $min = (int) $it['min_stock'];
                if ($stockStatus === 'out') {
                    return $qty <= 0;
                }
                if ($stockStatus === 'low') {
                    return $qty > 0 && $qty <= $min;
                }
                if ($stockStatus === 'safe') {
                    return $qty > $min;
                }
                return true;
            }));
        }

        $total = 0;
        foreach ($items as $it) {
            $qty = $warehouseId > 0 ? ($stocksMap[$it['id']][$warehouseId] ?? 0) : (int) $it['stock_qty'];
            $total += $qty * (float) $it['purchase_price'];
        }

        return view('inventory/index', [
            'title' => 'Stok Produk',
            'items' => $items,
            'totalValue' => $total,
            'q' => $q,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'categories' => $categories,
            'categoryId' => $categoryId,
            'stockStatus' => $stockStatus,
            'stocksMap' => $stocksMap,
        ]);
    }
    public function movements()
    {
        if ($r = $this->requireRole(['admin', 'gudang', 'purchasing', 'manajemen'], '/inventory', 'Peran Anda tidak diizinkan melihat Riwayat/Mutasi Stok.')) return $r;
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $productId = (int) ($this->request->getGet('product_id') ?? 0);
        $warehouseId = (int) ($this->request->getGet('warehouse_id') ?? 0);
        $movementType = strtoupper(trim((string) ($this->request->getGet('movement_type') ?? '')));
        $start = (string) ($this->request->getGet('start_date') ?? '');
        $end = (string) ($this->request->getGet('end_date') ?? '');

        $builder = $db->table('stock_movements sm')
            ->select('sm.*, p.code, p.name AS product_name, u.name AS user_name, w.name AS warehouse_name, gr_ref.purchase_order_id AS ref_po_id, gr_ref.gr_number AS ref_gr_number, so_ref.so_number AS ref_so_number')
            ->join('products p', 'p.id = sm.product_id')
            ->join('users u', 'u.id = sm.created_by')
            ->join('warehouses w', 'w.id = sm.warehouse_id')
            ->join('goods_receipts gr_ref', "gr_ref.id = sm.reference_id AND sm.reference_type = 'PURCHASE_RECEIPT'", 'left')
            ->join('sales_orders so_ref', "so_ref.id = sm.reference_id AND sm.reference_type = 'SALES_ORDER'", 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.code', $q, 'both', null, true)
                ->orLike('p.name', $q, 'both', null, true)
                ->groupEnd();
        }
        if ($productId > 0) $builder->where('sm.product_id', $productId);
        if ($warehouseId > 0) $builder->where('sm.warehouse_id', $warehouseId);
        if (in_array($movementType, ['IN', 'OUT', 'ADJUSTMENT'], true)) $builder->where('sm.movement_type', $movementType);
        if ($start !== '') $builder->where('DATE(sm.created_at) >=', $start);
        if ($end !== '') $builder->where('DATE(sm.created_at) <=', $end);

        $items = $builder->orderBy('sm.created_at', 'DESC')->orderBy('sm.id', 'DESC')->limit(200)->get()->getResultArray();
        $warehouses = (new WarehouseModel())->orderBy('id', 'ASC')->findAll();

        return view('inventory/movements', [
            'title' => 'Riwayat Stok',
            'items' => $items,
            'products' => (new ProductModel())->orderBy('code', 'ASC')->findAll(),
            'warehouses' => $warehouses,
            'q' => $q,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $movementType,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }
}
