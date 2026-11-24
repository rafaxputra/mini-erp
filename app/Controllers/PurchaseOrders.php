<?php
namespace App\Controllers;
use App\Models\ProductModel;
use App\Models\PurchaseOrderModel;
use App\Models\SupplierModel;
use App\Models\WarehouseModel;
class PurchaseOrders extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'purchasing', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pesanan Beli.')) return $r;
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = (string) ($this->request->getGet('status') ?? '');
        $start = (string) ($this->request->getGet('start_date') ?? '');
        $end = (string) ($this->request->getGet('end_date') ?? '');
        $statuses = ['draft', 'approved', 'received', 'cancelled'];
        if (! in_array($status, $statuses, true)) $status = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = '';
        $perPage = 10;
        $total = count($this->poListBuilder($q, $status, $start, $end)->get()->getResultArray());
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        if ($page > $lastPage) $page = $lastPage;
        $items = $this->poListBuilder($q, $status, $start, $end)->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        return view('purchase_orders/index', ['title' => 'Pesanan Beli', 'items' => $items, 'q' => $q, 'status' => $status, 'statuses' => $statuses, 'start_date' => $start, 'end_date' => $end, 'pager' => ['page' => $page, 'lastPage' => $lastPage, 'total' => $total, 'perPage' => $perPage, 'from' => $total ? ($page - 1) * $perPage + 1 : 0, 'to' => min($total, $page * $perPage)]]);
    }
    protected function poListBuilder(string $q, string $status, string $start, string $end)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('purchase_orders po')->select('MIN(po.id) AS id, po.po_number, po.supplier_id, s.name AS supplier_name, MIN(po.order_date) AS order_date, MIN(po.status) AS status, SUM(po.qty * po.unit_price) AS total_amount, MIN(u.name) AS creator_name, MAX(po.created_at) AS created_at')->join('suppliers s', 's.id = po.supplier_id')->join('users u', 'u.id = po.created_by');
        if ($q !== '') {
            $builder->groupStart()->like('po.po_number', $q, 'both', null, true)->orLike('s.name', $q, 'both', null, true)->groupEnd();
        }
        if ($status !== '') $builder->where('po.status', $status);
        if ($start !== '') $builder->where('po.order_date >=', $start);
        if ($end !== '') $builder->where('po.order_date <=', $end);
        return $builder->groupBy('po.po_number, po.supplier_id, s.name')->orderBy('order_date', 'DESC')->orderBy('created_at', 'DESC')->orderBy('po.po_number', 'DESC');
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/purchase-orders', 'Hanya Staff Purchasing dan Admin yang bisa membuat Pesanan Beli.')) return $r;
        $selectedProductId = (int) ($this->request->getGet('product_id') ?? 0);
        $suggestedQty = max(1, (int) ($this->request->getGet('qty') ?? 1));

        $existingActivePo = null;
        if ($selectedProductId > 0) {
            $existingActivePo = \Config\Database::connect()->table('purchase_orders')
                ->select('MIN(id) AS id, po_number, status, SUM(qty) AS ordered_qty')
                ->where('product_id', $selectedProductId)
                ->whereIn('status', ['draft', 'approved'])
                ->groupBy('po_number, status')
                ->orderBy('MIN(order_date)', 'DESC')
                ->get()
                ->getRowArray();
        }

        $products = \Config\Database::connect()->table('products p')
            ->select('p.id, p.code, p.name, p.unit, p.purchase_price, p.stock_qty, p.min_stock, p.max_stock,
                COALESCE((
                    SELECT SUM(po.qty - COALESCE((SELECT SUM(gr.qty_received) FROM goods_receipts gr WHERE gr.purchase_order_id = po.id), 0))
                    FROM purchase_orders po
                    WHERE po.product_id = p.id AND po.status IN (\'draft\', \'approved\')
                ), 0) AS on_order_qty')
            ->where('p.is_active', true)
            ->orderBy('p.code', 'ASC')
            ->get()
            ->getResultArray();

        return view('purchase_orders/create', [
            'title' => 'Buat Pesanan Beli',
            'suppliers' => (new SupplierModel())->orderBy('name', 'ASC')->findAll(),
            'products' => $products,
            'selectedProductId' => $selectedProductId,
            'suggestedQty' => $suggestedQty,
            'existingActivePo' => $existingActivePo,
        ]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/purchase-orders', 'Hanya Staff Purchasing dan Admin yang bisa membuat Pesanan Beli.')) return $r;
        $supplierId = (int) $this->request->getPost('supplier_id');
        $orderDate = $this->request->getPost('order_date');
        $productIds = (array) $this->request->getPost('product_id');
        $qtys = (array) $this->request->getPost('qty');
        $prices = (array) $this->request->getPost('unit_price');
        $errors = [];
        if (! $supplierId) $errors[] = 'Pemasok wajib dipilih.';
        if (! $orderDate) $errors[] = 'Tanggal pesanan wajib diisi.';
        if (empty($productIds)) $errors[] = 'Minimal satu detail produk wajib diisi.';
        if ($errors) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $lines = [];
            $seen = [];
            foreach ($productIds as $i => $pid) {
                $pid = (int) $pid;
                $qty = (int) ($qtys[$i] ?? 0);
                $price = (float) ($prices[$i] ?? 0);
                $row = $i + 1;
                if ($pid <= 0) {
                    throw new \RuntimeException('Baris ' . $row . ': produk wajib dipilih.');
                }
                $prod = (new ProductModel())->find($pid);
                if (! $prod) {
                    throw new \RuntimeException('Baris ' . $row . ': produk tidak ditemukan.');
                }
                $prodName = $prod['name'] . ' (' . $prod['code'] . ')';
                if ($qty <= 0) {
                    throw new \RuntimeException($prodName . ': jumlah harus lebih dari nol, diisi ' . $qty . '.');
                }
                if ($price < 0) {
                    throw new \RuntimeException($prodName . ': harga satuan tidak boleh negatif, diisi ' . $price . '.');
                }
                if (isset($seen[$pid])) {
                    throw new \RuntimeException($prodName . ' hanya boleh sekali dalam satu PO.');
                }

                // Validasi Batas Stok Maksimum (Max Stock Guard)
                $maxStock = (int) ($prod['max_stock'] ?? 0);
                if ($maxStock > 0) {
                    $onOrderRow = $db->table('purchase_orders po')
                        ->select('COALESCE(SUM(po.qty - COALESCE((SELECT SUM(gr.qty_received) FROM goods_receipts gr WHERE gr.purchase_order_id = po.id), 0)), 0) AS pending_qty')
                        ->where('po.product_id', $pid)
                        ->whereIn('po.status', ['draft', 'approved'])
                        ->get()
                        ->getRowArray();
                    $pendingQty = (int) ($onOrderRow['pending_qty'] ?? 0);
                    $totalProjected = (int) $prod['stock_qty'] + $pendingQty;

                    if ($totalProjected >= $maxStock) {
                        throw new \RuntimeException("Produk '{$prodName}' sudah mencapai kapasitas stok maksimal ({$maxStock} {$prod['unit']}). Stok fisik saat ini: {$prod['stock_qty']}, dalam pesanan berjalan: {$pendingQty}. Tidak diperbolehkan membuat pesanan baru.");
                    }

                    if ($totalProjected + $qty > $maxStock) {
                        $availableQuota = max(0, $maxStock - $totalProjected);
                        throw new \RuntimeException("Jumlah pesanan untuk '{$prodName}' ({$qty} {$prod['unit']}) melampaui kapasitas stok maksimal! Sisa kuota pesanan yang diperbolehkan adalah {$availableQuota} {$prod['unit']} (Maksimal: {$maxStock}, Fisik: {$prod['stock_qty']}, On-Order: {$pendingQty}).");
                    }
                }

                $seen[$pid] = true;
                $lines[] = ['product_id' => $pid, 'qty' => $qty, 'unit_price' => $price];
            }
            if (empty($lines)) {
                throw new \RuntimeException('Minimal satu detail produk wajib diisi.');
            }
            $poNumber = $this->nextNumber('purchase_orders', 'po_number', 'PO', 1001);
            $firstId = 0;
            foreach ($lines as $line) {
                $id = (new PurchaseOrderModel())->insert([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplierId,
                    'warehouse_id' => null,
                    'product_id' => $line['product_id'],
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'order_date' => $orderDate,
                    'status' => 'draft',
                    'notes' => $this->request->getPost('notes'),
                    'created_by' => $this->userId(),
                ], true);
                if (! $firstId) $firstId = (int) $id;
            }
            $db->transCommit();
            return redirect()->to('/purchase-orders/show/' . $firstId)->with('success', 'Pesanan Beli berhasil dibuat dengan status Draft. Menunggu persetujuan (ACC) Manajer.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    protected function poById(int $id): ?array
    {
        $db = \Config\Database::connect();
        $row = $db->table('purchase_orders po')
            ->select('po.*, s.name AS supplier_name, s.code AS supplier_code, u.name AS creator_name, w.name AS warehouse_name')
            ->join('suppliers s', 's.id = po.supplier_id')
            ->join('users u', 'u.id = po.created_by')
            ->join('warehouses w', 'w.id = po.warehouse_id', 'left')
            ->where('po.id', $id)
            ->get()
            ->getRowArray();
        if (! $row) return null;
        $row['total_amount'] = (new PurchaseOrderModel())->totalAmount($row['po_number']);
        return $row;
    }
    public function show($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pesanan Beli.')) return $r;
        $po = $this->poById((int) $id);
        if (! $po) return redirect()->to('/purchase-orders')->with('error', 'Pesanan Beli tidak ditemukan.');
        $db = \Config\Database::connect();
        $items = $db->table('purchase_orders po')->select('po.id, po.product_id, po.qty, po.unit_price, (po.qty * po.unit_price) AS subtotal, p.code, p.name AS product_name, p.unit, COALESCE((SELECT SUM(gr.qty_received) FROM goods_receipts gr WHERE gr.purchase_order_id = po.id), 0) AS received_qty')->join('products p', 'p.id = po.product_id')->where('po.po_number', $po['po_number'])->orderBy('po.id', 'ASC')->get()->getResultArray();
        $receipts = $db->table('goods_receipts gr')->select('MIN(gr.id) AS id, gr.gr_number, MIN(gr.receipt_date) AS receipt_date')->whereIn('gr.purchase_order_id', array_column($items, 'id'))->groupBy('gr.gr_number')->orderBy('receipt_date', 'DESC')->get()->getResultArray();
        return view('purchase_orders/show', ['title' => 'Detail Pesanan Beli', 'po' => $po, 'items' => $items, 'receipts' => $receipts]);
    }
    public function approve($id)
    {
        if ($r = $this->requireRole(['admin', 'manajemen'], '/purchase-orders', 'Hanya Manajer atau Admin yang berhak menyetujui (ACC) Pesanan Beli.')) return $r;
        $po = (new PurchaseOrderModel())->find((int) $id);
        if (! $po) return redirect()->to('/purchase-orders')->with('error', 'Pesanan Beli tidak ditemukan.');
        if ($po['status'] !== 'draft') {
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'Hanya PO berstatus Draft yang bisa di-approve (ACC).');
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $db->table('purchase_orders')->where('po_number', $po['po_number'])->where('status', 'draft')->update(['status' => 'approved', 'updated_at' => date('Y-m-d H:i:s')]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'Gagal menyetujui Pesanan Beli.');
        }
        return redirect()->to('/purchase-orders/show/' . $id)->with('success', 'Pesanan Beli berhasil disetujui (ACC). Stok siap diterima oleh petugas Gudang saat barang tiba.');
    }
    public function cancel($id)
    {
        if ($r = $this->requireRole(['admin', 'purchasing'], '/purchase-orders', 'Hanya Staff Purchasing dan Admin yang bisa membatalkan Pesanan Beli.')) return $r;
        $po = (new PurchaseOrderModel())->find((int) $id);
        if (! $po) return redirect()->to('/purchase-orders')->with('error', 'Pesanan Beli tidak ditemukan.');
        if ($po['status'] === 'received') {
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'PO yang sudah diterima penuh tidak bisa dibatalkan.');
        }
        if ($po['status'] === 'cancelled') {
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'PO sudah dibatalkan.');
        }

        $reason = trim((string) $this->request->getPost('cancel_reason'));
        if ($reason === '') {
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'Wajib menyertakan alasan pembatalan pesanan.');
        }

        $existingNotes = $po['notes'] ? trim($po['notes']) : '';
        $canceller = session()->get('user_name') ?: 'User';
        $cancelNote = "[Dibatalkan oleh {$canceller} pada " . date('d/m/Y H:i') . "]: " . $reason;
        $updatedNotes = $existingNotes !== '' ? $existingNotes . "\n" . $cancelNote : $cancelNote;

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $db->table('purchase_orders')
                ->where('po_number', $po['po_number'])
                ->where('status <>', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'notes' => $updatedNotes,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/purchase-orders/show/' . $id)->with('error', 'Gagal membatalkan Pesanan Beli.');
        }
        return redirect()->to('/purchase-orders/show/' . $id)->with('success', 'Pesanan Beli berhasil dibatalkan beserta alasannya.');
    }
}
