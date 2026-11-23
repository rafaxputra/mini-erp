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
        if ($r = $this->requireRole(['admin', 'gudang', 'purchasing', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Barang Masuk.')) return $r;
        $db = \Config\Database::connect();
        $items = $db->table('goods_receipts gr')->select('MIN(gr.id) AS id, gr.gr_number, MIN(po.po_number) AS po_number, MIN(gr.receipt_date) AS receipt_date, MIN(gr.notes) AS notes, MIN(u.name) AS receiver_name, MIN(w.name) AS warehouse_name, MAX(gr.created_at) AS created_at')->join('purchase_orders po', 'po.id = gr.purchase_order_id')->join('users u', 'u.id = gr.received_by')->join('warehouses w', 'w.id = gr.warehouse_id')->groupBy('gr.gr_number')->orderBy('created_at', 'DESC')->get()->getResultArray();
        return view('goods_receipts/index', ['title' => 'Barang Masuk', 'items' => $items]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'gudang'], '/goods-receipts', 'Hanya Staff Gudang dan Admin yang bisa mencatat Barang Masuk.')) return $r;
        $db = \Config\Database::connect();
        $poId = (int) ($this->request->getGet('purchase_order_id') ?? 0);
        $pos = $db->table('purchase_orders')->select('MIN(id) AS id, po_number, MIN(order_date) AS order_date')->where('status', 'approved')->groupBy('po_number')->orderBy('order_date', 'DESC')->get()->getResultArray();
        $po = null;
        $items = [];
        if ($poId) {
            $po = (new PurchaseOrderModel())->find($poId);
            if (! $po) {
                return redirect()->to('/goods-receipts/create')->with('error', 'Pesanan Beli tidak ditemukan.');
            }
            if ($po['status'] !== 'approved') {
                return redirect()->to('/goods-receipts/create')->with('error', 'Barang Masuk hanya bisa dibuat untuk PO berstatus Approved. PO ini berstatus ' . $po['status'] . '.');
            }
            $items = $db->table('purchase_orders po')->select('po.id, po.product_id, po.qty, po.unit_price, p.code, p.name AS product_name, p.unit, COALESCE((SELECT SUM(gr.qty_received) FROM goods_receipts gr WHERE gr.purchase_order_id = po.id), 0) AS received_qty')->join('products p', 'p.id = po.product_id')->where('po.po_number', $po['po_number'])->orderBy('po.id', 'ASC')->get()->getResultArray();
        }
        return view('goods_receipts/create', ['title' => 'Terima Barang', 'pos' => $pos, 'po' => $po, 'items' => $items, 'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'gudang'], '/goods-receipts', 'Hanya Staff Gudang dan Admin yang bisa mencatat Barang Masuk.')) return $r;
        $poId = (int) $this->request->getPost('purchase_order_id');
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $receiptDate = $this->request->getPost('receipt_date');
        $qtys = (array) $this->request->getPost('qty_received');
        $po = (new PurchaseOrderModel())->find($poId);
        if (! $po) {
            return redirect()->back()->withInput()->with('error', 'Pesanan Beli tidak ditemukan.');
        }
        if ($po['status'] !== 'approved') {
            return redirect()->back()->withInput()->with('error', 'Barang Masuk hanya bisa dibuat untuk PO berstatus Approved.');
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
                    throw new \RuntimeException('Jumlah terima melebihi sisa. ' . $prodName . ' sisa terima ' . $remaining . ', diminta ' . $qty . '.');
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
}
