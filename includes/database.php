<?php
require_once __DIR__ . '/../vendor/autoload.php'; 
use Dotenv\Dotenv;
// Load .env file 
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

try {
    // Concatenate the variables correctly using the variables inside the string
    $pdo = new PDO("mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database " . $_ENV['DB_NAME'] . " :" . $e->getMessage());
}
