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
    protected function defaultWarehouseId(): int
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
    protected function recordMovement(int $productId, string $type, string $referenceType, int $referenceId, int $qty, int $before, int $after, int $userId, ?string $notes): void
    {
        $this->db->table('stock_movements')->insert([
            'product_id' => $productId,
            'warehouse_id' => $this->defaultWarehouseId(),
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
    public function stockFromMovements(int $productId): int
    {
        $row = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId])->getRowArray();
        return $row ? (int) $row['qty_after'] : 0;
    }
    public function stockAtDate(int $productId, string $date): int
    {
        $row = $this->db->query('SELECT qty_after FROM stock_movements WHERE product_id = ? AND created_at <= ? ORDER BY created_at DESC, id DESC LIMIT 1', [$productId, $date . ' 23:59:59'])->getRowArray();
        return $row ? (int) $row['qty_after'] : 0;
    }
    public function receiveStock(int $productId, int $qty, string $referenceType, int $referenceId, int $userId, ?string $notes = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah terima harus lebih dari nol.');
        }
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = (int) $product['stock_qty'];
            $after = $before + $qty;
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $after, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, 'IN', $referenceType, $referenceId, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat penerimaan stok: ' . $e->getMessage());
        }
    }
    public function deliverStock(int $productId, int $qty, string $referenceType, int $referenceId, int $userId, ?string $notes = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah kirim harus lebih dari nol.');
        }
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = (int) $product['stock_qty'];
            if ($qty > $before) {
                throw new RuntimeException('Stok ' . $product['name'] . ' tidak mencukupi. Stok saat ini ' . $before . ', diminta ' . $qty . '.');
            }
            $after = $before - $qty;
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $after, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, 'OUT', $referenceType, $referenceId, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat pengiriman stok: ' . $e->getMessage());
        }
    }
    public function adjustStock(int $productId, int $qty, string $direction, int $userId, ?string $notes = null): array
    {
        if ($qty <= 0) {
            throw new RuntimeException('Jumlah penyesuaian harus lebih dari nol.');
        }
        if ($direction !== 'IN' && $direction !== 'OUT') {
            throw new RuntimeException('Arah penyesuaian tidak valid.');
        }
        $this->db->transBegin();
        try {
            $product = $this->lockProduct($productId);
            $before = (int) $product['stock_qty'];
            $after = $direction === 'IN' ? $before + $qty : $before - $qty;
            if ($after < 0) {
                throw new RuntimeException('Penyesuaian ditolak. Stok ' . $product['name'] . ' hanya ' . $before . ', tidak bisa dikurangi ' . $qty . '.');
            }
            $this->db->table('products')->where('id', $productId)->update(['stock_qty' => $after, 'updated_at' => date('Y-m-d H:i:s')]);
            $this->recordMovement($productId, 'ADJUSTMENT', 'ADJUSTMENT', 0, $qty, $before, $after, $userId, $notes);
            $this->db->transCommit();
            return ['qty_before' => $before, 'qty_after' => $after];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            throw new RuntimeException('Gagal mencatat penyesuaian stok: ' . $e->getMessage());
        }
    }
}
