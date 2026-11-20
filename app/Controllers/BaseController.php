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
        $this->helpers = ['form', 'url'];
        parent::initController($request, $response, $logger);
        $this->session = session();
    }
    protected function userId(): int
    {
        return (int) session()->get('user_id');
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
    protected function nextNumber(string $table, string $column, string $prefix): string
    {
        $db = \Config\Database::connect();
        $row = $db->query("SELECT COUNT(*) AS total FROM " . $table . " WHERE DATE(created_at) = ?", [date('Y-m-d')])->getRowArray();
        $n = ((int) ($row['total'] ?? 0)) + 1;
        return $prefix . '-' . date('Ymd') . '-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
    protected function fmtRupiah($value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }
}
