<?php
namespace App\Controllers;
use App\Libraries\StockService;
use App\Models\DeliveryModel;
use App\Models\ProductModel;
use App\Models\SalesOrderModel;
use App\Models\WarehouseModel;
class Deliveries extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'gudang', 'sales', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pengiriman.')) return $r;
        $db = \Config\Database::connect();
        $items = $db->table('deliveries d')->select('MIN(d.id) AS id, d.delivery_number, MIN(so.so_number) AS so_number, MIN(d.delivery_date) AS delivery_date, MIN(d.notes) AS notes, MIN(u.name) AS deliverer_name, MIN(w.name) AS warehouse_name, MAX(d.created_at) AS created_at')->join('sales_orders so', 'so.id = d.sales_order_id')->join('users u', 'u.id = d.delivered_by')->join('warehouses w', 'w.id = d.warehouse_id')->groupBy('d.delivery_number')->orderBy('created_at', 'DESC')->get()->getResultArray();
        return view('deliveries/index', ['title' => 'Pengiriman', 'items' => $items]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'gudang'], '/deliveries', 'Hanya Staff Gudang dan Admin yang bisa mencatat Pengiriman.')) return $r;
        $db = \Config\Database::connect();
        $soId = (int) ($this->request->getGet('sales_order_id') ?? 0);
        $sos = $db->table('sales_orders')->select('MIN(id) AS id, so_number, MIN(order_date) AS order_date')->where('status', 'confirmed')->groupBy('so_number')->orderBy('order_date', 'DESC')->get()->getResultArray();
        $so = null;
        $items = [];
        if ($soId) {
            $so = $db->table('sales_orders so')->select('so.*, c.name AS customer_name, c.code AS customer_code, w.name AS warehouse_name')->join('customers c', 'c.id = so.customer_id')->join('warehouses w', 'w.id = so.warehouse_id')->where('so.id', $soId)->get()->getRowArray();
            if (! $so) {
                return redirect()->to('/deliveries/create')->with('error', 'Pesanan Jual tidak ditemukan.');
            }
            if ($so['status'] !== 'confirmed') {
                return redirect()->to('/deliveries/create')->with('error', 'Pengiriman hanya bisa dibuat untuk SO berstatus Confirmed. SO ini berstatus ' . $so['status'] . '.');
            }
            $items = $db->table('sales_orders so')->select('so.id, so.product_id, so.qty, so.unit_price, p.code, p.name AS product_name, p.unit, p.stock_qty, COALESCE((SELECT SUM(d.qty_delivered) FROM deliveries d WHERE d.sales_order_id = so.id), 0) AS delivered_qty')->join('products p', 'p.id = so.product_id')->where('so.so_number', $so['so_number'])->orderBy('so.id', 'ASC')->get()->getResultArray();
        }
        return view('deliveries/create', ['title' => 'Buat Pengiriman', 'sos' => $sos, 'so' => $so, 'items' => $items, 'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'gudang'], '/deliveries', 'Hanya Staff Gudang dan Admin yang bisa mencatat Pengiriman.')) return $r;
        $soId = (int) $this->request->getPost('sales_order_id');
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $deliveryDate = $this->request->getPost('delivery_date');
        $qtys = (array) $this->request->getPost('qty_delivered');
        $so = (new SalesOrderModel())->find($soId);
        if (! $so) {
            return redirect()->back()->withInput()->with('error', 'Pesanan Jual tidak ditemukan.');
        }
        if ($so['status'] !== 'confirmed') {
            return redirect()->back()->withInput()->with('error', 'Pengiriman hanya bisa dibuat untuk SO berstatus Confirmed.');
        }
        if (! $deliveryDate) {
            return redirect()->back()->withInput()->with('error', 'Tanggal kirim wajib diisi.');
        }
        $warehouseId = $this->resolveWarehouseId($warehouseId);
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $lines = [];
            foreach ($qtys as $soRowId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                $soRow = $db->table('sales_orders')->where('id', (int) $soRowId)->where('so_number', $so['so_number'])->get()->getRowArray();
                if (! $soRow) {
                    throw new \RuntimeException('Detail SO tidak ditemukan.');
                }
                $delivered = (int) ($db->query('SELECT COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE sales_order_id = ?', [(int) $soRowId])->getRowArray()['total']);
                $remaining = (int) $soRow['qty'] - $delivered;
                if ($qty > $remaining) {
                    $prod = (new ProductModel())->find((int) $soRow['product_id']);
                    $prodName = $prod ? $prod['name'] : ('Produk ' . $soRow['product_id']);
                    throw new \RuntimeException('Jumlah kirim melebihi sisa. ' . $prodName . ' sisa kirim ' . $remaining . ', diminta ' . $qty . '.');
                }
                $lines[] = ['sales_order_id' => (int) $soRowId, 'product_id' => (int) $soRow['product_id'], 'qty_delivered' => $qty];
            }
            if (empty($lines)) {
                throw new \RuntimeException('Isi minimal satu jumlah kirim yang lebih dari nol.');
            }
            $doNumber = $this->nextNumber('deliveries', 'delivery_number', 'DO', 1004);
            $stock = new StockService();
            foreach ($lines as $line) {
                $doRowId = (new DeliveryModel())->insert([
                    'delivery_number' => $doNumber,
                    'sales_order_id' => $line['sales_order_id'], 'warehouse_id' => $warehouseId, 'product_id' => $line['product_id'],
                    'qty_delivered' => $line['qty_delivered'], 'delivery_date' => $deliveryDate,
                    'notes' => $this->request->getPost('notes'), 'delivered_by' => $this->userId(),
                ], true);
                $stock->deliverStock($line['product_id'], $line['qty_delivered'], 'SALES_DELIVERY', (int) $doRowId, $this->userId(), 'Kirim SO ' . $so['so_number'], $warehouseId);
            }
            $soRows = $db->table('sales_orders')->select('id, qty')->where('so_number', $so['so_number'])->get()->getResultArray();
            $full = true;
            foreach ($soRows as $row) {
                $delivered = (int) ($db->query('SELECT COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE sales_order_id = ?', [(int) $row['id']])->getRowArray()['total']);
                if ($delivered < (int) $row['qty']) { $full = false; break; }
            }
            if ($full) {
                $db->table('sales_orders')->where('so_number', $so['so_number'])->update(['status' => 'delivered', 'updated_at' => date('Y-m-d H:i:s')]);
            }
            $db->transCommit();
            return redirect()->to('/deliveries')->with('success', 'Pengiriman berhasil disimpan. Stok berkurang sesuai jumlah yang dikirim.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
