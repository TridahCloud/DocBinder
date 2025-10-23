<?php
// setup.php - Database setup script
require_once 'config.php';

echo "<h1>DocBinder Database Setup</h1>";

try {
    // Test database connection
    echo "<p>✓ Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['users', 'binders', 'documents', 'user_sessions', 'shared_binders'];
    $missing_tables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        if (!$stmt->fetch()) {
            $missing_tables[] = $table;
        }
    }
    
    if (empty($missing_tables)) {
        echo "<p>✓ All tables exist</p>";
        echo "<p><strong>Database setup complete!</strong></p>";
    } else {
        echo "<p>❌ Missing tables: " . implode(', ', $missing_tables) . "</p>";
        echo "<p>Please run the database_schema.sql file to create the tables.</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>
