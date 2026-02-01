<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

$logFile = __DIR__ . '/force_migration.log';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

logMsg("Connecting to database $database at $host:$port...");

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logMsg("Connected successfully.");

    // 1. Add downvotes to community_ideas
    logMsg("Checking 'downvotes' column in 'community_ideas'...");
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM community_ideas LIKE 'downvotes'");
        if ($stmt->rowCount() == 0) {
            logMsg("Adding 'downvotes' column...");
            $pdo->exec("ALTER TABLE community_ideas ADD COLUMN downvotes INT DEFAULT 0 AFTER votes");
            logMsg("Column 'downvotes' added.");
        } else {
            logMsg("Column 'downvotes' already exists.");
        }
    } catch (PDOException $e) {
        logMsg("Error checking/adding downvotes: " . $e->getMessage());
    }

    // 2. Add type to community_idea_votes
    logMsg("Checking 'type' column in 'community_idea_votes'...");
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM community_idea_votes LIKE 'type'");
        if ($stmt->rowCount() == 0) {
            logMsg("Adding 'type' column...");
            $pdo->exec("ALTER TABLE community_idea_votes ADD COLUMN type ENUM('up', 'down') DEFAULT 'up' AFTER user_id");
            logMsg("Column 'type' added.");
        } else {
            logMsg("Column 'type' already exists.");
        }
    } catch (PDOException $e) {
        logMsg("Error checking/adding type: " . $e->getMessage());
    }

    // 3. Add sub_sector_id to issue_reports
    logMsg("Checking 'sub_sector_id' column in 'issue_reports'...");
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM issue_reports LIKE 'sub_sector_id'");
        if ($stmt->rowCount() == 0) {
            logMsg("Adding 'sub_sector_id' column...");
            $pdo->exec("ALTER TABLE issue_reports ADD COLUMN sub_sector_id INT UNSIGNED NULL AFTER sector_id");
            $pdo->exec("ALTER TABLE issue_reports ADD CONSTRAINT fk_issue_sub_sector FOREIGN KEY (sub_sector_id) REFERENCES sub_sectors(id) ON DELETE SET NULL ON UPDATE CASCADE");
            logMsg("Column 'sub_sector_id' added.");
        } else {
            logMsg("Column 'sub_sector_id' already exists.");
        }
    } catch (PDOException $e) {
        logMsg("Error checking/adding sub_sector_id: " . $e->getMessage());
    }

    logMsg("Migration completed.");

} catch (PDOException $e) {
    logMsg("Connection failed: " . $e->getMessage());
}
