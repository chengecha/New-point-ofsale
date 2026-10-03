<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test...<br>";

// Test database connection
$conn = pg_connect("host=localhost port=5432 dbname=fogypos user=felix password=Chengecha@26!");
if (!$conn) {
    echo "Database connection failed!<n>";
    exit;
}
echo "Database connected successfully<br>";

// Test query
$result = pg_query($conn, "SELECT * FROM ospos_people LIMIT 1");
if (!$result) {
    echo "Query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "Query successful<br>";
    $row = pg_fetch_assoc($result);
    echo "<pre>";
    print_r($row);
    echo "</pre>";
}

// Get customers table fields
$result = pg_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_name = 'ospos_customers'");
if (!$result) {
    echo "Customers query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "<br>Customers table fields:<br>";
    while ($row = pg_fetch_assoc($result)) {
        echo $row['column_name'] . "<br>";
    }
}

// Check if sales_payments table exists
$result = pg_query($conn, "SELECT table_name FROM information_schema.tables WHERE table_name = 'ospos_sales_payments'");
if (!$result) {
    echo "<br>Sales payments query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "<br>Sales payments table exists: " . pg_num_rows($result) . " rows<br>";
}

// Check if sales_items table exists
$result = pg_query($conn, "SELECT table_name FROM information_schema.tables WHERE table_name = 'ospos_sales_items'");
if (!$result) {
    echo "Sales items query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "Sales items table exists: " . pg_num_rows($result) . " rows<br>";
}

// Get sales_payments table fields
$result = pg_query($conn, "SELECT column_name FROM information_schema.columns WHERE table_name = 'ospos_sales_payments'");
if (!$result) {
    echo "<br>Sales payments query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "<br>Sales payments table fields:<br>";
    while ($row = pg_fetch_assoc($result)) {
        echo $row['column_name'] . "<br>";
    }
}

// Get mailchimp configurations
$result = pg_query($conn, "SELECT * FROM ospos_app_config WHERE key LIKE 'mailchimp%'");
if (!$result) {
    echo "<br>Mailchimp query failed: " . pg_last_error($conn) . "<br>";
} else {
    echo "<br>Mailchimp config:<br>";
    while ($row = pg_fetch_assoc($result)) {
        echo "key: " . $row['key'] . ", value: " . $row['value'] . "<br>";
    }
}

pg_close($conn);
?>