<?php
// Convert all PHP files from MySQL to PostgreSQL
$directory = '.'; // directory ya sasa
$files = glob($directory . "/*.php");

foreach($files as $file) {
    $content = file_get_contents($file);
    
    // Badilisha mysqli_ kuwa pg_
    $content = str_replace('mysqli_query', 'pg_query', $content);
    $content = str_replace('mysqli_fetch_assoc', 'pg_fetch_assoc', $content);
    $content = str_replace('mysqli_num_rows', 'pg_num_rows', $content);
    $content = str_replace('mysqli_insert_id', 'pg_last_oid', $content);
    $content = str_replace('$conn->query', 'pg_query', $content);
    $content = str_replace('$conn->error', 'pg_last_error($conn)', $content);
    
    // Badilisha syntax ya prepared statements (ikiwa ipo)
    $content = str_replace('$stmt->execute()', 'pg_execute', $content);
    
    file_put_contents($file, $content);
    echo "Converted: $file<br>";
}

echo "✅ Conversion complete!";
?>