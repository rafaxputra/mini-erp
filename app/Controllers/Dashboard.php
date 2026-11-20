<?php
namespace App\Controllers;
use App\Models\ProductModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];
        $data = [
            'title' => 'Dashboard',
            'productCount' => (new ProductModel())->countAllResults(),
            'stockValue' => $stockValue,
            'lowStock' => $db->query('SELECT * FROM products WHERE stock_qty <= min_stock ORDER BY stock_qty ASC')->getResultArray(),
        ];
        return view('dashboard/index', $data);
    }
}
