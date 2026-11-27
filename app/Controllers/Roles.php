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
        $_POST['name'] = $name;
        $_REQUEST['name'] = $name;
        $this->request->setGlobal('post', $_POST);
        $this->request->setGlobal('request', $_REQUEST);
        $messages = [
            'name' => ['required' => 'Nama peran wajib diisi.', 'min_length' => 'Nama peran minimal 2 karakter.', 'max_length' => 'Nama peran maksimal 20 karakter.', 'alpha_dash' => 'Nama peran hanya boleh berisi huruf, angka, garis bawah, dan strip, tanpa spasi.', 'is_unique' => 'Nama peran {value} sudah dipakai.'],
            'description' => ['max_length' => 'Deskripsi maksimal 255 karakter.'],
        ];
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[20]|alpha_dash|is_unique[roles.name]', 'description' => 'permit_empty|max_length[255]'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ((new RoleModel())->where('LOWER(name)', strtolower($name))->first()) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Nama peran ' . $name . ' sudah dipakai.']);
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
        $messages = [
            'name' => ['required' => 'Nama peran wajib diisi.', 'min_length' => 'Nama peran minimal 2 karakter.', 'max_length' => 'Nama peran maksimal 20 karakter.', 'alpha_dash' => 'Nama peran hanya boleh berisi huruf, angka, garis bawah, dan strip, tanpa spasi.', 'is_unique' => 'Nama peran {value} sudah dipakai.'],
            'description' => ['max_length' => 'Deskripsi maksimal 255 karakter.'],
        ];
            if (! $this->validate(['description' => 'permit_empty|max_length[255]'], $messages)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
            (new RoleModel())->update($id, ['description' => $this->request->getPost('description')]);
            return redirect()->to('/roles')->with('success', 'Peran berhasil diubah.');
        }
        $name = strtolower(trim((string) $this->request->getPost('name')));
        $_POST['name'] = $name;
        $_REQUEST['name'] = $name;
        $this->request->setGlobal('post', $_POST);
        $this->request->setGlobal('request', $_REQUEST);
        $messages = [
            'name' => ['required' => 'Nama peran wajib diisi.', 'min_length' => 'Nama peran minimal 2 karakter.', 'max_length' => 'Nama peran maksimal 20 karakter.', 'alpha_dash' => 'Nama peran hanya boleh berisi huruf, angka, garis bawah, dan strip, tanpa spasi.', 'is_unique' => 'Nama peran {value} sudah dipakai.'],
            'description' => ['max_length' => 'Deskripsi maksimal 255 karakter.'],
        ];
        if (! $this->validate(['name' => 'required|min_length[2]|max_length[20]|alpha_dash|is_unique[roles.name,id,' . $id . ']', 'description' => 'permit_empty|max_length[255]'], $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        if ((new RoleModel())->where('LOWER(name)', strtolower($name))->where('id !=', $id)->first()) {
            return redirect()->back()->withInput()->with('errors', ['name' => 'Nama peran ' . $name . ' sudah dipakai.']);
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
