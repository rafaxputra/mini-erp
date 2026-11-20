<?php
namespace App\Controllers;
use App\Models\ProductModel;
use App\Models\PurchaseOrderModel;
use App\Models\SalesOrderModel;
use App\Models\StockMovementModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $start = date('Y-m-01');
        $end = date('Y-m-t');
        $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];
        $data = [
            'title' => 'Dashboard',
            'productCount' => (new ProductModel())->countAllResults(),
            'stockValue' => $stockValue,
            'poMonth' => (new PurchaseOrderModel())->where('order_date >=', $start)->where('order_date <=', $end)->countAllResults(),
            'soMonth' => (new SalesOrderModel())->where('order_date >=', $start)->where('order_date <=', $end)->countAllResults(),
            'lowStock' => $db->query('SELECT * FROM products WHERE stock_qty <= min_stock ORDER BY stock_qty ASC')->getResultArray(),
            'movements' => $db->table('stock_movements sm')->select('sm.*, p.code, p.name AS product_name, u.name AS user_name')->join('products p', 'p.id = sm.product_id')->join('users u', 'u.id = sm.created_by')->orderBy('sm.created_at', 'DESC')->orderBy('sm.id', 'DESC')->limit(5)->get()->getResultArray(),
        ];
        return view('dashboard/index', $data);
    }
}
