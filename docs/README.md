# upMVC Documentation Index

Welcome to upMVC documentation!

## 📚 Core Documentation

### Routing
📁 **[routing/](routing/)** - Complete routing strategies guide
- **[routing/README.md](routing/README.md)** - Quick start and overview
- **[routing/ROUTING_STRATEGIES.md](routing/ROUTING_STRATEGIES.md)** - Complete guide with 3 approaches
- **[routing/examples/](routing/examples/)** - Working code examples

### Module Development
- **[Admin Module Example](../modules/admin/README.md)** - Complete admin dashboard implementation

### Architecture & Bug Fixes
- **[ARCHITECTURAL_STRENGTHS.md](ARCHITECTURAL_STRENGTHS.md)** - upMVC design philosophy
- **[BUG_FIX_AUTH_ASSIGNMENT.md](BUG_FIX_AUTH_ASSIGNMENT.md)** - Authentication fixes
- **[BUG_FIX_AUTH_REDIRECT.md](BUG_FIX_AUTH_REDIRECT.md)** - Redirect handling
- **[BUG_FIX_MISSING_EXIT.md](BUG_FIX_MISSING_EXIT.md)** - Exit statement fixes
- **[BUG_FIX_OUTPUT_BEFORE_HEADER.md](BUG_FIX_OUTPUT_BEFORE_HEADER.md)** - Headers already sent fixes

## 🚀 Quick Links

### For New Developers
1. Read [upMVC Philosophy](#) (if exists)
2. Study [Admin Module Example](../modules/admin/README.md)
3. Learn [Routing Strategies](routing/ROUTING_STRATEGIES.md)

### For Routing Questions
- "Which routing approach should I use?" → [routing/README.md](routing/README.md)
- "How to implement caching?" → [routing/examples/Routes_WithCache.php](routing/examples/Routes_WithCache.php)
- "How to use pattern matching?" → [routing/examples/Router_PatternMatching_README.md](routing/examples/Router_PatternMatching_README.md)

### For Performance Optimization
- Database routing taking too long? → [Cached DB approach](routing/ROUTING_STRATEGIES.md#approach-2-cached-database-routes)
- Need to scale to millions of records? → [Pattern Matching approach](routing/ROUTING_STRATEGIES.md#approach-3-pattern-matching-routes)

## 📊 Routing Decision Tree

```
How many records do you have?

< 100 records
  └─ Use Dynamic DB (simple, overhead doesn't matter)

100 - 100,000 records
  └─ Use Cached DB (best balance)
     See: docs/routing/examples/Routes_WithCache.php

> 100,000 records
  └─ Use Pattern Matching (scales infinitely)
     See: docs/routing/examples/Router_PatternMatching.php
```

## 🎯 Common Tasks

### Add a New Module
1. Create directory: `modules/yourmodule/`
2. Create MVC files: `Model.php`, `Controller.php`, `View.php`
3. Create routes: `routes/Routes.php`
4. Register in `/etc/InitMods.php`
5. Add namespace to `composer.json`
6. Run `composer dump-autoload`

Example: See [modules/admin/](../modules/admin/)

### Implement Cached Routing
```powershell
# 1. Create cache directory
New-Item -Path "modules/cache" -ItemType Directory -Force

# 2. Copy example
Copy-Item "docs/routing/examples/Routes_WithCache.php" "modules/yourmodule/routes/Routes.php"

# 3. Update namespace
# Edit Routes.php and change namespace to match your module

# 4. Add cache invalidation to controller
# See: docs/routing/examples/Controller_WithCache.php
```

### Implement Pattern Matching
```powershell
# 1. Backup Router
Copy-Item "etc/Router.php" "etc/Router_BACKUP.php"

# 2. Install pattern matching Router
Copy-Item "docs/routing/examples/Router_PatternMatching.php" "etc/Router.php" -Force

# 3. Update routes to use patterns
# Change: /users/edit/1, /users/edit/2, ...
# To: /users/edit/{id}

# 4. Add ID validation in controller
```

See: [routing/examples/Router_PatternMatching_README.md](routing/examples/Router_PatternMatching_README.md)

## 📖 Documentation Organization

```
docs/
├── README.md (this file)
├── routing/
│   ├── README.md                        # Routing quick start
│   ├── ROUTING_STRATEGIES.md            # Complete guide
│   └── examples/
│       ├── Router_PatternMatching.php          # Pattern Router
│       ├── Router_PatternMatching_README.md    # Installation guide
│       ├── Routes_WithCache.php                # Cached routes example
│       ├── Controller_WithCache.php            # Controller with cache
│       └── Pattern_Tester.php                  # Testing tool
├── ARCHITECTURAL_STRENGTHS.md
├── BUG_FIX_*.md
└── ...other docs...

modules/
└── admin/
    ├── README.md                        # Admin module guide
    ├── Controller.php                   # Working implementation
    ├── Model.php
    ├── View.php
    ├── schema.sql
    └── routes/
        └── Routes.php                   # Dynamic DB routing
```

## 💡 Design Philosophy

upMVC follows a **NoFramework** philosophy:

- **No magic** - Clear, explicit code
- **No bloat** - Only what you need
- **Pure PHP** - Direct access to `$_POST`, `$_SESSION`, `$_GET`
- **Full control** - Modify anything, anytime
- **Easy to understand** - No complex abstractions

## 🛠️ Development Tools

### Testing Pattern Matching
```powershell
php docs/routing/examples/Pattern_Tester.php
```

### Measuring Route Performance
```php
$start = microtime(true);
// ... route loading code ...
$time = (microtime(true) - $start) * 1000;
echo "Routes loaded in: {$time}ms";
```

## 📞 Need Help?

1. Check relevant documentation above
2. Study the admin module example
3. Review routing strategies guide
4. Test with the pattern tester tool

## 🔄 Migration Guides

Migrating between routing approaches? See:
- [Dynamic DB → Cached DB](routing/ROUTING_STRATEGIES.md#migrating-from-dynamic-db-to-cached-db)
- [Dynamic DB → Pattern Matching](routing/ROUTING_STRATEGIES.md#migrating-from-dynamic-db-to-pattern-matching)

## ✨ What's Next?

- [ ] Add more module examples
- [ ] Create API module with pattern matching
- [ ] Add performance benchmarking tools
- [ ] Create module generator script

---

**Happy coding with upMVC!** 🚀
