<?php
namespace App\Controllers;
use App\Models\ProductModel;

class Dashboard extends BaseController
{
    protected function resolvePeriod(): array
    {
        $periodType = (string) ($this->request->getGet('period_type') ?? 'month');
        if (! in_array($periodType, ['week', 'month', 'year'], true)) {
            $periodType = 'month';
        }

        $year = (int) ($this->request->getGet('year') ?? date('Y'));
        if ($year < 2020 || $year > 2100) $year = (int) date('Y');

        $month = (int) ($this->request->getGet('month') ?? date('m'));
        if ($month < 1 || $month > 12) $month = (int) date('m');

        $weekStart = (string) ($this->request->getGet('week_start') ?? date('Y-m-d', strtotime('monday this week')));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart)) {
            $weekStart = date('Y-m-d', strtotime('monday this week'));
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($periodType === 'year') {
            $startDate = sprintf('%04d-01-01', $year);
            $endDate = sprintf('%04d-12-31', $year);
            $periodLabel = 'Tahun ' . $year;
        } elseif ($periodType === 'week') {
            $startDate = $weekStart;
            $endDate = date('Y-m-d', strtotime($weekStart . ' +6 days'));
            $periodLabel = 'Minggu ' . date('d M', strtotime($startDate)) . ' – ' . date('d M Y', strtotime($endDate));
        } else {
            $startDate = sprintf('%04d-%02d-01', $year, $month);
            $endDate = date('Y-m-t', strtotime($startDate));
            $periodLabel = $monthNames[$month] . ' ' . $year;
        }

        return [
            'periodType' => $periodType,
            'year' => $year,
            'month' => $month,
            'weekStart' => $weekStart,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'periodLabel' => $periodLabel,
        ];
    }

    protected function resolvePreviousPeriod(array $period): array
    {
        $type = $period['periodType'];
        if ($type === 'year') {
            $prevYear = $period['year'] - 1;
            return [sprintf('%04d-01-01', $prevYear), sprintf('%04d-12-31', $prevYear)];
        }
        if ($type === 'week') {
            $prevStart = date('Y-m-d', strtotime($period['startDate'] . ' -7 days'));
            $prevEnd = date('Y-m-d', strtotime($prevStart . ' +6 days'));
            return [$prevStart, $prevEnd];
        }
        $prevStart = date('Y-m-01', strtotime($period['startDate'] . ' -1 month'));
        $prevEnd = date('Y-m-t', strtotime($prevStart));
        return [$prevStart, $prevEnd];
    }

    protected function calcDeltaPct(float $current, float $previous): ?float
    {
        if ($previous <= 0.0) {
            return $current > 0.0 ? 100.0 : null;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    protected function buildRoleExtras($db, string $startDate, string $endDate, string $role): array
    {
        if ($role === 'gudang') {
            $healthRow = $db->query(
                "SELECT
                    SUM(CASE WHEN stock_qty > min_stock THEN 1 ELSE 0 END) AS safe_cnt,
                    SUM(CASE WHEN stock_qty > 0 AND stock_qty <= min_stock THEN 1 ELSE 0 END) AS low_cnt,
                    SUM(CASE WHEN stock_qty <= 0 THEN 1 ELSE 0 END) AS empty_cnt
                 FROM products"
            )->getRowArray();

            $fastMoving = $db->query(
                "SELECT p.name, SUM(d.qty_delivered) AS total_qty
                 FROM deliveries d
                 JOIN products p ON p.id = d.product_id
                 WHERE d.status <> 'cancelled' AND d.delivery_date BETWEEN ? AND ?
                 GROUP BY p.id, p.name
                 ORDER BY total_qty DESC
                 LIMIT 5",
                [$startDate, $endDate]
            )->getResultArray();

            return [
                'stockHealth' => [
                    'labels' => ['Stok Aman', 'Menipis', 'Habis'],
                    'series' => [
                        (int) ($healthRow['safe_cnt'] ?? 0),
                        (int) ($healthRow['low_cnt'] ?? 0),
                        (int) ($healthRow['empty_cnt'] ?? 0),
                    ],
                ],
                'fastMoving' => [
                    'labels' => array_column($fastMoving, 'name'),
                    'series' => array_map('intval', array_column($fastMoving, 'total_qty')),
                ],
            ];
        }

        if ($role === 'sales') {
            $statusRows = $db->query(
                "SELECT status, COUNT(DISTINCT so_number) AS cnt
                 FROM sales_orders
                 WHERE order_date BETWEEN ? AND ?
                 GROUP BY status",
                [$startDate, $endDate]
            )->getResultArray();
            $statusMap = ['draft' => 0, 'revision' => 0, 'confirmed' => 0, 'delivered' => 0, 'cancelled' => 0];
            foreach ($statusRows as $sr) {
                if (isset($statusMap[$sr['status']])) {
                    $statusMap[$sr['status']] = (int) $sr['cnt'];
                }
            }

            $topCustomers = $db->query(
                "SELECT c.name, COALESCE(SUM(so.qty * so.unit_price), 0) AS total_sales
                 FROM sales_orders so
                 JOIN customers c ON c.id = so.customer_id
                 WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ?
                 GROUP BY c.id, c.name
                 ORDER BY total_sales DESC
                 LIMIT 5",
                [$startDate, $endDate]
            )->getResultArray();

            return [
                'soStatusDist' => [
                    'labels' => ['Draf', 'Revisi', 'Dikonfirmasi', 'Terkirim', 'Dibatalkan'],
                    'series' => array_values($statusMap),
                ],
                'topCustomers' => [
                    'labels' => array_column($topCustomers, 'name'),
                    'series' => array_map('floatval', array_column($topCustomers, 'total_sales')),
                ],
            ];
        }

        if ($role === 'purchasing') {
            $poRows = $db->query(
                "SELECT status, COUNT(DISTINCT po_number) AS cnt
                 FROM purchase_orders
                 WHERE order_date BETWEEN ? AND ?
                 GROUP BY status",
                [$startDate, $endDate]
            )->getResultArray();
            $poMap = ['draft' => 0, 'approved' => 0, 'received' => 0, 'cancelled' => 0];
            foreach ($poRows as $pr) {
                if (isset($poMap[$pr['status']])) {
                    $poMap[$pr['status']] = (int) $pr['cnt'];
                }
            }

            $topSuppliers = $db->query(
                "SELECT s.name, COALESCE(SUM(po.qty * po.unit_price), 0) AS total_spend
                 FROM purchase_orders po
                 JOIN suppliers s ON s.id = po.supplier_id
                 WHERE po.status <> 'cancelled' AND po.order_date BETWEEN ? AND ?
                 GROUP BY s.id, s.name
                 ORDER BY total_spend DESC
                 LIMIT 5",
                [$startDate, $endDate]
            )->getResultArray();

            return [
                'poStatusDist' => [
                    'labels' => ['Menunggu ACC', 'Disetujui', 'Diterima', 'Dibatalkan'],
                    'series' => array_values($poMap),
                ],
                'topSuppliers' => [
                    'labels' => array_column($topSuppliers, 'name'),
                    'series' => array_map('floatval', array_column($topSuppliers, 'total_spend')),
                ],
            ];
        }

        if ($role === 'manajemen') {
            $catSales = $db->query(
                "SELECT COALESCE(c.name, 'Lainnya') AS name, COALESCE(SUM(so.qty * so.unit_price), 0) AS total_sales
                 FROM sales_orders so
                 JOIN products p ON p.id = so.product_id
                 LEFT JOIN categories c ON c.id = p.category_id
                 WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ?
                 GROUP BY c.name
                 ORDER BY total_sales DESC
                 LIMIT 6",
                [$startDate, $endDate]
            )->getResultArray();

            return [
                'categorySalesDist' => [
                    'labels' => array_column($catSales, 'name'),
                    'series' => array_map('floatval', array_column($catSales, 'total_sales')),
                ],
            ];
        }

        // Admin
        $catProds = $db->query(
            "SELECT COALESCE(c.name, 'Tanpa Kategori') AS name, COUNT(p.id) AS cnt
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             GROUP BY c.name
             ORDER BY cnt DESC
             LIMIT 6"
        )->getResultArray();

        $roleUsers = $db->query(
            "SELECT COALESCE(r.name, 'User') AS name, COUNT(u.id) AS cnt
             FROM users u
             LEFT JOIN roles r ON r.id = u.role_id
             WHERE u.is_active = true
             GROUP BY r.name
             ORDER BY cnt DESC"
        )->getResultArray();

        return [
            'categoryProductDist' => [
                'labels' => array_column($catProds, 'name'),
                'series' => array_map('intval', array_column($catProds, 'cnt')),
            ],
            'userRoleDist' => [
                'labels' => array_column($roleUsers, 'name'),
                'series' => array_map('intval', array_column($roleUsers, 'cnt')),
            ],
        ];
    }

    protected function buildChartSeries($db, string $startDate, string $endDate, string $periodType, string $role): array
    {
        $labels = [];
        $keys = [];
        if ($periodType === 'year') {
            $year = (int) substr($startDate, 0, 4);
            $shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $keys[] = sprintf('%04d-%02d', $year, $m);
                $labels[] = $shortMonths[$m - 1];
            }
            $fmt = "TO_CHAR(%s, 'YYYY-MM')";
        } else {
            $cur = strtotime($startDate);
            $end = strtotime($endDate);
            while ($cur <= $end) {
                $keys[] = date('Y-m-d', $cur);
                $labels[] = date('d/m', $cur);
                $cur = strtotime('+1 day', $cur);
            }
            $fmt = "TO_CHAR(%s, 'YYYY-MM-DD')";
        }

        $primaryMap = array_fill_keys($keys, 0);
        $secondaryMap = array_fill_keys($keys, 0);

        if ($role === 'gudang') {
            $grExpr = sprintf($fmt, 'receipt_date');
            $grRows = $db->query("SELECT {$grExpr} AS k, COALESCE(SUM(qty_received), 0) AS total FROM goods_receipts WHERE receipt_date BETWEEN ? AND ? GROUP BY k", [$startDate, $endDate])->getResultArray();
            foreach ($grRows as $r) {
                if (isset($primaryMap[$r['k']])) $primaryMap[$r['k']] = (int) $r['total'];
            }
            $doExpr = sprintf($fmt, 'delivery_date');
            $doRows = $db->query("SELECT {$doExpr} AS k, COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE status <> 'cancelled' AND delivery_date BETWEEN ? AND ? GROUP BY k", [$startDate, $endDate])->getResultArray();
            foreach ($doRows as $r) {
                if (isset($secondaryMap[$r['k']])) $secondaryMap[$r['k']] = (int) $r['total'];
            }
        } else {
            $soExpr = sprintf($fmt, 'order_date');
            $soRows = $db->query("SELECT {$soExpr} AS k, COALESCE(SUM(qty * unit_price), 0) AS total FROM sales_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ? GROUP BY k", [$startDate, $endDate])->getResultArray();
            foreach ($soRows as $r) {
                if (isset($primaryMap[$r['k']])) $primaryMap[$r['k']] = (float) $r['total'];
            }
            $poExpr = sprintf($fmt, 'order_date');
            $poRows = $db->query("SELECT {$poExpr} AS k, COALESCE(SUM(qty * unit_price), 0) AS total FROM purchase_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ? GROUP BY k", [$startDate, $endDate])->getResultArray();
            foreach ($poRows as $r) {
                if (isset($secondaryMap[$r['k']])) $secondaryMap[$r['k']] = (float) $r['total'];
            }
        }

        return [
            'labels' => $labels,
            'primary' => array_values($primaryMap),
            'secondary' => array_values($secondaryMap),
        ];
    }

    public function index()
    {
        $role = session()->get('user_role');
        $db = \Config\Database::connect();
        $period = $this->resolvePeriod();
        $startOfPeriod = $period['startDate'];
        $endOfPeriod = $period['endDate'];
        [$prevStart, $prevEnd] = $this->resolvePreviousPeriod($period);
        $chartData = $this->buildChartSeries($db, $startOfPeriod, $endOfPeriod, $period['periodType'], $role);
        $roleExtras = $this->buildRoleExtras($db, $startOfPeriod, $endOfPeriod, $role);

        // =====================================================================
        // 1. DASHBOARD KHUSUS STAFF GUDANG (LOGISTIK & OPERASIONAL FISIK)
        // =====================================================================
        if ($role === 'gudang') {
            $grRow = $db->query(
                "SELECT COUNT(DISTINCT gr_number) AS total_gr, COALESCE(SUM(qty_received), 0) AS total_qty_received 
                 FROM goods_receipts 
                 WHERE receipt_date BETWEEN ? AND ?",
                [$startOfPeriod, $endOfPeriod]
            )->getRowArray();

            $doRow = $db->query(
                "SELECT COUNT(DISTINCT delivery_number) AS total_do, COALESCE(SUM(qty_delivered), 0) AS total_qty_delivered 
                 FROM deliveries 
                 WHERE status <> 'cancelled' AND delivery_date BETWEEN ? AND ?",
                [$startOfPeriod, $endOfPeriod]
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
                ->select("MIN(po.id) AS id, po.po_number, MIN(s.name) AS supplier_name, 'Ditentukan saat GR' AS warehouse_name, MIN(po.order_date) AS order_date, COUNT(po.id) AS item_count, SUM(po.qty) AS total_qty")
                ->join('suppliers s', 's.id = po.supplier_id')
                ->where('po.status', 'approved')
                ->groupBy('po.po_number')
                ->orderBy('MIN(po.order_date)', 'ASC')
                ->limit(6)
                ->get()
                ->getResultArray();

            $pendingDeliveriesCount = count($db->table('sales_orders')->select('so_number')->where('status', 'confirmed')->groupBy('so_number')->get()->getResultArray());
            $pendingReceiptsCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'approved')->groupBy('po_number')->get()->getResultArray());
            $lowStock = $this->getLowStockProducts();

            $prevGrQty = (int) ($db->query("SELECT COALESCE(SUM(qty_received), 0) AS t FROM goods_receipts WHERE receipt_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRow('t') ?? 0);
            $prevDoQty = (int) ($db->query("SELECT COALESCE(SUM(qty_delivered), 0) AS t FROM deliveries WHERE status <> 'cancelled' AND delivery_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRow('t') ?? 0);

            return view('dashboard/index', array_merge($period, $roleExtras, [
                'title' => 'Dashboard Gudang & Pengiriman',
                'role' => $role,
                'productCount' => (new ProductModel())->countAllResults(),
                'grThisMonth' => (int) ($grRow['total_gr'] ?? 0),
                'grQtyThisMonth' => (int) ($grRow['total_qty_received'] ?? 0),
                'doThisMonth' => (int) ($doRow['total_do'] ?? 0),
                'doQtyThisMonth' => (int) ($doRow['total_qty_delivered'] ?? 0),
                'deltaGrQty' => $this->calcDeltaPct((float) ($grRow['total_qty_received'] ?? 0), (float) $prevGrQty),
                'deltaDoQty' => $this->calcDeltaPct((float) ($doRow['total_qty_delivered'] ?? 0), (float) $prevDoQty),
                'pendingDeliveriesCount' => $pendingDeliveriesCount,
                'pendingReceiptsCount' => $pendingReceiptsCount,
                'pendingDeliveries' => $pendingDeliveries,
                'pendingReceipts' => $pendingReceipts,
                'lowStock' => $lowStock,
                'chartData' => $chartData,
            ]));
        }

        // =====================================================================
        // 2. DASHBOARD KHUSUS STAFF SALES (PENJUALAN & PELANGGAN)
        // =====================================================================
        if ($role === 'sales') {
            $salesRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
                 FROM sales_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfPeriod, $endOfPeriod]
            )->getRowArray();

            $draftSoCount = count($db->table('sales_orders')->select('so_number')->where('status', 'draft')->groupBy('so_number')->get()->getResultArray());
            $revisionSoCount = count($db->table('sales_orders')->select('so_number')->where('status', 'revision')->groupBy('so_number')->get()->getResultArray());
            $deliveredSoCount = count($db->table('sales_orders')->select('so_number')->where('status', 'delivered')->where('order_date >=', $startOfPeriod)->where('order_date <=', $endOfPeriod)->groupBy('so_number')->get()->getResultArray());
            $customerCount = $db->table('customers')->countAllResults();

            $recentOrders = $db->table('sales_orders so')
                ->select('MIN(so.id) AS id, so.so_number, MIN(c.name) AS customer_name, MIN(so.order_date) AS order_date, MIN(so.status) AS status, SUM(so.qty * so.unit_price) AS total_amount, MAX(so.created_at) AS created_at')
                ->join('customers c', 'c.id = so.customer_id')
                ->where('so.order_date >=', $startOfPeriod)
                ->where('so.order_date <=', $endOfPeriod)
                ->groupBy('so.so_number')
                ->orderBy('MAX(so.created_at)', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $topProducts = $db->query(
                "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
                 FROM sales_orders so 
                 JOIN products p ON p.id = so.product_id 
                 WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
                 GROUP BY p.id, p.code, p.name, p.unit 
                 ORDER BY total_qty DESC 
                 LIMIT 10",
                [$startOfPeriod, $endOfPeriod]
            )->getResultArray();

            $prevSalesRow = $db->query("SELECT COALESCE(SUM(qty * unit_price), 0) AS s, COUNT(DISTINCT so_number) AS o FROM sales_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRowArray();

            return view('dashboard/index', array_merge($period, $roleExtras, [
                'title' => 'Dashboard & Laporan Penjualan',
                'role' => $role,
                'salesThisMonth' => (float) ($salesRow['total_sales'] ?? 0),
                'ordersThisMonth' => (int) ($salesRow['total_orders'] ?? 0),
                'deltaSales' => $this->calcDeltaPct((float) ($salesRow['total_sales'] ?? 0), (float) ($prevSalesRow['s'] ?? 0)),
                'deltaOrders' => $this->calcDeltaPct((float) ($salesRow['total_orders'] ?? 0), (float) ($prevSalesRow['o'] ?? 0)),
                'draftSoCount' => $draftSoCount,
                'revisionSoCount' => $revisionSoCount,
                'deliveredSoCount' => $deliveredSoCount,
                'customerCount' => $customerCount,
                'recentOrders' => $recentOrders,
                'topProducts' => $topProducts,
                'chartData' => $chartData,
            ]));
        }

        // =====================================================================
        // 3. DASHBOARD KHUSUS STAFF PURCHASING (PENGADAAN & PEMASOK)
        // =====================================================================
        if ($role === 'purchasing') {
            $purchaseRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_purchases, COUNT(DISTINCT po_number) AS total_orders 
                 FROM purchase_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfPeriod, $endOfPeriod]
            )->getRowArray();

            $draftPoCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'draft')->groupBy('po_number')->get()->getResultArray());
            $approvedPoCount = count($db->table('purchase_orders')->select('po_number')->where('status', 'approved')->groupBy('po_number')->get()->getResultArray());
            $supplierCount = $db->table('suppliers')->countAllResults();
            $lowStock = $this->getLowStockProducts();

            $recentPurchases = $db->table('purchase_orders po')
                ->select('MIN(po.id) AS id, po.po_number, MIN(s.name) AS supplier_name, MIN(po.order_date) AS order_date, MIN(po.status) AS status, SUM(po.qty * po.unit_price) AS total_amount, MAX(po.created_at) AS created_at')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->where('po.order_date >=', $startOfPeriod)
                ->where('po.order_date <=', $endOfPeriod)
                ->groupBy('po.po_number')
                ->orderBy('MAX(po.created_at)', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $restockAlerts = $db->table('restock_alerts ra')
                ->select('ra.*, p.code AS product_code, p.name AS product_name, p.unit, w.name AS warehouse_name')
                ->join('products p', 'p.id = ra.product_id')
                ->join('warehouses w', 'w.id = ra.warehouse_id')
                ->where('ra.status', 'open')
                ->orderBy('ra.created_at', 'DESC')
                ->get()
                ->getResultArray();

            $prevPoRow = $db->query("SELECT COALESCE(SUM(qty * unit_price), 0) AS p, COUNT(DISTINCT po_number) AS o FROM purchase_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRowArray();

            return view('dashboard/index', array_merge($period, $roleExtras, [
                'title' => 'Dashboard & Laporan Pembelian',
                'role' => $role,
                'purchasesThisMonth' => (float) ($purchaseRow['total_purchases'] ?? 0),
                'ordersThisMonth' => (int) ($purchaseRow['total_orders'] ?? 0),
                'deltaPurchases' => $this->calcDeltaPct((float) ($purchaseRow['total_purchases'] ?? 0), (float) ($prevPoRow['p'] ?? 0)),
                'draftPoCount' => $draftPoCount,
                'approvedPoCount' => $approvedPoCount,
                'supplierCount' => $supplierCount,
                'lowStock' => $lowStock,
                'recentPurchases' => $recentPurchases,
                'restockAlerts' => $restockAlerts,
                'chartData' => $chartData,
            ]));
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
                [$startOfPeriod, $endOfPeriod]
            )->getRowArray();

            $purchaseRow = $db->query(
                "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_purchases, COUNT(DISTINCT po_number) AS total_orders 
                 FROM purchase_orders 
                 WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
                [$startOfPeriod, $endOfPeriod]
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
                [$startOfPeriod, $endOfPeriod]
            )->getResultArray();

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
            $pendingApprovalPOs = array_slice($pendingApprovalPOs, 0, 5);

            $reportSales = $db->table('sales_orders so')
                ->select('MIN(so.id) AS id, so.so_number, c.name AS customer_name, MIN(so.order_date) AS order_date, MIN(so.status) AS status, SUM(so.qty * so.unit_price) AS total_amount')
                ->join('customers c', 'c.id = so.customer_id')
                ->where('so.order_date >=', $startOfPeriod)
                ->where('so.order_date <=', $endOfPeriod)
                ->groupBy('so.so_number, c.name')
                ->orderBy('order_date', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $reportPurchases = $db->table('purchase_orders po')
                ->select('MIN(po.id) AS id, po.po_number, s.name AS supplier_name, MIN(po.order_date) AS order_date, MIN(po.status) AS status, SUM(po.qty * po.unit_price) AS total_amount')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->where('po.order_date >=', $startOfPeriod)
                ->where('po.order_date <=', $endOfPeriod)
                ->groupBy('po.po_number, s.name')
                ->orderBy('order_date', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $prevSalesVal = (float) ($db->query("SELECT COALESCE(SUM(qty * unit_price), 0) AS t FROM sales_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRow('t') ?? 0);
            $prevPoVal = (float) ($db->query("SELECT COALESCE(SUM(qty * unit_price), 0) AS t FROM purchase_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRow('t') ?? 0);

            return view('dashboard/index', array_merge($period, $roleExtras, [
                'title' => 'Dashboard Eksekutif & Laporan Perusahaan',
                'role' => $role,
                'totalSales' => $totalSales,
                'totalPurchases' => $totalPurchases,
                'deltaSales' => $this->calcDeltaPct($totalSales, $prevSalesVal),
                'deltaPurchases' => $this->calcDeltaPct($totalPurchases, $prevPoVal),
                'netMargin' => $netMargin,
                'stockValue' => (float) $stockValue,
                'soCount' => (int) ($salesRow['total_orders'] ?? 0),
                'poCount' => (int) ($purchaseRow['total_orders'] ?? 0),
                'lowStockCount' => $lowStockCount,
                'topProducts' => $topProducts,
                'warehouseSummary' => $warehouseSummary,
                'pendingApprovalPOs' => $pendingApprovalPOs,
                'pendingPoCount' => $pendingPoCount,
                'reportSales' => $reportSales,
                'reportPurchases' => $reportPurchases,
                'chartData' => $chartData,
            ]));
        }

        // =====================================================================
        // 5. DASHBOARD KHUSUS ADMINISTRATOR (SUPER ADMIN)
        // =====================================================================
        $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];

        $salesRow = $db->query(
            "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
             FROM sales_orders 
             WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
            [$startOfPeriod, $endOfPeriod]
        )->getRowArray();

        $topProducts = $db->query(
            "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
             FROM sales_orders so 
             JOIN products p ON p.id = so.product_id 
             WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
             GROUP BY p.id, p.code, p.name, p.unit 
             ORDER BY total_qty DESC 
             LIMIT 5",
            [$startOfPeriod, $endOfPeriod]
        )->getResultArray();

        $userCount = $db->table('users')->where('is_active', true)->countAllResults();
        $warehouseCount = $db->table('warehouses')->countAllResults();
        $categoryCount = $db->table('categories')->countAllResults();

        $prevSalesVal = (float) ($db->query("SELECT COALESCE(SUM(qty * unit_price), 0) AS t FROM sales_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$prevStart, $prevEnd])->getRow('t') ?? 0);

        return view('dashboard/index', array_merge($period, $roleExtras, [
            'title' => 'Dashboard Administrator',
            'role' => $role,
            'productCount' => (new ProductModel())->countAllResults(),
            'stockValue' => (float) $stockValue,
            'salesThisMonth' => (float) ($salesRow['total_sales'] ?? 0),
            'ordersThisMonth' => (int) ($salesRow['total_orders'] ?? 0),
            'deltaSales' => $this->calcDeltaPct((float) ($salesRow['total_sales'] ?? 0), $prevSalesVal),
            'userCount' => $userCount,
            'warehouseCount' => $warehouseCount,
            'categoryCount' => $categoryCount,
            'lowStock' => $this->getLowStockProducts(),
            'topProducts' => $topProducts,
            'chartData' => $chartData,
        ]));
    }

    public function quickSearch()
    {
        $q = trim((string) $this->request->getGet('q'));
        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['items' => []]);
        }

        $role = session()->get('user_role');
        $db = \Config\Database::connect();
        $like = '%' . $q . '%';
        $items = [];

        $prods = $db->query("SELECT id, code, name, stock_qty, unit FROM products WHERE code ILIKE ? OR name ILIKE ? ORDER BY name ASC LIMIT 5", [$like, $like])->getResultArray();
        foreach ($prods as $p) {
            $items[] = [
                'type' => 'Produk',
                'title' => esc($p['name']) . ' (' . esc($p['code']) . ')',
                'subtitle' => 'Stok: ' . $p['stock_qty'] . ' ' . $p['unit'],
                'url' => '/inventory?q=' . urlencode($p['code']),
            ];
        }

        if (in_array($role, ['admin', 'sales', 'manajemen'], true)) {
            $sos = $db->query("SELECT MIN(id) AS id, so_number, MIN(status) AS status FROM sales_orders WHERE so_number ILIKE ? GROUP BY so_number ORDER BY MIN(id) DESC LIMIT 4", [$like])->getResultArray();
            foreach ($sos as $so) {
                $items[] = [
                    'type' => 'SO',
                    'title' => esc($so['so_number']),
                    'subtitle' => 'Status: ' . status_label((string) $so['status']),
                    'url' => '/sales-orders/show/' . $so['id'],
                ];
            }
        }

        if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)) {
            $pos = $db->query("SELECT MIN(id) AS id, po_number, MIN(status) AS status FROM purchase_orders WHERE po_number ILIKE ? GROUP BY po_number ORDER BY MIN(id) DESC LIMIT 4", [$like])->getResultArray();
            foreach ($pos as $po) {
                $items[] = [
                    'type' => 'PO',
                    'title' => esc($po['po_number']),
                    'subtitle' => 'Status: ' . status_label((string) $po['status']),
                    'url' => '/purchase-orders/show/' . $po['id'],
                ];
            }
        }

        if ($role === 'gudang') {
            $dos = $db->query("SELECT MIN(id) AS id, delivery_number, MIN(status) AS status FROM deliveries WHERE delivery_number ILIKE ? GROUP BY delivery_number ORDER BY MIN(id) DESC LIMIT 3", [$like])->getResultArray();
            foreach ($dos as $do) {
                $items[] = [
                    'type' => 'DO',
                    'title' => esc($do['delivery_number']),
                    'subtitle' => 'Status: ' . status_label((string) ($do['status'] ?? 'delivered')),
                    'url' => '/deliveries?q=' . urlencode((string) $do['delivery_number']),
                ];
            }
            $grs = $db->query("SELECT MIN(id) AS id, gr_number, MIN(receipt_date) AS receipt_date FROM goods_receipts WHERE gr_number ILIKE ? GROUP BY gr_number ORDER BY MIN(id) DESC LIMIT 3", [$like])->getResultArray();
            foreach ($grs as $gr) {
                $items[] = [
                    'type' => 'GR',
                    'title' => esc($gr['gr_number']),
                    'subtitle' => 'Tanggal: ' . date('d/m/Y', strtotime((string) $gr['receipt_date'])),
                    'url' => '/goods-receipts?q=' . urlencode((string) $gr['gr_number']),
                ];
            }
        }

        if (in_array($role, ['admin', 'sales', 'manajemen'], true)) {
            $custs = $db->query("SELECT id, name, phone FROM customers WHERE name ILIKE ? LIMIT 3", [$like])->getResultArray();
            foreach ($custs as $c) {
                $items[] = [
                    'type' => 'Pelanggan',
                    'title' => esc($c['name']),
                    'subtitle' => esc($c['phone'] ?? 'Mitra Pelanggan'),
                    'url' => '/customers',
                ];
            }
        }

        if (in_array($role, ['admin', 'purchasing', 'manajemen'], true)) {
            $sups = $db->query("SELECT id, name, phone FROM suppliers WHERE name ILIKE ? LIMIT 3", [$like])->getResultArray();
            foreach ($sups as $s) {
                $items[] = [
                    'type' => 'Pemasok',
                    'title' => esc($s['name']),
                    'subtitle' => esc($s['phone'] ?? 'Mitra Pemasok'),
                    'url' => '/suppliers',
                ];
            }
        }

        return $this->response->setJSON(['items' => array_slice($items, 0, 12)]);
    }

    public function poPreview(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'manajemen', 'purchasing'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }
        $db = \Config\Database::connect();
        $head = $db->table('purchase_orders po')
            ->select('po.id, po.po_number, po.order_date, po.status, po.notes, s.name AS supplier_name, u.name AS creator_name')
            ->join('suppliers s', 's.id = po.supplier_id')
            ->join('users u', 'u.id = po.created_by', 'left')
            ->where('po.id', $id)
            ->get()
            ->getRowArray();

        if (! $head) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'PO tidak ditemukan']);
        }

        $rows = $db->table('purchase_orders po')
            ->select('p.code, p.name, p.unit, po.qty, po.unit_price, (po.qty * po.unit_price) AS subtotal')
            ->join('products p', 'p.id = po.product_id')
            ->where('po.po_number', $head['po_number'])
            ->orderBy('po.id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['po' => $head, 'items' => $rows]);
    }

    public function soPreview(int $id)
    {
        if (! in_array(session()->get('user_role'), ['admin', 'manajemen', 'sales'], true)) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }
        $db = \Config\Database::connect();
        $head = $db->table('sales_orders so')
            ->select('so.id, so.so_number, so.order_date, so.status, so.notes, c.name AS customer_name, w.name AS warehouse_name')
            ->join('customers c', 'c.id = so.customer_id')
            ->join('warehouses w', 'w.id = so.warehouse_id', 'left')
            ->where('so.id', $id)
            ->get()
            ->getRowArray();

        if (! $head) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'SO tidak ditemukan']);
        }

        $rows = $db->table('sales_orders so')
            ->select('p.code, p.name, p.unit, so.qty, so.unit_price, (so.qty * so.unit_price) AS subtotal')
            ->join('products p', 'p.id = so.product_id')
            ->where('so.so_number', $head['so_number'])
            ->orderBy('so.id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON(['so' => $head, 'items' => $rows]);
    }

    public function export()
    {
        $role = session()->get('user_role');
        $type = (string) ($this->request->getGet('type') ?? '');
        $period = $this->resolvePeriod();
        $start = $period['startDate'];
        $end = $period['endDate'];

        // Role guard untuk export yang perlu-perlu saja
        $allowedByRole = [
            'manajemen' => ['sales', 'purchase', 'stock'],
            'admin' => ['sales', 'purchase', 'stock'],
            'sales' => ['sales'],
            'purchasing' => ['purchase'],
            'gudang' => ['stock'],
        ];
        if (! isset($allowedByRole[$role]) || ! in_array($type, $allowedByRole[$role], true)) {
            return redirect()->to('/dashboard')->with('error', 'Peran Anda tidak diizinkan mengekspor jenis laporan tersebut.');
        }

        $db = \Config\Database::connect();
        $userName = (string) (session()->get('user_name') ?? 'Pengguna');
        $filename = 'laporan-' . $type . '-' . $start . '-sd-' . $end . '.xlsx';
        $metaLine = 'Periode: ' . $period['periodLabel'] . ' (' . $start . ' s/d ' . $end . ')   |   Dicetak oleh: ' . $userName . ' (' . ucfirst($role) . ')   |   ' . date('d/m/Y H:i') . ' WIB';
        $exporter = new \App\Libraries\SimpleXlsxExporter();

        if ($type === 'sales') {
            $rows = $db->table('sales_orders so')
                ->select('so.so_number, MIN(so.order_date) AS order_date, c.name AS customer_name, MIN(so.status) AS status, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_amount')
                ->join('customers c', 'c.id = so.customer_id')
                ->where('so.status <>', 'cancelled')
                ->where('so.order_date >=', $start)
                ->where('so.order_date <=', $end)
                ->groupBy('so.so_number, c.name')
                ->orderBy('order_date', 'DESC')
                ->get()
                ->getResultArray();

            $dataRows = [];
            $sumQty = 0;
            $sumVal = 0.0;
            $statusMap = [];
            foreach ($rows as $i => $r) {
                $st = status_label($r['status']);
                $q = (int) $r['total_qty'];
                $v = (float) $r['total_amount'];
                $sumQty += $q;
                $sumVal += $v;
                $statusMap[$st]['cnt'] = ($statusMap[$st]['cnt'] ?? 0) + 1;
                $statusMap[$st]['qty'] = ($statusMap[$st]['qty'] ?? 0) + $q;
                $statusMap[$st]['val'] = ($statusMap[$st]['val'] ?? 0.0) + $v;
                $dataRows[] = [$i + 1, $r['so_number'], date('d/m/Y', strtotime($r['order_date'])), $r['customer_name'], $q, $st, $v];
            }

            $exporter->addSheet([
                'name' => 'Rekap Penjualan',
                'title' => 'PT NUSANTARA DISTRIBUSI — LAPORAN PENJUALAN (SO)',
                'subtitle' => 'Rekapitulasi Pesanan Penjualan Pelanggan & Ringkasan Kinerja',
                'metaLine' => $metaLine,
                'showGridLines' => false,
                'kpis' => [
                    ['label' => 'TOTAL TRANSAKSI (SO)', 'value' => count($rows), 'type' => 'int'],
                    ['label' => 'TOTAL UNIT TERJUAL', 'value' => $sumQty, 'type' => 'int'],
                    ['label' => 'TOTAL NILAI PENJUALAN', 'value' => $sumVal, 'type' => 'currency'],
                ],
                'columns' => [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Nomor SO', 'width' => 19, 'type' => 'center'],
                    ['label' => 'Tanggal', 'width' => 14, 'type' => 'center'],
                    ['label' => 'Nama Pelanggan', 'width' => 36, 'type' => 'text'],
                    ['label' => 'Total Qty', 'width' => 14, 'type' => 'int'],
                    ['label' => 'Status', 'width' => 18, 'type' => 'status'],
                    ['label' => 'Total Nilai (Rp)', 'width' => 23, 'type' => 'currency'],
                ],
                'rows' => $dataRows,
                'totalColumns' => [4 => 'int', 6 => 'currency'],
            ]);

            $itemRows = $db->table('sales_orders so')
                ->select('so.so_number, so.order_date, c.name AS customer_name, p.code AS product_code, p.name AS product_name, so.qty, p.unit, so.unit_price, (so.qty * so.unit_price) AS subtotal, so.status')
                ->join('customers c', 'c.id = so.customer_id')
                ->join('products p', 'p.id = so.product_id')
                ->where('so.status <>', 'cancelled')
                ->where('so.order_date >=', $start)
                ->where('so.order_date <=', $end)
                ->orderBy('so.order_date', 'DESC')
                ->get()
                ->getResultArray();
            $detailRows = [];
            foreach ($itemRows as $i => $ir) {
                $detailRows[] = [$i + 1, $ir['so_number'], date('d/m/Y', strtotime($ir['order_date'])), $ir['customer_name'], $ir['product_code'], $ir['product_name'], (int) $ir['qty'], $ir['unit'], (float) $ir['unit_price'], (float) $ir['subtotal'], status_label($ir['status'])];
            }
            $exporter->addSheet([
                'name' => 'Rincian Item SO',
                'title' => 'PT NUSANTARA DISTRIBUSI — RINCIAN ITEM PENJUALAN',
                'subtitle' => 'Detail Produk per Pesanan Penjualan Pelanggan',
                'metaLine' => $metaLine,
                'columns' => [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Nomor SO', 'width' => 18, 'type' => 'center'],
                    ['label' => 'Tanggal', 'width' => 13, 'type' => 'center'],
                    ['label' => 'Pelanggan', 'width' => 28, 'type' => 'text'],
                    ['label' => 'Kode', 'width' => 12, 'type' => 'center'],
                    ['label' => 'Nama Produk', 'width' => 32, 'type' => 'text'],
                    ['label' => 'Qty', 'width' => 10, 'type' => 'int'],
                    ['label' => 'Satuan', 'width' => 10, 'type' => 'center'],
                    ['label' => 'Harga Satuan', 'width' => 18, 'type' => 'currency'],
                    ['label' => 'Subtotal', 'width' => 20, 'type' => 'currency'],
                    ['label' => 'Status', 'width' => 16, 'type' => 'status'],
                ],
                'rows' => $detailRows,
                'totalColumns' => [6 => 'int', 9 => 'currency'],
            ]);
        } elseif ($type === 'purchase') {
            $rows = $db->table('purchase_orders po')
                ->select('po.po_number, MIN(po.order_date) AS order_date, s.name AS supplier_name, MIN(po.status) AS status, SUM(po.qty) AS total_qty, SUM(po.qty * po.unit_price) AS total_amount')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->where('po.status <>', 'cancelled')
                ->where('po.order_date >=', $start)
                ->where('po.order_date <=', $end)
                ->groupBy('po.po_number, s.name')
                ->orderBy('order_date', 'DESC')
                ->get()
                ->getResultArray();

            $dataRows = [];
            $sumQty = 0;
            $sumVal = 0.0;
            foreach ($rows as $i => $r) {
                $st = status_label($r['status']);
                $q = (int) $r['total_qty'];
                $v = (float) $r['total_amount'];
                $sumQty += $q;
                $sumVal += $v;
                $dataRows[] = [$i + 1, $r['po_number'], date('d/m/Y', strtotime($r['order_date'])), $r['supplier_name'], $q, $st, $v];
            }

            $exporter->addSheet([
                'name' => 'Rekap Pembelian',
                'title' => 'PT NUSANTARA DISTRIBUSI — LAPORAN PEMBELIAN (PO)',
                'subtitle' => 'Rekapitulasi Pesanan Pembelian ke Pemasok & Ringkasan Belanja',
                'metaLine' => $metaLine,
                'showGridLines' => false,
                'kpis' => [
                    ['label' => 'TOTAL TRANSAKSI (PO)', 'value' => count($rows), 'type' => 'int'],
                    ['label' => 'TOTAL UNIT DIPESAN', 'value' => $sumQty, 'type' => 'int'],
                    ['label' => 'TOTAL NILAI PEMBELIAN', 'value' => $sumVal, 'type' => 'currency'],
                ],
                'columns' => [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Nomor PO', 'width' => 19, 'type' => 'center'],
                    ['label' => 'Tanggal', 'width' => 14, 'type' => 'center'],
                    ['label' => 'Nama Pemasok', 'width' => 36, 'type' => 'text'],
                    ['label' => 'Total Qty', 'width' => 14, 'type' => 'int'],
                    ['label' => 'Status', 'width' => 18, 'type' => 'status'],
                    ['label' => 'Total Nilai (Rp)', 'width' => 23, 'type' => 'currency'],
                ],
                'rows' => $dataRows,
                'totalColumns' => [4 => 'int', 6 => 'currency'],
            ]);

            $itemRows = $db->table('purchase_orders po')
                ->select('po.po_number, po.order_date, s.name AS supplier_name, p.code AS product_code, p.name AS product_name, po.qty, p.unit, po.unit_price, (po.qty * po.unit_price) AS subtotal, po.status')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->join('products p', 'p.id = po.product_id')
                ->where('po.status <>', 'cancelled')
                ->where('po.order_date >=', $start)
                ->where('po.order_date <=', $end)
                ->orderBy('po.order_date', 'DESC')
                ->get()
                ->getResultArray();
            $detailRows = [];
            foreach ($itemRows as $i => $ir) {
                $detailRows[] = [$i + 1, $ir['po_number'], date('d/m/Y', strtotime($ir['order_date'])), $ir['supplier_name'], $ir['product_code'], $ir['product_name'], (int) $ir['qty'], $ir['unit'], (float) $ir['unit_price'], (float) $ir['subtotal'], status_label($ir['status'])];
            }
            $exporter->addSheet([
                'name' => 'Rincian Item PO',
                'title' => 'PT NUSANTARA DISTRIBUSI — RINCIAN ITEM PEMBELIAN',
                'subtitle' => 'Detail Produk per Pesanan Pembelian ke Pemasok',
                'metaLine' => $metaLine,
                'columns' => [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Nomor PO', 'width' => 18, 'type' => 'center'],
                    ['label' => 'Tanggal', 'width' => 13, 'type' => 'center'],
                    ['label' => 'Pemasok', 'width' => 28, 'type' => 'text'],
                    ['label' => 'Kode', 'width' => 12, 'type' => 'center'],
                    ['label' => 'Nama Produk', 'width' => 32, 'type' => 'text'],
                    ['label' => 'Qty', 'width' => 10, 'type' => 'int'],
                    ['label' => 'Satuan', 'width' => 10, 'type' => 'center'],
                    ['label' => 'Harga Beli', 'width' => 18, 'type' => 'currency'],
                    ['label' => 'Subtotal', 'width' => 20, 'type' => 'currency'],
                    ['label' => 'Status', 'width' => 16, 'type' => 'status'],
                ],
                'rows' => $detailRows,
                'totalColumns' => [6 => 'int', 9 => 'currency'],
            ]);
        } else {
            $isGudang = $role === 'gudang';
            $rows = $db->table('products p')
                ->select('p.*, c.name AS category_name')
                ->join('categories c', 'c.id = p.category_id', 'left')
                ->orderBy('p.code', 'ASC')
                ->get()
                ->getResultArray();
            $dataRows = [];
            $sumUnits = 0;
            $sumAsset = 0.0;
            $lowCount = 0;
            foreach ($rows as $i => $p) {
                $qty = (int) $p['stock_qty'];
                $min = (int) $p['min_stock'];
                $sumUnits += $qty;
                if ($qty <= $min) $lowCount++;
                $lineVal = $qty * (float) $p['purchase_price'];
                $sumAsset += $lineVal;
                $stLabel = $qty <= 0 ? 'Habis' : ($qty <= $min ? 'Menipis' : 'Aman');
                if ($isGudang) {
                    $dataRows[] = [$i + 1, $p['code'], $p['name'], $p['category_name'] ?? '-', $qty, $p['unit'], $min, $stLabel];
                } else {
                    $dataRows[] = [$i + 1, $p['code'], $p['name'], $p['category_name'] ?? '-', $qty, $p['unit'], (float) $p['purchase_price'], $lineVal, $stLabel];
                }
            }

            $kpis = [
                ['label' => 'TOTAL JENIS PRODUK', 'value' => count($rows), 'type' => 'int'],
                ['label' => 'TOTAL STOK FISIK', 'value' => $sumUnits, 'type' => 'int'],
                ['label' => 'PRODUK STOK MENIPIS', 'value' => $lowCount, 'type' => 'int'],
            ];
            if (!$isGudang) {
                $kpis[] = ['label' => 'TOTAL NILAI PERSEDIAAN', 'value' => $sumAsset, 'type' => 'currency'];
            }

            $exporter->addSheet([
                'name' => 'Stok Produk',
                'title' => 'PT NUSANTARA DISTRIBUSI — LAPORAN STOK PRODUK',
                'subtitle' => $isGudang ? 'Daftar Persediaan Fisik Gudang (Tanpa Nilai Harga)' : 'Daftar Persediaan Fisik & Valuasi Aset Gudang',
                'metaLine' => $metaLine,
                'showGridLines' => false,
                'kpis' => $kpis,
                'columns' => $isGudang ? [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Kode Produk', 'width' => 15, 'type' => 'center'],
                    ['label' => 'Nama Produk', 'width' => 36, 'type' => 'text'],
                    ['label' => 'Kategori', 'width' => 22, 'type' => 'text'],
                    ['label' => 'Stok Fisik', 'width' => 14, 'type' => 'int'],
                    ['label' => 'Satuan', 'width' => 12, 'type' => 'center'],
                    ['label' => 'Stok Minimum', 'width' => 15, 'type' => 'int'],
                    ['label' => 'Status Stok', 'width' => 15, 'type' => 'status'],
                ] : [
                    ['label' => 'No', 'width' => 6, 'type' => 'center'],
                    ['label' => 'Kode Produk', 'width' => 15, 'type' => 'center'],
                    ['label' => 'Nama Produk', 'width' => 34, 'type' => 'text'],
                    ['label' => 'Kategori', 'width' => 20, 'type' => 'text'],
                    ['label' => 'Stok Fisik', 'width' => 13, 'type' => 'int'],
                    ['label' => 'Satuan', 'width' => 11, 'type' => 'center'],
                    ['label' => 'Harga Modal (Rp)', 'width' => 19, 'type' => 'currency'],
                    ['label' => 'Total Nilai (Rp)', 'width' => 22, 'type' => 'currency'],
                    ['label' => 'Status Stok', 'width' => 14, 'type' => 'status'],
                ],
                'rows' => $dataRows,
                'totalColumns' => $isGudang ? [4 => 'int'] : [4 => 'int', 7 => 'currency'],
            ]);
        }

        $xlsx = $exporter->buildBinary();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($xlsx);
    }

    public function exportPdf()
    {
        $role = (string) session()->get('user_role');
        $period = $this->resolvePeriod();
        $start = $period['startDate'];
        $end = $period['endDate'];

        $allowedSections = match ($role) {
            'manajemen', 'admin' => ['summary', 'sales', 'purchase', 'stock', 'top_products', 'low_stock'],
            'sales'              => ['summary', 'sales', 'top_products', 'low_stock'],
            'purchasing'         => ['summary', 'purchase', 'stock', 'low_stock'],
            'gudang'             => ['summary', 'stock', 'low_stock'],
            default              => ['summary', 'low_stock'],
        };

        $requested = (array) ($this->request->getGet('sections') ?? []);
        $sections = array_values(array_intersect($allowedSections, $requested));
        if ($sections === []) {
            $sections = $allowedSections;
        }

        $note = trim((string) ($this->request->getGet('report_note') ?? ''));
        $db = \Config\Database::connect();
        $isGudang = ($role === 'gudang');

        $data = [
            'role'       => $role,
            'userName'   => (string) (session()->get('user_name') ?? 'Pengguna'),
            'period'     => $period,
            'sections'   => $sections,
            'isGudang'   => $isGudang,
            'reportNote' => $note,
            'summary'    => [],
            'salesRows'  => [],
            'poRows'     => [],
            'stockRows'  => [],
            'topRows'    => [],
            'lowRows'    => [],
        ];

        if (in_array('summary', $sections, true)) {
            $soAgg = $db->query("SELECT COUNT(DISTINCT so_number) AS cnt, COALESCE(SUM(qty * unit_price), 0) AS total FROM sales_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$start, $end])->getRowArray();
            $poAgg = $db->query("SELECT COUNT(DISTINCT po_number) AS cnt, COALESCE(SUM(qty * unit_price), 0) AS total FROM purchase_orders WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?", [$start, $end])->getRowArray();
            $invAgg = $db->query("SELECT COUNT(*) AS sku_count, COALESCE(SUM(stock_qty), 0) AS total_units, COALESCE(SUM(stock_qty * purchase_price), 0) AS total_val FROM products")->getRowArray();
            $doAgg = $db->query("SELECT COUNT(DISTINCT delivery_number) AS cnt, COALESCE(SUM(qty_delivered), 0) AS qty FROM deliveries WHERE status <> 'cancelled' AND delivery_date BETWEEN ? AND ?", [$start, $end])->getRowArray();
            $data['summary'] = [
                'so_count'    => (int) ($soAgg['cnt'] ?? 0),
                'so_total'    => (float) ($soAgg['total'] ?? 0),
                'po_count'    => (int) ($poAgg['cnt'] ?? 0),
                'po_total'    => (float) ($poAgg['total'] ?? 0),
                'sku_count'   => (int) ($invAgg['sku_count'] ?? 0),
                'total_units' => (int) ($invAgg['total_units'] ?? 0),
                'inv_value'   => (float) ($invAgg['total_val'] ?? 0),
                'do_count'    => (int) ($doAgg['cnt'] ?? 0),
                'do_qty'      => (int) ($doAgg['qty'] ?? 0),
            ];
        }

        if (in_array('sales', $sections, true)) {
            $data['salesRows'] = $db->table('sales_orders so')
                ->select('so.so_number, MIN(so.order_date) AS order_date, c.name AS customer_name, MIN(so.status) AS status, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_amount')
                ->join('customers c', 'c.id = so.customer_id')
                ->where('so.status <>', 'cancelled')
                ->where('so.order_date >=', $start)
                ->where('so.order_date <=', $end)
                ->groupBy('so.so_number, c.name')
                ->orderBy('order_date', 'DESC')
                ->get()
                ->getResultArray();
        }

        if (in_array('purchase', $sections, true)) {
            $data['poRows'] = $db->table('purchase_orders po')
                ->select('po.po_number, MIN(po.order_date) AS order_date, s.name AS supplier_name, MIN(po.status) AS status, SUM(po.qty) AS total_qty, SUM(po.qty * po.unit_price) AS total_amount')
                ->join('suppliers s', 's.id = po.supplier_id')
                ->where('po.status <>', 'cancelled')
                ->where('po.order_date >=', $start)
                ->where('po.order_date <=', $end)
                ->groupBy('po.po_number, s.name')
                ->orderBy('order_date', 'DESC')
                ->get()
                ->getResultArray();
        }

        if (in_array('stock', $sections, true)) {
            $data['stockRows'] = $db->table('products p')
                ->select('p.code, p.name, c.name AS category_name, p.stock_qty, p.min_stock, p.unit, p.purchase_price')
                ->join('categories c', 'c.id = p.category_id', 'left')
                ->orderBy('p.code', 'ASC')
                ->get()
                ->getResultArray();
        }

        if (in_array('top_products', $sections, true)) {
            $data['topRows'] = $db->query("SELECT p.code, p.name, p.unit, COALESCE(SUM(so.qty), 0) AS total_qty, COALESCE(SUM(so.qty * so.unit_price), 0) AS total_sales FROM sales_orders so JOIN products p ON p.id = so.product_id WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? GROUP BY p.id, p.code, p.name, p.unit ORDER BY total_qty DESC LIMIT 10", [$start, $end])->getResultArray();
        }

        if (in_array('low_stock', $sections, true)) {
            $data['lowRows'] = $this->getLowStockProducts();
        }

        return view('dashboard/export_pdf', $data);
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
ORDER BY (CASE WHEN active_po.po_number IS NULL THEN 0 ELSE 1 END) ASC, p.stock_qty ASC
SQL
        )->getResultArray();
    }
}
