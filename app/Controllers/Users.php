<?php
namespace App\Controllers;
use App\Models\UserModel;
class Users extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa melihat daftar pengguna.')) return $r;
        return view('users/index', ['title' => 'Daftar Pengguna', 'users' => (new UserModel())->select('users.*, roles.name AS role_name')->join('roles', 'roles.id = users.role_id')->orderBy('users.id', 'ASC')->findAll()]);
    }
}
