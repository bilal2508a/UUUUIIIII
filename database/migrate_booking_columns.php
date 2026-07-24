<?php
require_once __DIR__ . '/../includes/config.php';

$columnsToAdd = [
    'commission_rate' => "ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT 10.00",
    'commission_amount' => "ADD COLUMN commission_amount DECIMAL(10,2) DEFAULT 0.00",
    'owner_payout' => "ADD COLUMN owner_payout DECIMAL(10,2) DEFAULT 0.00",
    'refund_amount' => "ADD COLUMN refund_amount DECIMAL(10,2) DEFAULT 0.00",
    'cancelled_at' => "ADD COLUMN cancelled_at TIMESTAMP NULL DEFAULT NULL",
];

$existing = db()->query("SHOW COLUMNS FROM bookings")->fetchAll(PDO::FETCH_COLUMN);
foreach ($columnsToAdd as $col => $ddl) {
    if (!in_array($col, $existing)) {
        db()->exec("ALTER TABLE bookings $ddl");
        echo "Added column: $col\n";
    } else {
        echo "Column already exists: $col\n";
    }
}

$enumCheck = db()->query("SHOW COLUMNS FROM bookings LIKE 'payment_status'")->fetch();
if ($enumCheck && strpos($enumCheck['Type'], 'partial_refund') === false) {
    db()->exec("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('unpaid','paid','refunded','partial_refund') DEFAULT 'unpaid'");
    echo "Updated payment_status enum\n";
}

echo "Migration complete.\n";
