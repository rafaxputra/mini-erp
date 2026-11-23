<?php
namespace App\Controllers;
use App\Models\ProductModel;
class Dashboard extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $stockValue = $db->query('SELECT COALESCE(SUM(stock_qty * purchase_price), 0) AS total FROM products')->getRowArray()['total'];
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');

        $salesRow = $db->query(
            "SELECT COALESCE(SUM(qty * unit_price), 0) AS total_sales, COUNT(DISTINCT so_number) AS total_orders 
             FROM sales_orders 
             WHERE status <> 'cancelled' AND order_date BETWEEN ? AND ?",
            [$startOfMonth, $endOfMonth]
        )->getRowArray();

        $topProducts = $db->query(
            "SELECT p.code, p.name, p.unit, SUM(so.qty) AS total_qty, SUM(so.qty * so.unit_price) AS total_sales 
             FROM sales_orders so 
             JOIN products p ON p.id = so.product_id 
             WHERE so.status <> 'cancelled' AND so.order_date BETWEEN ? AND ? 
             GROUP BY p.id, p.code, p.name, p.unit 
             ORDER BY total_qty DESC 
             LIMIT 5",
            [$startOfMonth, $endOfMonth]
        )->getResultArray();

        $data = [
            'title' => 'Dashboard',
            'productCount' => (new ProductModel())->countAllResults(),
            'stockValue' => $stockValue,
            'salesThisMonth' => (float) ($salesRow['total_sales'] ?? 0),
            'ordersThisMonth' => (int) ($salesRow['total_orders'] ?? 0),
            'lowStock' => $db->query('SELECT * FROM products WHERE stock_qty <= min_stock ORDER BY stock_qty ASC')->getResultArray(),
            'topProducts' => $topProducts,
        ];
        return view('dashboard/index', $data);
    }
}
