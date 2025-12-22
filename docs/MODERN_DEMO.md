# Modern UI Demo Instructions

This demo compares the **original** BaseView layout with the
**modern** BaseViewModern layout.

## 1. Run the built‑in PHP server

From the project root:

```bash
php -S localhost:8080
```

## 2. Visit the demo URLs

- Classic layout: `http://localhost:8080/test`
- Modern layout:  `http://localhost:8080/test/modern`

If your document root is configured to `public/`, adjust the URL
accordingly (for example `http://localhost:8080/test` still works
because `public/index.php` is the front controller).

## 3. What to look for

- Updated typography and spacing
- Modern navigation and header/footer
- Dark‑mode‑friendly colors
- Responsive behavior on mobile vs desktop

The implementation behind the modern layout lives in
[src/Common/Bmvc/BaseViewModern.php](src/Common/Bmvc/BaseViewModern.php).

For deeper architectural background, see:

- [docs/ARCHITECTURAL_STRENGTHS.md](docs/ARCHITECTURAL_STRENGTHS.md)
- [docs/PHILOSOPHY_PURE_PHP.md](docs/PHILOSOPHY_PURE_PHP.md)
