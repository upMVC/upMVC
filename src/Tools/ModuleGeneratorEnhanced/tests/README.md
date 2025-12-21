# ModuleGeneratorEnhanced - Test Files

This folder contains test files for validating the Enhanced Module Generator functionality.

## 🧪 Test Files

### Main Tests
- **test-generator.php** - Full generator functionality test
- **final-test.php** - Complete end-to-end test
- **quick-test.php** - Quick validation test

### Namespace Tests
- **test-namespace.php** - Namespace generation test
- **test-namespace-demo.php** - Namespace demo and examples
- **test-namespace-fix.php** - PSR-4 namespace compliance test

### Generation Tests
- **generate-test.php** - Module generation test suite

### Batch Scripts
- **test-namespace-fix.bat** - Windows batch script for namespace testing

## ▶️ Running Tests

### Quick Test
```bash
cd src/Tools/modulegenerator-enhanced/tests
php quick-test.php
```

### Full Test Suite
```bash
cd src/Tools/modulegenerator-enhanced/tests
php test-generator.php
```

### Namespace Validation
```bash
cd src/Tools/modulegenerator-enhanced/tests
php test-namespace-fix.php
```

## 📝 Test Results

After running tests, check:
1. Module creation in `src/Modules/`
2. Namespace correctness: `App\Modules\{ModuleName}`
3. PSR-4 folder structure compliance
4. Auto-discovery compatibility with InitModsImproved.php

## 🔧 Test Configuration

All tests use the updated PSR-4 structure:
- **Base Path:** `src/Modules/`
- **Namespace:** `App\Modules\`
- **Routes Folder:** `Routes/` (capital R)
- **Submodules Folder:** `Modules/` (capital M)

## 🐛 Troubleshooting

If tests fail:
1. Check that `composer dump-autoload` was run
2. Verify database configuration in `src/Etc/ConfigDatabase.php`
3. Ensure write permissions on `src/Modules/` directory
4. Check that InitModsImproved.php exists

## 📖 Documentation
See [../docs/](../docs/) for detailed documentation.
