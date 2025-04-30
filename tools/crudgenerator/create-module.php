<?php
/*
 * Example script to demonstrate usage of CrudModuleGenerator
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Tools\CrudGenerator\CrudModuleGenerator;

// Define module name
$moduleName = 'Productbest';

// Define fields with SQL and HTML types including select and radio with options
$fields = [
    ['name' => 'name', 'sql_type' => 'VARCHAR(100)', 'html_type' => 'text'],
    ['name' => 'price', 'sql_type' => 'DECIMAL(10,2)', 'html_type' => 'number', 'step' => '0.01', 'min' => '0'],
    ['name' => 'description', 'sql_type' => 'TEXT', 'html_type' => 'textarea'],
    [
        'name' => 'category',
        'sql_type' => 'VARCHAR(50)',
        'html_type' => 'select',
        'options' => [
            ['value' => 'electronics', 'label' => 'Electronics'],
            ['value' => 'clothing', 'label' => 'Clothing'],
            ['value' => 'books', 'label' => 'Books'],
            ['value' => 'food', 'label' => 'Food & Beverages']
        ]
    ],
    [
        'name' => 'availability',
        'sql_type' => 'VARCHAR(20)',
        'html_type' => 'radio',
        'options' => [
            ['value' => 'in_stock', 'label' => 'In Stock'],
            ['value' => 'out_of_stock', 'label' => 'Out of Stock'],
            ['value' => 'pre_order', 'label' => 'Pre-order']
        ]
    ],
    ['name' => 'email', 'sql_type' => 'VARCHAR(255)', 'html_type' => 'email'],
];

// Create instance of CrudModuleGenerator
$generator = new CrudModuleGenerator($moduleName, $fields);

// Generate the CRUD module files
$generator->generate();

// Create the database table
$generator->createTable();

echo "Module '{$moduleName}' created successfully with specified field types.\n";
?>
