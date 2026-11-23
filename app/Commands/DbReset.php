<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DbReset extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:reset';
    protected $description = 'Reset bersih schema public PostgreSQL, jalankan migrasi, dan seeder.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('1. Membersihkan schema public database...', 'yellow');
        $db->query('DROP SCHEMA IF EXISTS public CASCADE');
        $db->query('CREATE SCHEMA public');
        CLI::write('   Schema public berhasil dibersihkan.', 'green');

        CLI::write('2. Menjalankan seluruh migrasi...', 'yellow');
        $this->call('migrate');

        CLI::write('3. Mengisi data awal (seeder)...', 'yellow');
        $this->call('db:seed', ['DatabaseSeeder']);

        CLI::newLine();
        CLI::write('SUKSES: Database berhasil di-reset, dimigrasi, dan diisi data awal secara bersih!', 'green');
    }
}
