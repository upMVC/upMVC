@echo off
echo.
echo ================================================
echo Enhanced Module Generator for upMVC v2.0
echo Auto-Discovery • Submodules • Environment-Aware
echo ================================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not installed or not in PATH
    echo Please install PHP and add it to your system PATH
    echo.
    pause
    exit /b 1
)

REM Check if we're in the right directory
if not exist "..\..\etc\InitModsImproved.php" (
    echo ERROR: InitModsImproved.php not found
    echo Please ensure you're running this from: tools/modulegenerator-enhanced/
    echo And that the enhanced upMVC system is installed
    echo.
    pause
    exit /b 1
)

REM Run the enhanced generator
echo Starting Enhanced Module Generator...
echo.
php generate-module.php

echo.
echo ================================================
echo Generation complete!
echo.
echo Remember to run: composer dump-autoload
echo.
pause