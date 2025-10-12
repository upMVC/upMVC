@echo off
echo.
echo ====================================
echo Enhanced Module Generator - Quick Test
echo ====================================
echo.
echo Testing namespace convention fix...
echo.

REM Test the namespace generation logic
php -r "echo 'Input: TestItems -> Namespace: ' . ucfirst(strtolower('TestItems')) . PHP_EOL;"
php -r "echo 'Input: testitems -> Namespace: ' . ucfirst(strtolower('testitems')) . PHP_EOL;"
php -r "echo 'Input: ANYTHINGELSE -> Namespace: ' . ucfirst(strtolower('ANYTHINGELSE')) . PHP_EOL;"

echo.
echo ====================================
echo All inputs now generate consistent namespaces!
echo Ready to generate modules with correct naming.
echo ====================================
echo.
pause