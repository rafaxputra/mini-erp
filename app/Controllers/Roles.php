<?php
namespace App\Controllers;
use App\Models\RoleModel;
class Roles extends BaseController
{
    private const CORE = ['admin', 'purchasing', 'gudang', 'sales', 'manajemen'];

    public function index()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        $db = \Config\Database::connect();
        $items = $db->table('roles r')->select('r.*, (SELECT COUNT(*) FROM users u WHERE u.role_id = r.id) AS user_count')->orderBy('r.id', 'ASC')->get()->getResultArray();
        return view('roles/index', ['title' => 'Peran', 'items' => $items, 'core' => self::CORE]);
    }
    public function create()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        return view('roles/form', ['title' => 'Tambah Peran', 'item' => null, 'isCore' => false]);
    }
    public function store()
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        $name = strtolower(trim((string) $this->request->getPost('name')));
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[20]|alpha_dash|is_unique[roles.name]', 'description' => 'permit_empty|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', 'Nama peran wajib diisi (huruf, angka, garis bawah, tanpa spasi, maks 20 karakter) dan belum dipakai.');
        }
        (new RoleModel())->insert(['name' => $name, 'description' => $this->request->getPost('description')]);
        return redirect()->to('/roles')->with('success', 'Peran berhasil ditambahkan.');
    }
    public function edit($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        $item = (new RoleModel())->find($id);
        if (! $item) return redirect()->to('/roles')->with('error', 'Peran tidak ditemukan.');
        return view('roles/form', ['title' => 'Ubah Peran', 'item' => $item, 'isCore' => in_array($item['name'], self::CORE, true)]);
    }
    public function update($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        $item = (new RoleModel())->find($id);
        if (! $item) return redirect()->to('/roles')->with('error', 'Peran tidak ditemukan.');
        $isCore = in_array($item['name'], self::CORE, true);
        if ($isCore) {
            if (! $this->validate(['description' => 'permit_empty|max_length[255]'])) {
                return redirect()->back()->withInput()->with('error', 'Deskripsi maksimal 255 karakter.');
            }
            (new RoleModel())->update($id, ['description' => $this->request->getPost('description')]);
            return redirect()->to('/roles')->with('success', 'Peran berhasil diubah.');
        }
        $name = strtolower(trim((string) $this->request->getPost('name')));
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[20]|alpha_dash|is_unique[roles.name,id,' . $id . ']', 'description' => 'permit_empty|max_length[255]'])) {
            return redirect()->back()->withInput()->with('error', 'Nama peran wajib diisi (huruf, angka, garis bawah, tanpa spasi, maks 20 karakter) dan belum dipakai.');
        }
        (new RoleModel())->update($id, ['name' => $name, 'description' => $this->request->getPost('description')]);
        return redirect()->to('/roles')->with('success', 'Peran berhasil diubah.');
    }
    public function delete($id)
    {
        if ($r = $this->requireRole(['admin'], '/dashboard', 'Hanya admin yang bisa mengelola peran.')) return $r;
        $item = (new RoleModel())->find($id);
        if (! $item) return redirect()->to('/roles')->with('error', 'Peran tidak ditemukan.');
        if (in_array($item['name'], self::CORE, true)) {
            return redirect()->to('/roles')->with('error', 'Peran sistem tidak bisa dihapus.');
        }
        try {
            (new RoleModel())->delete($id);
            return redirect()->to('/roles')->with('success', 'Peran berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('/roles')->with('error', 'Peran tidak bisa dihapus karena sudah dipakai pengguna.');
        }
    }
}
