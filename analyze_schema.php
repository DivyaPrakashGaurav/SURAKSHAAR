<?php
$schema = json_decode(file_get_contents('schema.json'), true);
if (isset($schema['components']['schemas'])) {
    $schemas = $schema['components']['schemas'];
} else if (isset($schema['definitions'])) {
    $schemas = $schema['definitions'];
} else {
    echo "No schemas found in schema.json\n";
    exit;
}
echo 'Tables: ' . implode(', ', array_keys($schemas)) . "\n";
foreach ($schemas as $name => $def) {
    if (in_array($name, ['workers', 'assessments', 'certificates', 'training_attempts', 'retention_records', 'modules'])) {
        echo "\nTable: $name\n";
        print_r(array_keys($def['properties']));
    }
}
