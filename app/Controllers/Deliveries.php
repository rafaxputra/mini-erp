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
        if ($r = $this->requireRole(['admin', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pengiriman.')) return $r;
        $db = \Config\Database::connect();
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $warehouseId = (int) ($this->request->getGet('warehouse_id') ?? 0);
        $start = trim((string) ($this->request->getGet('start_date') ?? ''));
        $end = trim((string) ($this->request->getGet('end_date') ?? ''));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = '';

        $pendingSoQuery = $db->table('sales_orders so')
            ->select('MIN(so.id) AS id, so.so_number, MIN(so.order_date) AS order_date, MIN(c.name) AS customer_name, MIN(w.name) AS warehouse_name, COUNT(so.id) AS item_count, SUM(so.qty) AS total_qty, MIN(so.notes) AS notes')
            ->join('customers c', 'c.id = so.customer_id', 'left')
            ->join('warehouses w', 'w.id = so.warehouse_id', 'left')
            ->where('so.status', 'confirmed');

        if ($status === 'pending_so') {
            if ($warehouseId > 0) {
                $pendingSoQuery->where('so.warehouse_id', $warehouseId);
            }
            if ($start !== '') {
                $pendingSoQuery->where('so.order_date >=', $start);
            }
            if ($end !== '') {
                $pendingSoQuery->where('so.order_date <=', $end);
            }
        }

        $pendingSoQuery->groupBy('so.so_number');

        if ($status === 'pending_so' && $q !== '') {
            $pendingSoQuery->havingLike('so.so_number', $q, 'both', null, true)
                ->orHavingLike('MIN(c.name)', $q, 'both', null, true)
                ->orHavingLike('MIN(w.name)', $q, 'both', null, true);
        }

        $pendingSos = $pendingSoQuery->orderBy('order_date', 'DESC')->get()->getResultArray();
        $pendingSoTotalCount = (int) ($db->query("SELECT COUNT(DISTINCT so_number) AS c FROM sales_orders WHERE status = 'confirmed'")->getRow('c') ?? 0);

        $countRows = $db->query("SELECT COALESCE(status, 'delivered') AS st, COUNT(DISTINCT delivery_number) AS cnt FROM deliveries GROUP BY COALESCE(status, 'delivered')")->getResultArray();
        $statusCounts = ['all' => 0, 'scheduled' => 0, 'delivered' => 0, 'cancelled' => 0, 'pending_so' => $pendingSoTotalCount];
        foreach ($countRows as $cr) {
            $stKey = (string) $cr['st'];
            $cnt = (int) $cr['cnt'];
            if (isset($statusCounts[$stKey])) {
                $statusCounts[$stKey] = $cnt;
            }
            $statusCounts['all'] += $cnt;
        }

        $subQuery = $db->table('deliveries d')
            ->select('MIN(d.id) AS id, d.delivery_number, MIN(so.so_number) AS so_number, MIN(c.name) AS customer_name, MIN(d.delivery_date) AS delivery_date, MIN(d.delivery_time) AS delivery_time, MIN(d.status) AS status, MIN(d.cancel_reason) AS cancel_reason, MIN(d.notes) AS notes, MIN(u.name) AS deliverer_name, MIN(w.name) AS warehouse_name, MAX(d.created_at) AS created_at')
            ->join('sales_orders so', 'so.id = d.sales_order_id')
            ->join('customers c', 'c.id = so.customer_id', 'left')
            ->join('users u', 'u.id = d.delivered_by')
            ->join('warehouses w', 'w.id = d.warehouse_id');

        if (in_array($status, ['scheduled', 'delivered', 'cancelled'], true)) {
            $subQuery->where('d.status', $status);
        }

        if ($warehouseId > 0) {
            $subQuery->where('d.warehouse_id', $warehouseId);
        }
        if ($start !== '') {
            $subQuery->where('d.delivery_date >=', $start);
        }
        if ($end !== '') {
            $subQuery->where('d.delivery_date <=', $end);
        }

        $subQuery->groupBy('d.delivery_number');

        if ($q !== '') {
            $subQuery->havingLike('d.delivery_number', $q, 'both', null, true)
                ->orHavingLike('MIN(so.so_number)', $q, 'both', null, true)
                ->orHavingLike('MIN(c.name)', $q, 'both', null, true)
                ->orHavingLike('MIN(w.name)', $q, 'both', null, true);
        }

        $allRows = $subQuery->orderBy('delivery_date', 'DESC')->orderBy('created_at', 'DESC')->get()->getResultArray();
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

        return view('deliveries/index', [
            'title' => 'Pengiriman',
            'items' => $items,
            'pendingSos' => $pendingSos,
            'status' => $status,
            'statusCounts' => $statusCounts,
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
        if ($r = $this->requireRole(['gudang'], '/deliveries', 'Hanya Staff Gudang yang bisa mencatat Pengiriman.')) return $r;
        $db = \Config\Database::connect();
        $soId = (int) ($this->request->getGet('sales_order_id') ?? 0);
        $sos = $db->table('sales_orders so')
            ->select('MIN(so.id) AS id, so.so_number, MIN(so.order_date) AS order_date, MIN(c.name) AS customer_name, MIN(w.name) AS warehouse_name, COUNT(so.id) AS item_count, SUM(so.qty) AS total_qty')
            ->join('customers c', 'c.id = so.customer_id', 'left')
            ->join('warehouses w', 'w.id = so.warehouse_id', 'left')
            ->where('so.status', 'confirmed')
            ->groupBy('so.so_number')
            ->orderBy('order_date', 'DESC')
            ->get()
            ->getResultArray();
        $so = null;
        $items = [];
        if ($soId) {
            $so = $db->table('sales_orders so')->select('so.*, c.name AS customer_name, c.code AS customer_code, w.name AS warehouse_name')->join('customers c', 'c.id = so.customer_id')->join('warehouses w', 'w.id = so.warehouse_id')->where('so.id', $soId)->get()->getRowArray();
            if (! $so) {
                return redirect()->to('/deliveries/create')->with('error', 'Pesanan Jual tidak ditemukan.');
            }
            if ($so['status'] !== 'confirmed') {
                return redirect()->to('/deliveries/create')->with('error', 'Pengiriman hanya bisa diproses untuk pesanan penjualan yang sudah Dikonfirmasi.');
            }
            $items = $db->table('sales_orders so')->select('so.id, so.product_id, so.qty, so.unit_price, p.code, p.name AS product_name, p.unit, p.stock_qty, COALESCE((SELECT SUM(d.qty_delivered) FROM deliveries d WHERE d.sales_order_id = so.id AND d.status <> \'cancelled\'), 0) AS delivered_qty')->join('products p', 'p.id = so.product_id')->where('so.so_number', $so['so_number'])->orderBy('so.id', 'ASC')->get()->getResultArray();
            
            // Hitung stok siap kirim untuk SO ini: sisa alokasi yang sudah dipotong di SO + stok bebas baru di gudang
            $stockSvc = new \App\Libraries\StockService();
            foreach ($items as &$it) {
                $freeWhStock = $stockSvc->stockInWarehouse((int) $it['product_id'], (int) $so['warehouse_id']);
                $soDeducted = (int) ($db->query("SELECT COALESCE(SUM(qty), 0) AS total FROM stock_movements WHERE reference_type = 'SALES_ORDER' AND movement_type = 'OUT' AND reference_id = ?", [(int) $it['id']])->getRowArray()['total']);
                $allocatedRemaining = max(0, $soDeducted - (int) $it['delivered_qty']);
                $it['allocated_remaining'] = $allocatedRemaining;
                $it['free_wh_stock'] = $freeWhStock;
                $it['warehouse_stock'] = $allocatedRemaining + $freeWhStock;
            }
            unset($it);
            $itemsByProduct = [];
            foreach ($items as $itRow) {
                $sisaRow = max(0, (int) $itRow['qty'] - (int) $itRow['delivered_qty']);
                $itemsByProduct[(int) $itRow['product_id']] = [
                    'sisa' => $sisaRow,
                    'warehouse_stock' => (int) $itRow['warehouse_stock'],
                ];
            }

            $rawAlerts = $db->table('restock_alerts ra')
                ->select('ra.*, p.code AS product_code, p.name AS product_name')
                ->join('products p', 'p.id = ra.product_id')
                ->where('ra.sales_order_number', $so['so_number'])
                ->whereIn('ra.status', ['open', 'ordered'])
                ->get()
                ->getResultArray();

            $shortageAlerts = array_values(array_filter($rawAlerts, static function ($al) use ($itemsByProduct) {
                $pid = (int) $al['product_id'];
                if (! isset($itemsByProduct[$pid])) {
                    return false;
                }
                return $itemsByProduct[$pid]['warehouse_stock'] < $itemsByProduct[$pid]['sisa'];
            }));
        }
        return view('deliveries/create', [
            'title' => 'Buat Pengiriman',
            'sos' => $sos,
            'so' => $so,
            'items' => $items,
            'shortageAlerts' => $shortageAlerts ?? [],
            'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['gudang'], '/deliveries', 'Hanya Staff Gudang yang bisa mencatat Pengiriman.')) return $r;
        $soId = (int) $this->request->getPost('sales_order_id');
        $deliveryDate = $this->request->getPost('delivery_date');
        $deliveryTime = trim((string) ($this->request->getPost('delivery_time') ?? ''));
        if (! preg_match('/^\d{2}:\d{2}$/', $deliveryTime)) {
            $deliveryTime = date('H:i');
        }
        $deliveryStatus = $this->request->getPost('delivery_status') === 'scheduled' ? 'scheduled' : 'delivered';
        if ($deliveryStatus === 'delivered') {
            $deliveryDate = date('Y-m-d');
        }
        $qtys = (array) $this->request->getPost('qty_delivered');
        $so = (new SalesOrderModel())->find($soId);
        if (! $so) {
            return redirect()->back()->withInput()->with('error', 'Pesanan Jual tidak ditemukan.');
        }
        if ($so['status'] !== 'confirmed') {
            return redirect()->back()->withInput()->with('error', 'Pengiriman hanya bisa diproses untuk pesanan penjualan yang sudah Dikonfirmasi.');
        }
        if (! $deliveryDate) {
            return redirect()->back()->withInput()->with('error', 'Tanggal kirim wajib diisi.');
        }
        if ($deliveryStatus === 'scheduled' && $deliveryDate < date('Y-m-d')) {
            return redirect()->back()->withInput()->with('error', 'Tanggal jadwal pengiriman tidak boleh sebelum hari ini.');
        }
        // Kunci gudang pengiriman strictly mengikuti alokasi gudang pada Pesanan Jual (SO)
        $warehouseId = (int) $so['warehouse_id'];
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $stock = new StockService();
            $lines = [];
            foreach ($qtys as $soRowId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) continue;
                $soRow = $db->table('sales_orders')->where('id', (int) $soRowId)->where('so_number', $so['so_number'])->get()->getRowArray();
                if (! $soRow) {
                    throw new \RuntimeException('Detail SO tidak ditemukan.');
                }
                $delivered = (int) ($db->query("SELECT COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE sales_order_id = ? AND status <> 'cancelled'", [(int) $soRowId])->getRowArray()['total']);
                $remaining = (int) $soRow['qty'] - $delivered;
                if ($qty > $remaining) {
                    $prod = (new ProductModel())->find((int) $soRow['product_id']);
                    $prodName = $prod ? $prod['name'] : ('Produk ' . $soRow['product_id']);
                    throw new \RuntimeException('Jumlah kirim melebihi sisa. ' . $prodName . ' sisa kirim ' . $remaining . ', diminta ' . $qty . '.');
                }
                $freeWhStock = $stock->stockInWarehouse((int) $soRow['product_id'], $warehouseId);
                $soDeducted = (int) ($db->query("SELECT COALESCE(SUM(qty), 0) AS total FROM stock_movements WHERE reference_type = 'SALES_ORDER' AND movement_type = 'OUT' AND reference_id = ?", [(int) $soRowId])->getRowArray()['total']);
                $allocatedRemaining = max(0, $soDeducted - $delivered);
                $readyStock = $allocatedRemaining + $freeWhStock;
                if ($qty > $readyStock) {
                    $prod = (new ProductModel())->find((int) $soRow['product_id']);
                    $prodName = $prod ? $prod['name'] : ('Produk ' . $soRow['product_id']);
                    $whRow = (new WarehouseModel())->find($warehouseId);
                    $whName = $whRow ? $whRow['name'] : ('Gudang ' . $warehouseId);
                    throw new \RuntimeException('Stok ' . $prodName . ' di ' . $whName . ' tidak mencukupi. Stok siap kirim ' . $readyStock . ', diminta ' . $qty . '.');
                }
                $lines[] = ['sales_order_id' => (int) $soRowId, 'product_id' => (int) $soRow['product_id'], 'qty_delivered' => $qty, 'already_delivered' => $delivered, 'so_deducted' => $soDeducted];
            }
            if (empty($lines)) {
                throw new \RuntimeException('Isi minimal satu jumlah kirim yang lebih dari nol.');
            }
            $doNumber = $this->nextNumber('deliveries', 'delivery_number', 'DO', 1004);
            foreach ($lines as $line) {
                $doRowId = (new DeliveryModel())->insert([
                    'delivery_number' => $doNumber,
                    'sales_order_id' => $line['sales_order_id'], 'warehouse_id' => $warehouseId, 'product_id' => $line['product_id'],
                    'qty_delivered' => $line['qty_delivered'], 'delivery_date' => $deliveryDate, 'delivery_time' => $deliveryTime,
                    'status' => $deliveryStatus,
                    'notes' => $this->request->getPost('notes'), 'delivered_by' => $this->userId(),
                ], true);

                // Stok sudah dipotong saat SO dibuat sejumlah stok yang tersedia saat itu.
                // Jika total pengiriman melebihi yang sudah dipotong di SO (kasus kekurangan stok / backorder yang baru masuk via GR), potong selisihnya sekarang.
                $totalAfter = $line['already_delivered'] + $line['qty_delivered'];
                $prevCovered = max($line['so_deducted'], $line['already_delivered']);
                $extraToDeduct = max(0, $totalAfter - $prevCovered);
                if ($extraToDeduct > 0) {
                    $stock->deliverStock($line['product_id'], $extraToDeduct, 'SALES_DELIVERY', (int) $doRowId, $this->userId(), 'Kirim SO ' . $so['so_number'], $warehouseId);
                }
            }
            if ($deliveryStatus === 'delivered') {
                $soRows = $db->table('sales_orders')->select('id, qty')->where('so_number', $so['so_number'])->get()->getResultArray();
                $full = true;
                foreach ($soRows as $row) {
                    $delivered = (int) ($db->query("SELECT COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE sales_order_id = ? AND status = 'delivered'", [(int) $row['id']])->getRowArray()['total']);
                    if ($delivered < (int) $row['qty']) { $full = false; break; }
                }
                if ($full) {
                    $db->table('sales_orders')->where('so_number', $so['so_number'])->update(['status' => 'delivered', 'updated_at' => date('Y-m-d H:i:s')]);
                    $db->table('restock_alerts')->where('sales_order_number', $so['so_number'])->whereIn('status', ['open', 'ordered'])->update(['status' => 'resolved', 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
            $db->transCommit();
            $msg = $deliveryStatus === 'scheduled'
                ? 'Pengiriman berhasil dijadwalkan (Status: Terjadwal).'
                : 'Pengiriman berhasil disimpan (Status: Terkirim).';
            return redirect()->to('/deliveries')->with('success', $msg);
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function complete($id)
    {
        if ($r = $this->requireRole(['gudang'], '/deliveries', 'Hanya Staff Gudang yang bisa menyelesaikan Pengiriman.')) return $r;
        $do = (new DeliveryModel())->find((int) $id);
        if (! $do) {
            return redirect()->to('/deliveries')->with('error', 'Data pengiriman tidak ditemukan.');
        }
        if ($do['status'] !== 'scheduled') {
            return redirect()->to('/deliveries')->with('error', 'Hanya pengiriman berstatus Terjadwal yang dapat diselesaikan.');
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $doRowIds = array_map('intval', array_column(
                $db->table('deliveries')
                    ->select('id')
                    ->where('delivery_number', $do['delivery_number'])
                    ->where('created_at', $do['created_at'])
                    ->where('status', 'scheduled')
                    ->get()
                    ->getResultArray(),
                'id'
            ));
            if (empty($doRowIds)) {
                $doRowIds = [(int) $do['id']];
            }

            $db->table('deliveries')
                ->whereIn('id', $doRowIds)
                ->where('status', 'scheduled')
                ->update(['status' => 'delivered', 'updated_at' => date('Y-m-d H:i:s')]);

            $soRow = (new SalesOrderModel())->find((int) $do['sales_order_id']);
            if ($soRow) {
                $soRows = $db->table('sales_orders')->select('id, qty')->where('so_number', $soRow['so_number'])->get()->getResultArray();
                $full = true;
                foreach ($soRows as $row) {
                    $delivered = (int) ($db->query("SELECT COALESCE(SUM(qty_delivered), 0) AS total FROM deliveries WHERE sales_order_id = ? AND status = 'delivered'", [(int) $row['id']])->getRowArray()['total']);
                    if ($delivered < (int) $row['qty']) { $full = false; break; }
                }
                if ($full) {
                    $db->table('sales_orders')->where('so_number', $soRow['so_number'])->where('status', 'confirmed')->update(['status' => 'delivered', 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }
            $db->transCommit();
            return redirect()->to('/deliveries')->with('success', 'Pengiriman ' . $do['delivery_number'] . ' berhasil ditandai Terkirim.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/deliveries')->with('error', 'Gagal menyelesaikan pengiriman: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        if ($r = $this->requireRole(['gudang'], '/deliveries', 'Hanya Staff Gudang yang bisa membatalkan Pengiriman.')) return $r;
        $do = (new DeliveryModel())->find((int) $id);
        if (! $do) {
            return redirect()->to('/deliveries')->with('error', 'Data pengiriman tidak ditemukan.');
        }
        if ($do['status'] !== 'scheduled') {
            return redirect()->to('/deliveries')->with('error', 'Hanya pengiriman berstatus Terjadwal yang dapat dibatalkan. Pengiriman yang sudah Terkirim tidak bisa dibatalkan.');
        }
        $reason = trim((string) $this->request->getPost('cancel_reason'));
        if ($reason === '') {
            return redirect()->to('/deliveries')->with('error', 'Wajib menyertakan alasan pembatalan pengiriman.');
        }
        $cancelType = $this->request->getPost('cancel_type') === 'cancel_order' ? 'cancel_order' : 'revision';

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $doRows = $db->table('deliveries')
                ->where('delivery_number', $do['delivery_number'])
                ->where('created_at', $do['created_at'])
                ->where('status <>', 'cancelled')
                ->get()
                ->getResultArray();
            $doRowIds = array_map('intval', array_column($doRows, 'id'));
            if (empty($doRowIds)) {
                $doRowIds = [(int) $do['id']];
            }

            // Kembalikan stok jika DO ini sempat memotong selisih stok tambahan (kasus backorder)
            if (! empty($doRowIds)) {
                $movements = $db->table('stock_movements')
                    ->where('reference_type', 'SALES_DELIVERY')
                    ->where('movement_type', 'OUT')
                    ->whereIn('reference_id', $doRowIds)
                    ->get()
                    ->getResultArray();
                $stockSvc = new StockService();
                foreach ($movements as $mov) {
                    $stockSvc->receiveStock(
                        (int) $mov['product_id'],
                        (int) $mov['qty'],
                        'CANCEL_DELIVERY',
                        (int) $mov['reference_id'],
                        $this->userId(),
                        'Batal DO ' . $do['delivery_number'] . ': ' . $reason,
                        (int) $mov['warehouse_id']
                    );
                }
            }

            $db->table('deliveries')
                ->whereIn('id', $doRowIds)
                ->where('status <>', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $reason,
                    'cancelled_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            // Cek apakah masih ada DO lain yang aktif pada SO induk
            $soRow = (new SalesOrderModel())->find((int) $do['sales_order_id']);
            $soAction = 'none';
            if ($soRow) {
                $otherActiveDoCount = $db->table('deliveries d')
                    ->join('sales_orders so', 'so.id = d.sales_order_id')
                    ->where('so.so_number', $soRow['so_number'])
                    ->where('d.status <>', 'cancelled')
                    ->countAllResults();

                if ($otherActiveDoCount === 0 && $soRow['status'] !== 'cancelled') {
                    $existingNotes = $soRow['notes'] ? trim($soRow['notes']) : '';
                    $canceller = session()->get('user_name') ?: 'Staff Gudang';

                    if ($cancelType === 'cancel_order') {
                        // Opsi 1: Customer batal total -> kembalikan stok & batalkan SO
                        $soRows = $db->table('sales_orders')->select('id')->where('so_number', $soRow['so_number'])->get()->getResultArray();
                        $soRowIds = array_column($soRows, 'id');
                        if (! empty($soRowIds)) {
                            $soMovements = $db->table('stock_movements')
                                ->where('reference_type', 'SALES_ORDER')
                                ->where('movement_type', 'OUT')
                                ->whereIn('reference_id', $soRowIds)
                                ->get()
                                ->getResultArray();
                            $stockSvc = new StockService();
                            foreach ($soMovements as $mov) {
                                $stockSvc->receiveStock(
                                    (int) $mov['product_id'],
                                    (int) $mov['qty'],
                                    'CANCEL_SALES_ORDER',
                                    (int) $mov['reference_id'],
                                    $this->userId(),
                                    'Batal SO ' . $soRow['so_number'] . ' (via Batal DO ' . $do['delivery_number'] . ')',
                                    (int) $mov['warehouse_id']
                                );
                            }
                        }

                        $cancelNote = "[Dibatalkan oleh {$canceller} via Pembatalan DO {$do['delivery_number']} pada " . date('d/m/Y H:i') . "]: " . $reason;
                        $updatedNotes = $existingNotes !== '' ? $existingNotes . "\n" . $cancelNote : $cancelNote;

                        $db->table('sales_orders')
                            ->where('so_number', $soRow['so_number'])
                            ->where('status <>', 'cancelled')
                            ->update([
                                'status' => 'cancelled',
                                'notes' => $updatedNotes,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        $db->table('restock_alerts')
                            ->where('sales_order_number', $soRow['so_number'])
                            ->whereIn('status', ['open', 'ordered'])
                            ->update([
                                'status' => 'cancelled',
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        $soAction = 'cancelled';
                    } else {
                        // Opsi 2: Customer minta revisi -> SO masuk status Butuh Revisi, stok tetap ditahan hingga Sales simpan revisi
                        $revNote = "[Butuh Revisi dari {$canceller} via DO {$do['delivery_number']} pada " . date('d/m/Y H:i') . "]: " . $reason;
                        $updatedNotes = $existingNotes !== '' ? $existingNotes . "\n" . $revNote : $revNote;

                        $db->table('sales_orders')
                            ->where('so_number', $soRow['so_number'])
                            ->where('status <>', 'cancelled')
                            ->update([
                                'status' => 'revision',
                                'notes' => $updatedNotes,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        $soAction = 'revision';
                    }
                } elseif ($soRow['status'] === 'delivered') {
                    $db->table('sales_orders')
                        ->where('so_number', $soRow['so_number'])
                        ->where('status', 'delivered')
                        ->update(['status' => 'confirmed', 'updated_at' => date('Y-m-d H:i:s')]);
                }
            }

            $db->transCommit();
            if ($soAction === 'cancelled') {
                $successMsg = 'Pengiriman ' . $do['delivery_number'] . ' dan Pesanan Jual ' . $soRow['so_number'] . ' dibatalkan. Seluruh stok telah dikembalikan ke gudang.';
            } elseif ($soAction === 'revision') {
                $successMsg = 'Pengiriman ' . $do['delivery_number'] . ' dibatalkan. Pesanan Jual ' . $soRow['so_number'] . ' kini berstatus Butuh Revisi untuk ditindaklanjuti Staff Sales.';
            } else {
                $successMsg = 'Pengiriman ' . $do['delivery_number'] . ' berhasil dibatalkan.';
            }
            return redirect()->to('/deliveries')->with('success', $successMsg);
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/deliveries')->with('error', 'Gagal membatalkan pengiriman: ' . $e->getMessage());
        }
    }
}
