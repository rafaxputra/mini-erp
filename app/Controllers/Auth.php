<?php
namespace App\Controllers;
use App\Models\UserModel;
class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login', ['title' => 'Login']);
    }
    public function loginPost()
    {
        $rules = ['email' => 'required|valid_email', 'password' => 'required|min_length[6]'];
        $messages = [
            'email' => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.'],
            'password' => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 6 karakter.'],
        ];
        if (! $this->validate($rules, $messages)) {
            return redirect()->to('/auth/login')->withInput()->with('errors', $this->validator->getErrors());
        }
        $user = (new UserModel())->select('users.*, roles.name AS role_name')->join('roles', 'roles.id = users.role_id')->where('email', $this->request->getPost('email'))->first();
        if (! $user || ! password_verify($this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->to('/auth/login')->withInput()->with('error', 'Email atau password salah.');
        }
        if (! $user['is_active']) {
            return redirect()->to('/auth/login')->withInput()->with('error', 'Akun tidak aktif. Hubungi admin.');
        }
        session()->set(['user_id' => $user['id'], 'user_name' => $user['name'], 'user_email' => $user['email'], 'user_role' => $user['role_name']]);
        return redirect()->to('/dashboard')->with('success', 'Selamat datang, ' . $user['name'] . '.');
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'Anda sudah logout.');
    }
}
