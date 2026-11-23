<?php
namespace App\Controllers;
class Reports extends BaseController
{
    protected function period(): array
    {
        $start = $this->request->getGet('start_date') ?? '';
        $end = $this->request->getGet('end_date') ?? '';
        if ($start === '') $start = date('Y-m-01');
        if ($end === '') $end = date('Y-m-t');
        return [$start, $end];
    }
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Hanya Admin dan Manajemen yang bisa melihat laporan.')) return $r;
        [$start, $end] = $this->period();
        return view('reports/index', ['title' => 'Laporan', 'start_date' => $start, 'end_date' => $end]);
    }
    public function purchase()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Hanya Admin dan Manajemen yang bisa melihat laporan.')) return $r;
        [$start, $end] = $this->period();
        $db = \Config\Database::connect();
        $pos = $db->table('purchase_orders po')->select('po.po_number, po.supplier_id, s.name AS supplier_name, MIN(po.order_date) AS order_date, MIN(po.status) AS status, SUM(po.qty * po.unit_price) AS total_amount')->join('suppliers s', 's.id = po.supplier_id')->where('po.order_date >=', $start)->where('po.order_date <=', $end)->groupBy('po.po_number, po.supplier_id, s.name')->orderBy('order_date', 'DESC')->get()->getResultArray();
        $grs = $db->table('goods_receipts gr')->select('gr.gr_number, MIN(po.po_number) AS po_number, MIN(gr.receipt_date) AS receipt_date')->join('purchase_orders po', 'po.id = gr.purchase_order_id')->where('gr.receipt_date >=', $start)->where('gr.receipt_date <=', $end)->groupBy('gr.gr_number')->orderBy('receipt_date', 'DESC')->get()->getResultArray();
        
        $total = 0;
        $totalCancelled = 0;
        $activeCount = 0;
        $cancelledCount = 0;
        foreach ($pos as $po) {
            if ($po['status'] !== 'cancelled') {
                $total += (float) $po['total_amount'];
                $activeCount++;
            } else {
                $totalCancelled += (float) $po['total_amount'];
                $cancelledCount++;
            }
        }
        return view('reports/purchase', [
            'title' => 'Laporan Pembelian',
            'pos' => $pos,
            'grs' => $grs,
            'total' => $total,
            'totalCancelled' => $totalCancelled,
            'activeCount' => $activeCount,
            'cancelledCount' => $cancelledCount,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }
    public function sales()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Hanya Admin dan Manajemen yang bisa melihat laporan.')) return $r;
        [$start, $end] = $this->period();
        $db = \Config\Database::connect();
        $sos = $db->table('sales_orders so')->select('so.so_number, so.customer_id, c.name AS customer_name, MIN(so.order_date) AS order_date, MIN(so.status) AS status, SUM(so.qty * so.unit_price) AS total_amount')->join('customers c', 'c.id = so.customer_id')->where('so.order_date >=', $start)->where('so.order_date <=', $end)->groupBy('so.so_number, so.customer_id, c.name')->orderBy('order_date', 'DESC')->get()->getResultArray();
        $dos = $db->table('deliveries d')->select('d.delivery_number, MIN(so.so_number) AS so_number, MIN(d.delivery_date) AS delivery_date')->join('sales_orders so', 'so.id = d.sales_order_id')->where('d.delivery_date >=', $start)->where('d.delivery_date <=', $end)->groupBy('d.delivery_number')->orderBy('delivery_date', 'DESC')->get()->getResultArray();
        
        $total = 0;
        $totalCancelled = 0;
        $activeCount = 0;
        $cancelledCount = 0;
        foreach ($sos as $so) {
            if ($so['status'] !== 'cancelled') {
                $total += (float) $so['total_amount'];
                $activeCount++;
            } else {
                $totalCancelled += (float) $so['total_amount'];
                $cancelledCount++;
            }
        }
        return view('reports/sales', [
            'title' => 'Laporan Penjualan',
            'sos' => $sos,
            'dos' => $dos,
            'total' => $total,
            'totalCancelled' => $totalCancelled,
            'activeCount' => $activeCount,
            'cancelledCount' => $cancelledCount,
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }
    public function stock()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Hanya Admin dan Manajemen yang bisa melihat laporan.')) return $r;
        [$start, $end] = $this->period();
        $db = \Config\Database::connect();
        $products = $db->table('products p')->select('p.*, c.name AS category_name')->join('categories c', 'c.id = p.category_id')->orderBy('p.code', 'ASC')->get()->getResultArray();
        $movements = $db->table('stock_movements sm')->select('sm.*, p.code, p.name AS product_name, w.name AS warehouse_name')->join('products p', 'p.id = sm.product_id')->join('warehouses w', 'w.id = sm.warehouse_id')->where('DATE(sm.created_at) >=', $start)->where('DATE(sm.created_at) <=', $end)->orderBy('sm.created_at', 'DESC')->orderBy('sm.id', 'DESC')->get()->getResultArray();
        $total = 0;
        foreach ($products as $p) $total += (int) $p['stock_qty'] * (float) $p['purchase_price'];
        return view('reports/stock', ['title' => 'Laporan Stok', 'products' => $products, 'movements' => $movements, 'total' => $total, 'start_date' => $start, 'end_date' => $end]);
    }
    public function topProducts()
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/dashboard', 'Hanya Admin dan Manajemen yang bisa melihat laporan.')) return $r;
        [$start, $end] = $this->period();
        $db = \Config\Database::connect();
        $items = $db->query("SELECT p.code, p.name, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales FROM sales_orders so JOIN products p ON p.id = so.product_id WHERE so.order_date BETWEEN ? AND ? AND so.status <> 'cancelled' GROUP BY p.id, p.code, p.name ORDER BY total_qty DESC LIMIT 10", [$start, $end])->getResultArray();
        return view('reports/top_products', ['title' => 'Produk Terlaris', 'items' => $items, 'start_date' => $start, 'end_date' => $end]);
    }
}
