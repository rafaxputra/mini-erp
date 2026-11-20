<?php
namespace App\Controllers;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;
class Users extends BaseController
{
    public function index()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa melihat daftar pengguna.')) return $r;
        return view('users/index', ['title' => 'Daftar Pengguna', 'users' => (new UserModel())->select('users.*, roles.name AS role_name')->join('roles', 'roles.id = users.role_id')->orderBy('users.id', 'ASC')->findAll()]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola pengguna.')) return $r;
        return view('users/form', ['title' => 'Tambah Pengguna', 'item' => null, 'roles' => $this->roles()]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola pengguna.')) return $r;
        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[users.email]',
            'password' => 'required|min_length[6]|max_length[100]',
            'role_id' => 'required|integer|is_not_unique[roles.id]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Nama, email, password minimal 6 karakter, dan peran wajib diisi. Email belum boleh dipakai pengguna lain.');
        }
        (new UserModel())->insert([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role_id' => (int) $this->request->getPost('role_id'),
            'is_active' => true,
        ]);
        return redirect()->to('/users')->with('success', 'Pengguna berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola pengguna.')) return $r;
        $item = (new UserModel())->find($id);
        if (! $item) return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        return view('users/form', ['title' => 'Ubah Pengguna', 'item' => $item, 'roles' => $this->roles()]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola pengguna.')) return $r;
        $item = (new UserModel())->find($id);
        if (! $item) return redirect()->to('/users')->with('error', 'Pengguna tidak ditemukan.');
        if (! $this->validate([
            'name' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|max_length[150]|is_unique[users.email,id,' . $id . ']',
            'password' => 'permit_empty|min_length[6]|max_length[100]',
            'role_id' => 'required|integer|is_not_unique[roles.id]',
        ])) {
            return redirect()->back()->withInput()->with('error', 'Nama, email, dan peran wajib diisi. Email belum boleh dipakai pengguna lain. Password baru minimal 6 karakter kalau diisi.');
        }
        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role_id' => (int) $this->request->getPost('role_id'),
            'is_active' => (bool) $this->request->getPost('is_active'),
        ];
        $password = $this->request->getPost('password');
        if ($password !== '' && $password !== null) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ((int) $id === (int) session()->get('user_id') && ! $data['is_active']) {
            return redirect()->back()->withInput()->with('error', 'Akun sendiri tidak bisa dinonaktifkan.');
        }
        (new UserModel())->update($id, $data);
        return redirect()->to('/users')->with('success', 'Pengguna berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola pengguna.')) return $r;
        if ((int) $id === (int) session()->get('user_id')) {
            return redirect()->to('/users')->with('error', 'Akun sendiri tidak bisa dihapus.');
        }
        try {
            (new UserModel())->delete($id);
            return redirect()->to('/users')->with('success', 'Pengguna berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/users')->with('error', 'Pengguna tidak bisa dihapus karena sudah dipakai di transaksi.');
        }
    }
    private function roles(): array
    {
        return \Config\Database::connect()->table('roles')->select('id, name')->orderBy('id', 'ASC')->get()->getResultArray();
    }
}
