<?php
namespace App\Controllers;
use App\Libraries\StockService;
use App\Models\GoodsReceiptModel;
use App\Models\ProductModel;
use App\Models\PurchaseOrderModel;
use App\Models\WarehouseModel;
class GoodsReceipts extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Barang Masuk.')) return $r;
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $warehouseId = (int) ($this->request->getGet('warehouse_id') ?? 0);
        $start = trim((string) ($this->request->getGet('start_date') ?? ''));
        $end = trim((string) ($this->request->getGet('end_date') ?? ''));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = '';

        $pendingPoQuery = $db->table('purchase_orders po')
            ->select('MIN(po.id) AS id, po.po_number, MIN(po.order_date) AS order_date, MIN(s.name) AS supplier_name, COUNT(po.id) AS item_count, SUM(po.qty) AS total_qty, MIN(po.notes) AS notes')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->where('po.status', 'approved');

        if ($status === 'pending_po') {
            if ($start !== '') {
                $pendingPoQuery->where('po.order_date >=', $start);
            }
            if ($end !== '') {
                $pendingPoQuery->where('po.order_date <=', $end);
            }
        }

        $pendingPoQuery->groupBy('po.po_number');

        if ($status === 'pending_po' && $q !== '') {
            $pendingPoQuery->havingLike('po.po_number', $q, 'both', null, true)
                ->orHavingLike('MIN(s.name)', $q, 'both', null, true);
        }

        $pendingPos = $pendingPoQuery->orderBy('order_date', 'DESC')->get()->getResultArray();
        $pendingPoTotalCount = (int) ($db->query("SELECT COUNT(DISTINCT po_number) AS c FROM purchase_orders WHERE status = 'approved'")->getRow('c') ?? 0);

        $subQuery = $db->table('goods_receipts gr')
            ->select('MIN(gr.id) AS id, gr.gr_number, MIN(po.po_number) AS po_number, MIN(s.name) AS supplier_name, MIN(gr.receipt_date) AS receipt_date, MIN(gr.notes) AS notes, MIN(u.name) AS receiver_name, MIN(w.name) AS warehouse_name, MAX(gr.created_at) AS created_at')
            ->join('purchase_orders po', 'po.id = gr.purchase_order_id')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->join('users u', 'u.id = gr.received_by')
            ->join('warehouses w', 'w.id = gr.warehouse_id');

        if ($warehouseId > 0) {
            $subQuery->where('gr.warehouse_id', $warehouseId);
        }
        if ($start !== '') {
            $subQuery->where('gr.receipt_date >=', $start);
        }
        if ($end !== '') {
            $subQuery->where('gr.receipt_date <=', $end);
        }

        $subQuery->groupBy('gr.gr_number');

        if ($q !== '') {
            $subQuery->havingLike('gr.gr_number', $q, 'both', null, true)
                ->orHavingLike('MIN(po.po_number)', $q, 'both', null, true)
                ->orHavingLike('MIN(s.name)', $q, 'both', null, true)
                ->orHavingLike('MIN(w.name)', $q, 'both', null, true);
        }

        $allRows = $subQuery->orderBy('receipt_date', 'DESC')->orderBy('created_at', 'DESC')->get()->getResultArray();
        $total = count($allRows);
        $perPage = $this->resolvePerPage(7);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        if ($page > $lastPage) $page = $lastPage;

        $items = array_slice($allRows, ($page - 1) * $perPage, $perPage);
        $warehouses = (new WarehouseModel())->orderBy('id', 'ASC')->findAll();

        $pager = [
            'page' => $page,
            'lastPage' => $lastPage,
            'total' => $total,
            'perPage' => $perPage,
            'from' => $total ? ($page - 1) * $perPage + 1 : 0,
            'to' => min($total, $page * $perPage),
        ];

        $allGrCount = (int) ($db->query("SELECT COUNT(DISTINCT gr_number) AS c FROM goods_receipts")->getRow('c') ?? 0);

        return view('goods_receipts/index', [
            'title' => 'Barang Masuk',
            'items' => $items,
            'pendingPos' => $pendingPos,
            'status' => $status,
            'statusCounts' => ['all' => $allGrCount, 'pending_po' => $pendingPoTotalCount],
            'q' => $q,
            'warehouses' => $warehouses,
            'warehouseId' => $warehouseId,
            'start_date' => $start,
            'end_date' => $end,
            'pager' => $pager,
        ]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['gudang'], '/goods-receipts', 'Hanya Staff Gudang yang bisa mencatat Barang Masuk.')) return $r;
        $db = \Config\Database::connect();
        $poId = (int) ($this->request->getGet('purchase_order_id') ?? 0);
        $pos = $db->table('purchase_orders po')
            ->select('MIN(po.id) AS id, po.po_number, MIN(po.order_date) AS order_date, MIN(s.name) AS supplier_name, COUNT(po.id) AS item_count, SUM(po.qty) AS total_qty')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->where('po.status', 'approved')
            ->groupBy('po.po_number')
            ->orderBy('order_date', 'DESC')
            ->get()
            ->getResultArray();
        $po = null;
        $items = [];
        if ($poId) {
            $po = (new PurchaseOrderModel())->find($poId);
            if (! $po) {
                return redirect()->to('/goods-receipts/create')->with('error', 'Pesanan Beli tidak ditemukan.');
            }
            if ($po['status'] !== 'approved') {
                return redirect()->to('/goods-receipts/create')->with('error', 'Penerimaan barang hanya bisa diproses untuk pesanan pembelian yang sudah Disetujui.');
            }
            $items = $db->table('purchase_orders po')->select('po.id, po.product_id, po.qty, po.unit_price, p.code, p.name AS product_name, p.unit, COALESCE((SELECT SUM(gr.qty_received) FROM goods_receipts gr WHERE gr.purchase_order_id = po.id), 0) AS received_qty')->join('products p', 'p.id = po.product_id')->where('po.po_number', $po['po_number'])->orderBy('po.id', 'ASC')->get()->getResultArray();
        }
        return view('goods_receipts/create', ['title' => 'Terima Barang', 'pos' => $pos, 'po' => $po, 'items' => $items, 'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['gudang'], '/goods-receipts', 'Hanya Staff Gudang yang bisa mencatat Barang Masuk.')) return $r;
        $poId = (int) $this->request->getPost('purchase_order_id');
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $receiptDate = $this->request->getPost('receipt_date');
        $qtys = (array) $this->request->getPost('qty_received');
        $po = (new PurchaseOrderModel())->find($poId);
        if (! $po) {
            return redirect()->back()->withInput()->with('error', 'Pesanan Beli tidak ditemukan.');
        }
        if ($po['status'] !== 'approved') {
            return redirect()->back()->withInput()->with('error', 'Penerimaan barang hanya bisa diproses untuk pesanan pembelian yang sudah Disetujui.');
        }
        if (! $receiptDate) {
            return redirect()->back()->withInput()->with('error', 'Tanggal terima wajib diisi.');
        }
        $warehouseId = $this->resolveWarehouseId($warehouseId);
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $lines = [];
            foreach ($qtys as $poRowId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                $poRow = $db->table('purchase_orders')->where('id', (int) $poRowId)->where('po_number', $po['po_number'])->get()->getRowArray();
                if (! $poRow) {
                    throw new \RuntimeException('Detail PO tidak ditemukan.');
                }
                $received = (int) ($db->query('SELECT COALESCE(SUM(qty_received), 0) AS total FROM goods_receipts WHERE purchase_order_id = ?', [(int) $poRowId])->getRowArray()['total']);
                $remaining = (int) $poRow['qty'] - $received;
                if ($qty > $remaining) {
                    $prod = (new ProductModel())->find((int) $poRow['product_id']);
                    $prodName = $prod ? $prod['name'] : ('Produk ' . $poRow['product_id']);
                    throw new \RuntimeException("{$prodName}: jumlah yang diterima ({$qty}) melebihi sisa pesanan yang belum datang ({$remaining}).");
                }
                $lines[] = ['purchase_order_id' => (int) $poRowId, 'product_id' => (int) $poRow['product_id'], 'qty_received' => $qty];
            }
            if (empty($lines)) {
                throw new \RuntimeException('Isi minimal satu jumlah terima yang lebih dari nol.');
            }
            $grNumber = $this->nextNumber('goods_receipts', 'gr_number', 'GR', 1003);
            $stock = new StockService();
            foreach ($lines as $line) {
                $grRowId = (new GoodsReceiptModel())->insert([
                    'gr_number' => $grNumber,
                    'purchase_order_id' => $line['purchase_order_id'], 'warehouse_id' => $warehouseId, 'product_id' => $line['product_id'],
                    'qty_received' => $line['qty_received'], 'receipt_date' => $receiptDate,
                    'notes' => $this->request->getPost('notes'), 'received_by' => $this->userId(),
                ], true);
                $stock->receiveStock($line['product_id'], $line['qty_received'], 'PURCHASE_RECEIPT', (int) $grRowId, $this->userId(), 'Terima PO ' . $po['po_number'], $warehouseId);

                // Tutup notifikasi kekurangan stok (restock_alerts) di gudang ini jika stok baru sudah mencukupi
                $currentWhStock = $stock->stockInWarehouse($line['product_id'], $warehouseId);
                $openAlerts = $db->table('restock_alerts')
                    ->where('product_id', $line['product_id'])
                    ->where('warehouse_id', $warehouseId)
                    ->whereIn('status', ['open', 'ordered'])
                    ->orderBy('created_at', 'ASC')
                    ->get()
                    ->getResultArray();
                $remainingCover = $currentWhStock;
                foreach ($openAlerts as $al) {
                    if ($remainingCover >= (int) $al['shortage_qty']) {
                        $db->table('restock_alerts')->where('id', $al['id'])->update([
                            'status' => 'resolved',
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        $remainingCover -= (int) $al['shortage_qty'];
                    }
                }
            }
            $poRows = $db->table('purchase_orders')->select('id, qty')->where('po_number', $po['po_number'])->get()->getResultArray();
            $full = true;
            foreach ($poRows as $row) {
                $received = (int) ($db->query('SELECT COALESCE(SUM(qty_received), 0) AS total FROM goods_receipts WHERE purchase_order_id = ?', [(int) $row['id']])->getRowArray()['total']);
                if ($received < (int) $row['qty']) { $full = false; break; }
            }
            if ($full) {
                $db->table('purchase_orders')->where('po_number', $po['po_number'])->update(['status' => 'received', 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $db->transCommit();
            return redirect()->to('/goods-receipts')->with('success', 'Barang Masuk berhasil disimpan. Stok bertambah sesuai jumlah yang diterima.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        if ($r = $this->requireRole(['admin', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Detail Barang Masuk.')) return $r;
        $grRow = (new GoodsReceiptModel())->find((int) $id);
        if (! $grRow) {
            return redirect()->to('/goods-receipts')->with('error', 'Data penerimaan barang tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $items = $db->table('goods_receipts gr')
            ->select('gr.id, gr.gr_number, gr.receipt_date, gr.qty_received, gr.notes, gr.created_at, po.id AS po_id, po.po_number, po.order_date AS po_date, po.qty AS po_qty, p.code AS sku, p.name AS product_name, p.unit, s.name AS supplier_name, u.name AS receiver_name, w.name AS warehouse_name')
            ->join('purchase_orders po', 'po.id = gr.purchase_order_id')
            ->join('products p', 'p.id = po.product_id')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->join('users u', 'u.id = gr.received_by', 'left')
            ->join('warehouses w', 'w.id = gr.warehouse_id', 'left')
            ->where('gr.gr_number', $grRow['gr_number'])
            ->orderBy('gr.id', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($items)) {
            return redirect()->to('/goods-receipts')->with('error', 'Data penerimaan barang tidak ditemukan.');
        }

        $first = $items[0];
        $role = (string) session()->get('user_role');

        return view('goods_receipts/show', [
            'title' => 'Detail Barang Masuk ' . $first['gr_number'],
            'gr' => $first,
            'items' => $items,
            'canViewPo' => in_array($role, ['admin', 'purchasing', 'manajemen'], true),
        ]);
    }
}
