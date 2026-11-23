<?php
namespace App\Libraries;
use RuntimeException;
class StockService
{
    protected $db;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    public function defaultWarehouseId(): int
    {
        $row = $this->db->table('warehouses')->where('is_default', true)->get()->getRowArray();
        return $row ? (int) $row['id'] : 1;
    }
    protected function lockProduct(int $productId): array
    {
        $row = $this->db->query('SELECT * FROM products WHERE id = ? FOR UPDATE', [$productId])->getRowArray();
        if (! $row) {
            throw new RuntimeException('Produk tidak ditemukan.');
        }
        return $row;
    }
    protected function warehouseName(int $warehouseId): string
    {
        $wh = $this->db->table('warehouses')->where('id', $warehouseId)->get()->getRowArray();
        return $wh ? (string) $wh['name'] : ('Gudang ' . $warehouseId);
    }
    // Stok satu produk di satu gudang = saldo mutasi terakhir produk itu di gudang itu.
    public function stockInWarehouse(int $productId, int $warehouseId): int
    {
        $row = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? AND warehouse_id = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId, $warehouseId])->getRowArray();
        return $row ? (int) $row['qty_after'] : 0;
    }
    protected function recordMovement(int $productId, int $warehouseId, string $type, string $referenceType, int $referenceId, int $qty, int $before, int $after, int $userId, ?string $notes): void
    {
        $this->db->table('stock_movements')->insert([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'movement_type' => $type,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'qty' => $qty,
            'qty_before' => $before,
            'qty_after' => $after,
            'notes' => $notes,
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    // Total stok semua gudang = jumlah saldo terakhir tiap gudang.
    public function stockFromMovements(int $productId): int
    {
        $row = $this->db->query('SELECT COALESCE(SUM(qa), 0) AS total FROM (SELECT DISTINCT ON (warehouse_id) qty_after AS qa FROM stock_movements WHERE product_id = ? ORDER BY warehouse_id, created_at DESC, id DESC) s', [$productId])->getRowArray();
        return (int) $row['total'];
    }
    public function stockAtDate(int $productId, string $date): int
    {
        $row = $this->db->query('SELECT COALESCE(SUM(qa), 0) AS total FROM (SELECT DISTINCT ON (warehouse_id) qty_after AS qa FROM stock_movements WHERE product_id = ? AND created_at <= ? ORDER BY warehouse_id, created_at DESC, id DESC) s', [$productId, $date . ' 23:59:59'])->getRowArray();
        return (int) $row['total'];
    }
    public function receiveStock(int $productId, int $qty, string $referenceType, int $referenceId, int $userId, ?string $notes = null, ?int $warehouseId = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah terima harus lebih dari nol.');
        }
        $warehouseId = $warehouseId ?: $this->defaultWarehouseId();
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = $this->stockInWarehouse($productId, $warehouseId);
            $after = $before + $qty;
            $totalAfter = (int) $product['stock_qty'] + $qty;
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $totalAfter, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, $warehouseId, 'IN', $referenceType, $referenceId, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after, 'warehouse_id' => $warehouseId];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat penerimaan stok: ' . $e->getMessage());
        }
    }
    public function deliverStock(int $productId, int $qty, string $referenceType, int $referenceId, int $userId, ?string $notes = null, ?int $warehouseId = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah kirim harus lebih dari nol.');
        }
        $warehouseId = $warehouseId ?: $this->defaultWarehouseId();
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = $this->stockInWarehouse($productId, $warehouseId);
            if ($qty > $before) {
                throw new RuntimeException('Stok ' . $product['name'] . ' di ' . $this->warehouseName($warehouseId) . ' tidak mencukupi. Stok saat ini ' . $before . ', diminta ' . $qty . '.');
            }
            $after = $before - $qty;
            $totalAfter = (int) $product['stock_qty'] - $qty;
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $totalAfter, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, $warehouseId, 'OUT', $referenceType, $referenceId, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after, 'warehouse_id' => $warehouseId];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat pengiriman stok: ' . $e->getMessage());
        }
    }
    public function adjustStock(int $productId, int $qty, string $direction, int $userId, ?string $notes = null, ?int $warehouseId = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah penyesuaian harus lebih dari nol.');
        }
        if ($direction !== 'IN' && $direction !== 'OUT') {
            throw new RuntimeException('Arah penyesuaian tidak valid.');
        }
        $warehouseId = $warehouseId ?: $this->defaultWarehouseId();
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = $this->stockInWarehouse($productId, $warehouseId);
            $after = $direction === 'IN' ? $before + $qty : $before - $qty;
            if ($after < 0) {
                throw new RuntimeException('Penyesuaian ditolak. Stok ' . $product['name'] . ' di ' . $this->warehouseName($warehouseId) . ' hanya ' . $before . ', tidak bisa dikurangi ' . $qty . '.');
            }
            $totalAfter = (int) $product['stock_qty'] + ($direction === 'IN' ? $qty : -$qty);
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $totalAfter, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, $warehouseId, 'ADJUSTMENT', 'ADJUSTMENT', 0, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after, 'warehouse_id' => $warehouseId];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat penyesuaian stok: ' . $e->getMessage());
        }
    }
}
