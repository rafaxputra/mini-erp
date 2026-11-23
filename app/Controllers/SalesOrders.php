<?php
namespace App\Controllers;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\WarehouseModel;
use App\Models\SalesOrderModel;
class SalesOrders extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin', 'sales', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pesanan Jual.')) return $r;
        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $status = (string) ($this->request->getGet('status') ?? '');
        $start = (string) ($this->request->getGet('start_date') ?? '');
        $end = (string) ($this->request->getGet('end_date') ?? '');
        $statuses = ['draft', 'confirmed', 'delivered', 'cancelled'];
        if (! in_array($status, $statuses, true)) $status = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) $start = '';
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) $end = '';
        $perPage = 10;
        $total = count($this->soListBuilder($q, $status, $start, $end)->get()->getResultArray());
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        if ($page > $lastPage) $page = $lastPage;
        $items = $this->soListBuilder($q, $status, $start, $end)->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        return view('sales_orders/index', ['title' => 'Pesanan Jual', 'items' => $items, 'q' => $q, 'status' => $status, 'statuses' => $statuses, 'start_date' => $start, 'end_date' => $end, 'pager' => ['page' => $page, 'lastPage' => $lastPage, 'total' => $total, 'perPage' => $perPage, 'from' => $total ? ($page - 1) * $perPage + 1 : 0, 'to' => min($total, $page * $perPage)]]);
    }
    protected function soListBuilder(string $q, string $status, string $start, string $end)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('sales_orders so')->select('MIN(so.id) AS id, so.so_number, so.customer_id, c.name AS customer_name, MIN(so.order_date) AS order_date, MIN(so.status) AS status, SUM(so.qty * so.unit_price) AS total_amount, MIN(u.name) AS creator_name, MAX(so.created_at) AS created_at')->join('customers c', 'c.id = so.customer_id')->join('users u', 'u.id = so.created_by');
        if ($q !== '') {
            $builder->groupStart()->like('so.so_number', $q, 'both', null, true)->orLike('c.name', $q, 'both', null, true)->groupEnd();
        }
        if ($status !== '') $builder->where('so.status', $status);
        if ($start !== '') $builder->where('so.order_date >=', $start);
        if ($end !== '') $builder->where('so.order_date <=', $end);
        return $builder->groupBy('so.so_number, so.customer_id, c.name')->orderBy('order_date', 'DESC')->orderBy('created_at', 'DESC')->orderBy('so.so_number', 'DESC');
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/sales-orders', 'Hanya Staff Sales dan Admin yang bisa membuat Pesanan Jual.')) return $r;
        return view('sales_orders/create', ['title' => 'Buat Pesanan Jual', 'customers' => (new CustomerModel())->orderBy('name', 'ASC')->findAll(), 'warehouses' => (new WarehouseModel())->orderBy('name', 'ASC')->findAll(), 'products' => (new ProductModel())->where('is_active', true)->orderBy('code', 'ASC')->findAll()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/sales-orders', 'Hanya Staff Sales dan Admin yang bisa membuat Pesanan Jual.')) return $r;
        $customerId = (int) $this->request->getPost('customer_id');
        $warehouseId = (int) $this->request->getPost('warehouse_id');
        $orderDate = $this->request->getPost('order_date');
        $productIds = (array) $this->request->getPost('product_id');
        $qtys = (array) $this->request->getPost('qty');
        $prices = (array) $this->request->getPost('unit_price');
        $errors = [];
        if (! $customerId) $errors[] = 'Pelanggan wajib dipilih.';
        $warehouseId = $this->resolveWarehouseId($warehouseId);
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
                    throw new \RuntimeException($prodName . ' hanya boleh sekali dalam satu SO.');
                }
                $seen[$pid] = true;
                $lines[] = ['product_id' => $pid, 'qty' => $qty, 'unit_price' => $price];
            }
            if (empty($lines)) {
                throw new \RuntimeException('Minimal satu detail produk wajib diisi.');
            }
            $soNumber = $this->nextNumber('sales_orders', 'so_number', 'SO', 1002);
            $firstId = 0;
            foreach ($lines as $line) {
                $id = (new SalesOrderModel())->insert([
                    'so_number' => $soNumber,
                    'customer_id' => $customerId, 'warehouse_id' => $warehouseId, 'product_id' => $line['product_id'],
                    'qty' => $line['qty'], 'unit_price' => $line['unit_price'],
                    'order_date' => $orderDate, 'status' => 'draft',
                    'notes' => $this->request->getPost('notes'), 'created_by' => $this->userId(),
                ], true);
                if (! $firstId) $firstId = (int) $id;
            }
            $db->transCommit();
            return redirect()->to('/sales-orders/show/' . $firstId)->with('success', 'Pesanan Jual berhasil dibuat dengan status Draft. Stok belum berubah.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
    protected function soById(int $id): ?array
    {
        $db = \Config\Database::connect();
        $row = $db->table('sales_orders so')->select('so.*, c.name AS customer_name, c.code AS customer_code, u.name AS creator_name, w.name AS warehouse_name')->join('customers c', 'c.id = so.customer_id')->join('users u', 'u.id = so.created_by')->join('warehouses w', 'w.id = so.warehouse_id')->where('so.id', $id)->get()->getRowArray();
        if (! $row) return null;
        $row['total_amount'] = (new SalesOrderModel())->totalAmount($row['so_number']);
        return $row;
    }
    public function show($id)
    {
        if ($r = $this->requireRole(['admin', 'sales', 'gudang', 'manajemen'], '/dashboard', 'Peran Anda tidak diizinkan melihat Pesanan Jual.')) return $r;
        $so = $this->soById((int) $id);
        if (! $so) return redirect()->to('/sales-orders')->with('error', 'Pesanan Jual tidak ditemukan.');
        $db = \Config\Database::connect();
        $items = $db->table('sales_orders so')->select('so.id, so.product_id, so.qty, so.unit_price, (so.qty * so.unit_price) AS subtotal, p.code, p.name AS product_name, p.unit, p.stock_qty, COALESCE((SELECT SUM(d.qty_delivered) FROM deliveries d WHERE d.sales_order_id = so.id), 0) AS delivered_qty')->join('products p', 'p.id = so.product_id')->where('so.so_number', $so['so_number'])->orderBy('so.id', 'ASC')->get()->getResultArray();
        $deliveries = $db->table('deliveries d')->select('MIN(d.id) AS id, d.delivery_number, MIN(d.delivery_date) AS delivery_date')->whereIn('d.sales_order_id', array_column($items, 'id'))->groupBy('d.delivery_number')->orderBy('delivery_date', 'DESC')->get()->getResultArray();
        return view('sales_orders/show', ['title' => 'Detail Pesanan Jual', 'so' => $so, 'items' => $items, 'deliveries' => $deliveries]);
    }
    public function confirm($id)
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/sales-orders', 'Hanya Staff Sales dan Admin yang bisa confirm Pesanan Jual.')) return $r;
        $so = (new SalesOrderModel())->find((int) $id);
        if (! $so) return redirect()->to('/sales-orders')->with('error', 'Pesanan Jual tidak ditemukan.');
        if ($so['status'] !== 'draft') {
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'Hanya SO berstatus Draft yang bisa di confirm.');
        }
        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $db->table('sales_orders')->where('so_number', $so['so_number'])->where('status', 'draft')->update(['status' => 'confirmed', 'updated_at' => date('Y-m-d H:i:s')]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'Gagal confirm Pesanan Jual.');
        }
        return redirect()->to('/sales-orders/show/' . $id)->with('success', 'Pesanan Jual berhasil di confirm.');
    }
    public function cancel($id)
    {
        if ($r = $this->requireRole(['admin', 'sales'], '/sales-orders', 'Hanya Staff Sales dan Admin yang bisa membatalkan Pesanan Jual.')) return $r;
        $so = (new SalesOrderModel())->find((int) $id);
        if (! $so) return redirect()->to('/sales-orders')->with('error', 'Pesanan Jual tidak ditemukan.');
        if ($so['status'] === 'delivered') {
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'SO yang sudah terkirim penuh tidak bisa dibatalkan.');
        }
        if ($so['status'] === 'cancelled') {
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'SO sudah dibatalkan.');
        }

        $reason = trim((string) $this->request->getPost('cancel_reason'));
        if ($reason === '') {
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'Wajib menyertakan alasan pembatalan pesanan.');
        }

        $existingNotes = $so['notes'] ? trim($so['notes']) : '';
        $canceller = session()->get('user_name') ?: 'User';
        $cancelNote = "[Dibatalkan oleh {$canceller} pada " . date('d/m/Y H:i') . "]: " . $reason;
        $updatedNotes = $existingNotes !== '' ? $existingNotes . "\n" . $cancelNote : $cancelNote;

        $db = \Config\Database::connect();
        $db->transBegin();
        try {
            $db->table('sales_orders')
                ->where('so_number', $so['so_number'])
                ->where('status <>', 'cancelled')
                ->update([
                    'status' => 'cancelled',
                    'notes' => $updatedNotes,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->to('/sales-orders/show/' . $id)->with('error', 'Gagal membatalkan Pesanan Jual.');
        }
        return redirect()->to('/sales-orders/show/' . $id)->with('success', 'Pesanan Jual berhasil dibatalkan beserta alasannya.');
    }
}
