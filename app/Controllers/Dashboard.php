<?php
namespace App\Controllers;
use App\Models\ProductModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $role = session()->get('user_role');
        $db = \Config\Database::connect();
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        // =====================================================================
        // 1. DASHBOARD KHUSUS STAFF GUDANG (LOGISTIK & OPERASIONAL FISIK)
        // =====================================================================
        if ($role === 'gudang') {
            $grRow = $db->query(
                "SELECT COUNT(DISTINCT gr_number) AS total_gr, COALESCE(SUM(qty_received), 0) AS total_qty_received 
                 FROM goods_receipts 
                 WHERE receipt_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $doRow = $db->query(
                "SELECT COUNT(DISTINCT delivery_number) AS total_do, COALESCE(SUM(qty_delivered), 0) AS total_qty_delivered 
                 FROM deliveries 
                 WHERE delivery_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $pendingDeliveries = $db->table('sales_orders so')
                ->select('MIN(so.id) AS id, so.so_number, MIN(c.name) AS customer_name, MIN(w.name) AS warehouse_name, MIN(so.order_date) AS order_date, COUNT(so.id) AS item_count, SUM(so.qty) AS total_qty')
                ->join('customers c', 'c.id = so.customer_id')
                ->join('warehouses w', 'w.id = so.warehouse_id')
                ->where('so.status', 'confirmed')
                ->groupBy('so.so_number')
                ->orderBy('MIN(so.order_date)', 'ASC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $pendingReceipts = $db->table('purchase_orders po')
                ->select('MIN(po.id) AS id, po.po_number, MIN(s.name) AS supplier_name, MIN(w.name) AS warehouse_name, MIN(po.order_date) AS order_date, COUNT(po.id) AS item_count, SUM(po.qty) AS total_qty')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->join('warehouses w', 'w.id = po.warehouse_id')
                ->where('po.status', 'approved')
                ->groupBy('po.po_number')
                ->orderBy('MIN(po.order_date)', 'ASC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $pendingDeliveriesCount = count($db->table('sales_orders')->select('so_number')->where('status', 'confirmed')->groupBy('so_number')->get()->getResultArray());
            $pendingReceiptsCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'approved')->groupBy('po_number')->get()->getResultArray());

            $lowStock = $this->getLowStockProducts();

            $data = [
                'title' => 'Dashboard Logistik & Gudang',
                'role' => $role,
                'productCount' => (new ProductModel())->countAllResults(),
                'grThisMonth' => (int) ($grRow['total_gr'] ?? 0),
                'grQtyThisMonth' => (int) ($grRow['total_qty_received'] ?? 0),
                'doThisMonth' => (int) ($doRow['total_do'] ?? 0),
                'doQtyThisMonth' => (int) ($doRow['total_qty_delivered'] ?? 0),
                'pendingDeliveriesCount' => $pendingDeliveriesCount,
                'pendingReceiptsCount' => $pendingReceiptsCount,
                'pendingDeliveries' => $pendingDeliveries,
                'pendingReceipts' => $pendingReceipts,
                'lowStock' => $lowStock,
            ];
            return view('dashboard/index', $data);
        }

        // =====================================================================
        // 2. DASHBOARD KHUSUS STAFF SALES (PENJUALAN & PELANGGAN)
        // =====================================================================
        if ($role === 'sales') {
            $salesRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
                 FROM sales_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $draftSoCount = count($db->table('sales_orders')->select('so_number')->where('status', 'draft')->groupBy('so_number')->get()->getResultArray());
            $deliveredSoCount = count($db->table('sales_orders')->select('so_number')->where('status', 'delivered')->where('order_date >=', $startOfMonth)->where('order_date <=', $endOfMonth)->groupBy('so_number')->get()->getResultArray());
            $customerCount = $db->table('customers')->countAllResults();

            // 5 SO Terbaru
            $recentOrders = $db->table('sales_orders so')
                ->select('MIN(so.id) AS id, so.so_number, MIN(c.name) AS customer_name, MIN(so.order_date) AS order_date, MIN(so.status) AS status, SUM(so.qty * so.unit_price) AS total_amount, MAX(so.created_at) AS created_at')
                ->join('customers c', 'c.id = so.customer_id')
                ->groupBy('so.so_number')
                ->orderBy('MAX(so.created_at)', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();

            // Top 5 Produk Terlaris
            $topProducts = $db->query(
                "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
                 FROM sales_orders so 
                 JOIN products p ON p.id = so.product_id 
                 WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
                 GROUP BY p.id, p.code, p.name, p.unit 
                 ORDER BY total_qty DESC 
                 LIMIT 5",
                [$startOfMonth, $endOfMonth]
            )->getResultArray();

            $data = [
                'title' => 'Dashboard Penjualan',
                'role' => $role,
                'salesThisMonth' => (float) ($salesRow['total_sales'] ?? 0),
                'ordersThisMonth' => (int) ($salesRow['total_orders'] ?? 0),
                'draftSoCount' => $draftSoCount,
                'deliveredSoCount' => $deliveredSoCount,
                'customerCount' => $customerCount,
                'recentOrders' => $recentOrders,
                'topProducts' => $topProducts,
            ];
            return view('dashboard/index', $data);
        }

        // =====================================================================
        // 3. DASHBOARD KHUSUS STAFF PURCHASING (PENGADAAN & PEMASOK)
        // =====================================================================
        if ($role === 'purchasing') {
            $purchaseRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_purchases, COUNT(DISTINCT po_number) AS total_orders 
                 FROM purchase_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $draftPoCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'draft')->groupBy('po_number')->get()->getResultArray());
            $approvedPoCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'approved')->groupBy('po_number')->get()->getResultArray());
            $supplierCount = $db->table('suppliers')->countAllResults();

            // Peringatan stok menipis (urgent restock)
            $lowStock = $this->getLowStockProducts();

            // 5 PO Terbaru
            $recentPurchases = $db->table('purchase_orders po')
                ->select('MIN(po.id) AS id, po.po_number, MIN(s.name) AS supplier_name, MIN(po.order_date) AS order_date, MIN(po.status) AS status, SUM(po.qty * po.unit_price) AS total_amount, MAX(po.created_at) AS created_at')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->groupBy('po.po_number')
                ->orderBy('MAX(po.created_at)', 'DESC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $data = [
                'title' => 'Dashboard Pengadaan',
                'role' => $role,
                'purchasesThisMonth' => (float) ($purchaseRow['total_purchases'] ?? 0),
                'ordersThisMonth' => (int) ($purchaseRow['total_orders'] ?? 0),
                'draftPoCount' => $draftPoCount,
                'approvedPoCount' => $approvedPoCount,
                'supplierCount' => $supplierCount,
                'lowStock' => $lowStock,
                'recentPurchases' => $recentPurchases,
            ];
            return view('dashboard/index', $data);
        }

        // =====================================================================
        // 4. DASHBOARD KHUSUS MANAJEMEN (EKSEKUTIF / DIREKSI)
        // =====================================================================
        if ($role === 'manajemen') {
            $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];

            $salesRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
                 FROM sales_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $purchaseRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_purchases, COUNT(DISTINCT po_number) AS total_orders 
                 FROM purchase_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfMonth, $endOfMonth]
            )->getRowArray();

            $totalSales = (float) ($salesRow['total_sales'] ?? 0);
            $totalPurchases = (float) ($purchaseRow['total_purchases'] ?? 0);
            $netMargin = $totalSales - $totalPurchases;

            $topProducts = $db->query(
                "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
                 FROM sales_orders so 
                 JOIN products p ON p.id = so.product_id 
                 WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
                 GROUP BY p.id, p.code, p.name, p.unit 
                 ORDER BY total_sales DESC 
                 LIMIT 5",
                [$startOfMonth, $endOfMonth]
            )->getResultArray();

            // Ringkasan Gudang
            $warehouses = $db->table('warehouses')->orderBy('id', 'ASC')->get()->getResultArray();
            $warehouseSummary = [];
            foreach ($warehouses as $w) {
                $lastMovements = $db->query(
                    'SELECT DISTINCT ON (product_id) product_id, qty_after 
                     FROM stock_movements 
                     WHERE warehouse_id = ? 
                     ORDER BY product_id, created_at DESC, id DESC',
                    [(int) $w['id']]
                )->getResultArray();
                $itemCount = 0;
                $wStockQty = 0;
                foreach ($lastMovements as $lm) {
                    if ((int) $lm['qty_after'] > 0) {
                        $itemCount++;
                        $wStockQty += (int) $lm['qty_after'];
                    }
                }
                $warehouseSummary[] = [
                    'id' => $w['id'],
                    'name' => $w['name'],
                    'code' => $w['code'],
                    'location' => $w['location'],
                    'itemCount' => $itemCount,
                    'totalQty' => $wStockQty,
                ];
            }

            $lowStockCount = count($db->query('SELECT id FROM products WHERE stock_qty <= min_stock')->getResultArray());

            // Antrean Pesanan Beli yang Menunggu Persetujuan (ACC) Manajer
            $pendingApprovalPOs = $db->table('purchase_orders po')
                ->select('MIN(po.id) AS id, po.po_number, MIN(po.order_date) AS order_date, s.name AS supplier_name, s.code AS supplier_code, u.name AS creator_name, SUM(po.qty * po.unit_price) AS total_amount, COUNT(po.id) AS total_items, MAX(po.created_at) AS created_at')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->join('users u', 'u.id = po.created_by')
                ->where('po.status', 'draft')
                ->groupBy('po.po_number, s.name, s.code, u.name')
                ->orderBy('MIN(po.order_date)', 'ASC')
                ->orderBy('MIN(po.id)', 'ASC')
                ->get()
                ->getResultArray();
            $pendingPoCount = count($pendingApprovalPOs);

            $data = [
                'title' => 'Dashboard Eksekutif',
                'role' => $role,
                'totalSales' => $totalSales,
                'totalPurchases' => $totalPurchases,
                'netMargin' => $netMargin,
                'stockValue' => (float) $stockValue,
                'soCount' => (int) ($salesRow['total_orders'] ?? 0),
                'poCount' => (int) ($purchaseRow['total_orders'] ?? 0),
                'lowStockCount' => $lowStockCount,
                'topProducts' => $topProducts,
                'warehouseSummary' => $warehouseSummary,
                'pendingApprovalPOs' => $pendingApprovalPOs,
                'pendingPoCount' => $pendingPoCount,
            ];
            return view('dashboard/index', $data);
        }

        // =====================================================================
        // 5. DASHBOARD KHUSUS ADMINISTRATOR (SUPER ADMIN)
        // =====================================================================
        $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];

        $salesRow = $db->query(
            "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
             FROM sales_orders 
             WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
            [$startOfMonth, $endOfMonth]
        )->getRowArray();

        $topProducts = $db->query(
            "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
             FROM sales_orders so 
             JOIN products p ON p.id = so.product_id 
             WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
             GROUP BY p.id, p.code, p.name, p.unit 
             ORDER BY total_qty DESC 
             LIMIT 5",
            [$startOfMonth, $endOfMonth]
        )->getResultArray();

        $userCount = $db->table('users')->where('is_active', true)->countAllResults();
        $warehouseCount = $db->table('warehouses')->countAllResults();
        $categoryCount = $db->table('categories')->countAllResults();

        $data = [
            'title' => 'Dashboard Administrator',
            'role' => $role,
            'productCount' => (new ProductModel())->countAllResults(),
            'stockValue' => (float) $stockValue,
            'salesThisMonth' => (float) ($salesRow['total_sales'] ?? 0),
            'ordersThisMonth' => (int) ($salesRow['total_orders'] ?? 0),
            'userCount' => $userCount,
            'warehouseCount' => $warehouseCount,
            'categoryCount' => $categoryCount,
            'lowStock' => $this->getLowStockProducts(),
            'topProducts' => $topProducts,
        ];
        return view('dashboard/index', $data);
    }

    protected function getLowStockProducts(): array
    {
        $db = \Config\Database::connect();
        return $db->query(<<<'SQL'
SELECT 
    p.*, 
    c.name AS category_name,
    active_po.po_number AS active_po_number,
    active_po.status AS active_po_status,
    active_po.po_id AS active_po_id,
    active_po.ordered_qty AS active_po_qty,
    active_po.po_count AS active_po_count
FROM products p
LEFT JOIN categories c ON c.id = p.category_id
LEFT JOIN (
    SELECT 
        po.product_id,
        MIN(po.id) AS po_id,
        MIN(po.po_number) AS po_number,
        CASE WHEN SUM(CASE WHEN po.status = 'approved' THEN 1 ELSE 0 END) > 0 THEN 'approved' ELSE 'draft' END AS status,
        SUM(po.qty) AS ordered_qty,
        COUNT(DISTINCT po.po_number) AS po_count
    FROM purchase_orders po
    WHERE po.status IN ('draft', 'approved')
    GROUP BY po.product_id
) active_po ON active_po.product_id = p.id
WHERE p.stock_qty <= p.min_stock
ORDER BY p.stock_qty ASC
SQL
        )->getResultArray();
    }
}
