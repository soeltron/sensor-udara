<?php

$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    touch($dbPath);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create sensor_data table
    $pdo->exec("CREATE TABLE IF NOT EXISTS sensor_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        temperature REAL,
        humidity REAL,
        air_quality REAL,
        created_at DATETIME,
        updated_at DATETIME
    )");

    // Create device_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS device_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        max_temperature REAL DEFAULT 30.0,
        max_air_quality REAL DEFAULT 500.0,
        led_red_status INTEGER DEFAULT 0,
        led_green_status INTEGER DEFAULT 0,
        created_at DATETIME,
        updated_at DATETIME
    )");

    // Insert default setting if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM device_settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO device_settings (max_temperature, max_air_quality, led_red_status, led_green_status, created_at, updated_at) 
                    VALUES (30.0, 500.0, 0, 0, datetime('now'), datetime('now'))");
    }

    echo "Database setup completed successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
