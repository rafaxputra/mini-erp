<?php
namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
abstract class BaseController extends Controller
{
    protected $session;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        $this->helpers = ['form', 'url', 'label'];
        parent::initController($request, $response, $logger);
        $this->session = session();
    }
    protected function resolvePerPage(int $default = 8): int
    {
        $ctrl = strtolower(class_basename(static::class));
        $sessionKey = 'erp_per_page_' . $ctrl;
        $req = (int) ($this->request->getGet('per_page') ?? 0);
        if (in_array($req, [7, 8, 10, 15, 25, 50], true)) {
            session()->set($sessionKey, $req);
            return $req;
        }
        $saved = (int) (session()->get($sessionKey) ?? 0);
        if (in_array($saved, [7, 8, 10, 15, 25, 50], true)) {
            return $saved;
        }
        return $default;
    }

    protected function userId(): int
    {
        return (int) session()->get('user_id');
    }
    // Gudang yang dipilih form; kalau tidak dikirim, pakai gudang utama.
    protected function resolveWarehouseId(int $warehouseId): int
    {
        if ($warehouseId > 0) {
            $row = \Config\Database::connect()->table('warehouses')->where('id', $warehouseId)->get()->getRowArray();
            if ($row) return $warehouseId;
        }
        $row = \Config\Database::connect()->table('warehouses')->where('is_default', true)->get()->getRowArray();
        return $row ? (int) $row['id'] : 1;
    }
    protected function isAdmin(): bool
    {
        return session()->get('user_role') === 'admin';
    }
    protected function isPurchasing(): bool
    {
        return session()->get('user_role') === 'purchasing';
    }
    protected function isGudang(): bool
    {
        return session()->get('user_role') === 'gudang';
    }
    protected function isSales(): bool
    {
        return session()->get('user_role') === 'sales';
    }
    protected function isManajemen(): bool
    {
        return session()->get('user_role') === 'manajemen';
    }
    protected function hasRole(array $roles): bool
    {
        return in_array(session()->get('user_role'), $roles, true);
    }
    protected function requireRole(array $roles, string $redirect = '/dashboard', string $message = 'Peran Anda tidak diizinkan melakukan aksi ini.')
    {
        if ($this->hasRole($roles)) {
            return null;
        }
        return redirect()->to($redirect)->with('error', $message);
    }
    protected function nextNumber(string $table, string $column, string $prefix, int $lockKey = 0): string
    {
        $db = \Config\Database::connect();
        if ($lockKey > 0) {
            $db->query('SELECT pg_advisory_xact_lock(?)', [$lockKey]);
        }
        $todayPrefix = $prefix . '-' . date('Ymd') . '-';
        $seqName = 'doc_seq_' . strtolower(preg_replace('/[^a-z0-9]/i', '', $prefix) ?? $prefix) . '_' . date('Ymd');
        $db->query("CREATE SEQUENCE IF NOT EXISTS {$seqName} MINVALUE 1 START 1");

        $row = $db->query(
            "SELECT COALESCE(MAX(CAST(SUBSTRING(" . $column . " FROM '[0-9]+$') AS INTEGER)), 0) AS max_seq FROM " . $table . " WHERE " . $column . " LIKE ?",
            [$todayPrefix . '%']
        )->getRowArray();
        $maxExisting = (int) ($row['max_seq'] ?? 0);

        $seqMeta = $db->query("SELECT last_value, is_called FROM {$seqName}")->getRowArray();
        $seqLast = ($seqMeta && ! empty($seqMeta['is_called'])) ? (int) $seqMeta['last_value'] : 0;
        if ($maxExisting > $seqLast) {
            $db->query('SELECT setval(?, ?, true)', [$seqName, $maxExisting]);
        }

        $n = (int) ($db->query('SELECT nextval(?) AS v', [$seqName])->getRow('v') ?? 1);
        return $todayPrefix . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
    protected function fmtRupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}
