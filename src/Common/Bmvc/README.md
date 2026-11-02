# BMVC — shared app skeleton (not the kernel)

This folder is **not** `src/Etc/` (the kernel). It is the **design place** for base Model / View / Controller / API classes so modules can share a skeleton.

- **Optional to extend** — stock `Welcome` does not use these classes.
- **Not optional bloat** — unlike demo modules (Releases zip). Shipping without a Common zone pushes people to reinvent layout/PDO in every module.
- **Library mode:** do not edit files under `vendor/bitshost/upmvc/src/Common/Bmvc`. Extend in the host (`App\Common\AppModel`, `AppView`, …) or override via host `src/Common/Bmvc/` (same FQCN).
- **Standalone:** after `create-project`, this tree is yours; `composer update` does not rewrite it (vendor only). Upstream zip/git merge can overwrite — treat carefully.

Full guide (standalone vs library, examples, update scenarios):

→ **[docs/KERNEL_COMMON_BMVC.md](../../../docs/KERNEL_COMMON_BMVC.md)**

Prefer **`BaseView`** as the default for a new `AppView`. Use `BaseViewModern` only if you want that shell.
