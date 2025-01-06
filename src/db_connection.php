
<?php
$db_name = "u328979044_teste";
$db_host = "srv803.hstgr.io";
$db_user = "u328979044_teste";
$db_pass = "Lun@081828";

try {
    $conn = new PDO("mysql:dbname=" . $db_name . ";host=" . $db_host, $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
