# Modern BaseView Guide (v2.0)

The modern UI system in upMVC is implemented in the class
[src/Common/Bmvc/BaseViewModern.php](src/Common/Bmvc/BaseViewModern.php).

This guide is intentionally short and points you to the
more detailed architectural documents that cover how the
modern BaseView works, how it differs from the legacy
BaseView, and how to integrate it into your own modules.

## Where to start

- Read [docs/ARCHITECTURAL_STRENGTHS.md](docs/ARCHITECTURAL_STRENGTHS.md)
  for an overview of the "modern UI system" and BaseView vs BaseViewModern.
- Read [docs/PHILOSOPHY_PURE_PHP.md](docs/PHILOSOPHY_PURE_PHP.md)
  to understand the noFramework rendering approach (BaseView family).

## Using the modern BaseView

- Extend `Common\Bmvc\BaseViewModern` in your module views when you
  want the new layout/dark-mode-ready structure.
- Keep using `Common\Bmvc\BaseView` if you prefer the classic minimal
  view layer; both are compatible.

For concrete end‑to‑end examples, follow the React/Vue integration
guides and the Islands/architecture docs referenced in README.md.
