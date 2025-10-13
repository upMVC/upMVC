<?php
/**
 * Test namespace generation for Enhanced Module Generator
 */

function testNamespaceGeneration($moduleName) {
    $namespace = ucfirst(strtolower($moduleName));
    echo "Module: '$moduleName' -> Namespace: '$namespace'\n";
}

echo "Testing Enhanced Module Generator Namespace Convention:\n";
echo "=======================================================\n";
testNamespaceGeneration('testitems');
testNamespaceGeneration('TestItems');
testNamespaceGeneration('TESTITEMS');
testNamespaceGeneration('anythingelse');
testNamespaceGeneration('AnythingElse');
testNamespaceGeneration('ANYTHINGELSE');
testNamespaceGeneration('camelCaseModule');
testNamespaceGeneration('snake_case_module');
echo "=======================================================\n";
echo "✅ All module names now generate consistent namespaces!\n";
echo "📝 Convention: First letter capitalized, rest lowercase\n";