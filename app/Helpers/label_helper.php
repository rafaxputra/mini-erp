<?php
// Label berbahasa Indonesia untuk kode-kode yang disimpan di database.
// Dipakai di view supaya yang tampil di layar mudah dipahami manusia,
// sementara isi databasenya tetap kode singkat berbahasa Inggris.
if (! function_exists('status_label')) {
    function status_label(string $status): string
    {
        return [
            'draft' => 'Draf',
            'approved' => 'Disetujui',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            'confirmed' => 'Dikonfirmasi',
            'delivered' => 'Terkirim',
        ][$status] ?? $status;
    }
}
if (! function_exists('movement_label')) {
    function movement_label(string $type): string
    {
        return [
            'IN' => 'Masuk',
            'OUT' => 'Keluar',
            'ADJUSTMENT' => 'Koreksi',
        ][$type] ?? $type;
    }
}
if (! function_exists('reference_label')) {
    function reference_label(string $type): string
    {
        return [
            'PURCHASE_RECEIPT' => 'Penerimaan Barang',
            'SALES_DELIVERY' => 'Pengiriman',
            'ADJUSTMENT' => 'Koreksi Stok',
            'STOCK_AWAL' => 'Stok Awal',
        ][$type] ?? $type;
    }
}
