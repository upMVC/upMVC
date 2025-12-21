# 🔒 Vault - Internal Planning & Features

**Status:** Private - Not committed to repository  
**Purpose:** Strategic planning, feature roadmaps, and internal documentation

---

## 📂 Directory Structure

```
vault/
├── Features/          # Planned features with detailed specs
├── Research/          # Technical research and experiments (future)
├── Decisions/         # Architecture decision records (future)
└── README.md          # This file
```

---

## 🎯 Features Queue

Priority-ordered features for implementation:

| # | Feature | Status | Priority | Version | Effort |
|---|---------|--------|----------|---------|--------|
| 1 | [HTTP Method-Aware Routing](Features/1-HTTP_METHOD_ROUTING.md) | 📋 Planned | 🔴 Critical | v2.1.0 | 3-4d |
| 2 | [Architecture Risks & Improvements](Features/2-ARCHITECTURE_RISKS_AND_IMPROVEMENTS.md) | 📋 Analysis | 🔴 Critical | v2.0.1 | 1-4h |

---

## 🔐 Security Notice

This folder is excluded from version control via `.gitignore`:

```gitignore
# Internal planning and features (top secret)
vault/
/vault/**
```

**Why vault is private:**
- Contains unreleased feature plans
- May include competitive analysis
- Contains architectural decisions before public announcement
- Allows free brainstorming without commitment

**When features go public:**
Once implemented and released, feature documentation moves to `/docs/` for public consumption.

---

## 📝 Document Templates

### Feature Document Structure
```markdown
# Feature #X: Title

**Priority:** 🔴 Critical / 🟡 High / 🟢 Normal / ⚪ Low
**Status:** Planned / In Progress / Testing / Complete
**Target Version:** vX.X.X
**Complexity:** Simple / Medium / Complex
**Effort:** X days

## Executive Summary
## Business Value
## Technical Specification
## Implementation Checklist
## Risk Assessment
## Timeline Estimate
```

---

## 🚀 Workflow

1. **Idea** → Create feature doc in `vault/Features/`
2. **Planning** → Flesh out technical specs and risks
3. **Approval** → Review and prioritize
4. **Implementation** → Create feature branch, develop
5. **Release** → Move docs to `/docs/`, update CHANGELOG
6. **Archive** → Keep vault doc for historical reference

---

**Last Updated:** 2025-11-16  
**Maintainer:** Core Team
