@echo off
echo.
echo ========================================
echo  upMVC Enhanced Module Generator
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: PHP is not installed or not in PATH
    echo Please install PHP and try again.
    pause
    exit /b 1
)

REM Check if we're in the right directory
if not exist "generate-module.php" (
    echo Error: generate-module.php not found
    echo Please run this from the tools/modulegenerator directory
    pause
    exit /b 1
)

REM Run the module generator
php generate-module.php

echo.
echo Generator finished. Don't forget to run:
echo composer dump-autoload
echo.
pause