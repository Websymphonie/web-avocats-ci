---
name: symfony-bounded-context
description: Implement, audit and modify Avocat CI Symfony functionality using the project's Bounded Context, CQRS, Domain/Application/Infrastructure/Presenter architecture. Use for Commands, Queries, Doctrine persistence, Frontoffice Twig, Member Area, Backoffice, forms, Twig components, Symfony UX, API work and business feature development.
---

# Symfony Bounded Context Workflow — Avocat CI

Use this skill whenever auditing, implementing or modifying Symfony functionality in the Avocat CI project.

Always read the repository `AGENTS.md` first.

Then read the documentation relevant to the requested feature.

---

# 1. Determine task mode

Before doing anything, determine whether the task is:

```text
AUDIT
ARCHITECTURE
IMPLEMENTATION
BUGFIX
REFACTORING
DOCUMENTATION
```

The rules differ by mode.

If the task is an audit, do not implement changes unless explicitly requested.

---

# 2. Understand the task

Before modifying code:

1. identify the requested business capability;
2. locate its requirement in `docs/PRODUCT_REQUIREMENTS.md`;
3. inspect `docs/BUSINESS_RULES.md`;
4. inspect `docs/PERMISSIONS.md` when authorization is involved;
5. inspect `docs/ROADMAP.md`;
6. inspect `docs/ARCHITECTURE.md`;
7. inspect `docs/UI_UX_GUIDELINES.md` for user-facing work;
8. identify the owning Bounded Context;
9. distinguish current-state behavior from target-state requirements.

Do not start coding before understanding the business responsibility.

---

# 3. Current state vs target state

The repository may already contain technical foundations, conventions and contexts.

Treat these as the current state.

The Avocat CI specifications represent the target state.

Never force the repository to match the target specification mechanically.

Before proposing a change:

1. inspect what already exists;
2. determine whether it is healthy and reusable;
3. identify the actual gap;
4. preserve compatible foundations;
5. propose the smallest justified evolution.

Do not replace stable implementation only for architectural aesthetics.

---

# 4. Inspect the repository

Before creating classes:

1. inspect the target Bounded Context;
2. inspect its directory structure;
3. find one or more similar existing features;
4. inspect existing Commands and Queries;
5. inspect existing factories/mappers;
6. inspect repository conventions;
7. inspect form and controller conventions;
8. inspect Presenter organization;
9. inspect relevant security conventions;
10. inspect relevant tests.

Existing code is the primary implementation reference.

Do not generate architecture blindly.

---

# 5. Existing contexts

The repository currently contains contexts including:

```text
AdminContext
AuthContext
IdentityContext
SharedContext
LogContext
NotificationContext
WebContext
```

Do not recreate, rename, move or merge them without an explicit architecture task.

Candidate business contexts such as:

```text
ContentContext
ContributionContext
LearningContext
PaymentContext
MediaContext
```

must only be introduced when justified by the actual repository audit and feature ownership.

---

# 6. Determine operation type

Determine whether the requested operation changes application state.

## Write operations

Examples:

- create;
- edit;
- publish;
- delete;
- archive;
- activate;
- disable;
- assign;
- enroll;
- confirm;
- cancel;
- schedule;
- complete;
- issue;
- pay.

Prefer:

```text
Presenter
    ↓
Command
    ↓
CommandHandler
    ↓
Domain
    ↓
Repository Interface
    ↓
Infrastructure Repository
```

Commands belong in:

```text
Application/UseCase/Command
```

Command handlers belong in:

```text
Application/UseCase/CommandHandler
```

---

## Read operations

Examples:

- display;
- find;
- list;
- catalog;
- search;
- autocomplete;
- dashboard;
- report;
- statistics.

Prefer:

```text
Presenter
    ↓
Query
    ↓
QueryHandler
    ↓
Repository / Read model
    ↓
Model / DTO
```

Queries belong in:

```text
Application/UseCase/Query
```

Query handlers belong in:

```text
Application/UseCase/QueryHandler
```

Queries must not mutate state.

---

# 7. Domain

Business concepts belong in Domain.

Use:

```text
Domain/Model
Domain/Enum
Domain/Exception
Domain/Event
Domain/Repository
```

when the feature actually requires them.

Domain must remain independent from:

- Symfony;
- Doctrine;
- HTTP;
- Twig;
- Presenter;
- Infrastructure.

---

# 8. Domain model rule

Do not create an anemic Domain Model automatically.

When a business invariant belongs naturally to a model, keep it close to that model.

Examples:

```text
Training::publish()
Training::archive()
Enrollment::complete()
Content::publish()
Contribution::markPaid()
```

may be appropriate if they enforce actual business behavior.

Do not add methods merely to make the model appear "rich".

---

# 9. Enums

Use enums for stable finite business states.

Examples may include:

- training type;
- training status;
- content status;
- enrollment status;
- payment status;
- contribution status.

Do not use enums for values that should be managed dynamically through administration.

Before adding an enum, inspect `docs/BUSINESS_RULES.md`.

Do not invent enum values.

---

# 10. CommandHandler rules

A CommandHandler orchestrates the use case.

A handler may:

- load required models;
- invoke Domain behavior;
- persist changes;
- emit events according to project conventions;
- return an application result when necessary.

A CommandHandler must not:

- render Twig;
- create HTTP responses;
- access Symfony Request;
- contain presentation logic;
- contain Doctrine QueryBuilder;
- decide UI messages.

---

# 11. QueryHandler rules

A QueryHandler retrieves and prepares data required by a read operation.

A QueryHandler may use:

- repository interfaces;
- dedicated read repositories;
- application services;
- read models.

A QueryHandler must not mutate state.

Avoid loading complete object graphs when a lightweight read representation is sufficient.

---

# 12. Persistence

Doctrine entities belong in:

```text
Infrastructure/Persistence/Doctrine/Entity
```

Doctrine repositories belong in:

```text
Infrastructure/Persistence/Doctrine/Repository
```

Repository contracts belong in:

```text
Domain/Repository
```

A Doctrine repository should implement the corresponding Domain repository interface when appropriate.

---

# 13. Mapping

Entity / Model transformations belong in:

```text
Infrastructure/Factory
```

Before creating a Factory:

1. inspect existing factories;
2. reproduce established conventions;
3. prefer explicit mapping.

Do not introduce a mapping framework unless explicitly requested.

---

# 14. Presenter

Typical Presenter locations include:

```text
Presenter/Controller
Presenter/Form
Presenter/Component
Presenter/Service
Presenter/Twig
```

When the context serves multiple UI surfaces and the repository architecture justifies it, Presenter may be subdivided into:

```text
Presenter/Frontoffice
Presenter/Member
Presenter/Backoffice
Presenter/Api
```

Do not introduce this subdivision mechanically if the existing project uses another healthy convention.

Twig templates must remain presentation-focused.

---

# 15. Platform surfaces

Always identify the target surface before implementing a user-facing feature.

Possible surfaces:

```text
FRONTOFFICE
MEMBER AREA
BACKOFFICE
API
```

## Frontoffice

Public Twig website.

Typical responsibilities:

- public content;
- news;
- events;
- videos;
- galleries;
- training catalog;
- training detail pages;
- Live detail pages;
- public search.

## Member Area

Authenticated Twig area.

Typical responsibilities:

- profile;
- contributions;
- payments;
- enrolled trainings;
- learning progress;
- certificates;
- notifications.

## Backoffice

Administrative Twig interface.

Do not place all business administration inside `AdminContext` automatically.

## API

API for mobile or external clients when explicitly required.

The backend remains authoritative.

---

# 16. Controller workflow

A controller should generally:

1. receive the HTTP request;
2. hydrate presentation input;
3. validate presentation constraints;
4. dispatch a Command or Query;
5. handle presentation-specific success or error behavior;
6. return a Response.

Controllers must remain thin.

---

# 17. Forms

Forms handle input.

Do not place business rules inside FormType classes.

Follow the existing project convention regarding:

- DTO binding;
- Model binding;
- validation groups;
- form handlers.

Do not introduce another form architecture if one is already established.

---

# 18. Symfony UX

When appropriate, reuse existing:

- TwigComponent;
- LiveComponent;
- Stimulus;
- shared UI components;
- Tailwind / Shadcn-inspired components.

Reusable presentation behavior should preferably use existing project conventions.

Do not introduce JavaScript complexity for behavior that can remain server-side.

Symfony UX React is reserved for genuinely rich analytical or highly interactive interfaces.

Do not use React for simple CRUD, forms, static detail pages or standard lists.

---

# 19. Public visibility vs protected access

Do not confuse discoverability with authorization.

A resource may be publicly visible while its protected content remains restricted.

Example:

```text
Training visibility = PUBLIC
Training access = PAID
```

The training page may be visible publicly while course content still requires authorization.

Backend access checks remain mandatory.

YouTube visibility must never be treated as application authorization.

---

# 20. Cross-context operations

Do not access another context's Doctrine entities directly.

If another context's information is required:

1. inspect existing project communication patterns;
2. prefer identifiers;
3. prefer public application contracts;
4. use Commands, Queries or events when appropriate.

Avoid circular dependencies.

---

# 21. Permissions

Before exposing a controller action or UI operation:

1. inspect `docs/PERMISSIONS.md`;
2. inspect existing Symfony security conventions;
3. inspect IdentityContext permission resolution;
4. reuse the existing permission engine when healthy;
5. reuse existing role naming when possible.

Backend authorization is mandatory.

Hiding a button in Twig is not sufficient security.

Do not create a second security system if the existing one can evolve safely.

---

# 22. Business rules

Before implementing state changes:

1. inspect `docs/BUSINESS_RULES.md`;
2. identify applicable invariants;
3. inspect the current implementation;
4. inspect the target Avocat CI specification;
5. implement rules in the appropriate layer;
6. add tests for meaningful behavior.

Never invent a transition or business rule.

---

# 23. Content-specific rules

Editorial content may include:

```text
NEWS
EVENT
VIDEO
PHOTO_GALLERY
ANNOUNCEMENT
DOCUMENT
```

Do not introduce all types automatically.

Implement only the types required by the current vertical slice.

Do not assume editorial videos and training videos are the same business concept.

---

# 24. E-learning-specific rules

The target learning model uses:

```text
Training
```

with important types such as:

```text
COURSE
LIVE
```

A Live is a training in its own right.

Do not model a Live as a child module of a classical course unless a validated requirement explicitly changes that rule.

YouTube may be used as a provider for:

- recorded training videos;
- Live broadcasts;
- replay videos.

The application remains responsible for:

- enrollment;
- payment state;
- access rights;
- authorization.

---

# 25. Contribution-specific rules

Contribution management requires validated business rules.

Do not invent:

- amount calculation;
- periods;
- due dates;
- penalties;
- exemptions;
- regularizations;
- accounting rules;
- receipt rules.

If the current system exists outside the repository, audit and document it before implementation.

---

# 26. Payment-specific rules

Payment infrastructure may be shared, but business ownership remains in the relevant context.

Do not confuse:

```text
Contribution
```

with:

```text
Payment
```

or:

```text
Training
```

with:

```text
Order / Payment
```

Financial history must remain explicit and auditable.

Do not silently delete or rewrite completed financial facts.

---

# 27. UI simplicity

When implementing UI:

- prioritize clarity;
- use simple French labels;
- minimize required fields;
- reuse established layout;
- show status clearly;
- provide useful empty states;
- avoid unnecessary dialogs;
- avoid excessive actions in tables;
- maintain responsive behavior;
- maintain accessibility;
- distinguish public, member and administrative UX needs.

Do not turn simple requirements into complex multi-step workflows without an explicit requirement.

---

# 28. Creation rule

Do not create every architectural class automatically.

Create only what the feature needs.

A simple read may require only:

```text
Query
QueryHandler
Controller
Twig
```

A write workflow may require:

```text
Command
CommandHandler
Domain behavior
Repository
Persistence
Presenter
Tests
```

Do not create abstractions for hypothetical future requirements.

---

# 29. Incremental implementation

Implement one coherent vertical slice at a time.

Example:

```text
Create Training
```

before implementing:

```text
Enrollment
Payment
Certificate
```

when later features depend on the Training foundation.

Do not implement an entire roadmap phase unless explicitly requested.

---

# 30. Refactoring

Do not perform unrelated refactoring.

If a problem is discovered:

- fix it only when required for the current feature;
- otherwise report it separately.

Do not redesign stable contexts while implementing a business feature.

---

# 31. Audit workflow

When the task is an audit, perform the following sequence.

## 31.1 Inspect

Inspect:

- `composer.json`;
- framework versions;
- dependencies;
- `src/`;
- `config/`;
- `templates/`;
- `assets/`;
- `tests/`;
- `migrations/`;
- `docs/`;
- existing security configuration;
- existing contexts;
- database mappings;
- Messenger configuration;
- frontend foundation;
- API capabilities;
- CI/CD files.

## 31.2 Map current architecture

Document:

- existing contexts;
- their responsibilities;
- dependency patterns;
- CQRS conventions;
- persistence conventions;
- Presenter conventions;
- security model;
- permission engine;
- Frontoffice;
- Member Area;
- Backoffice;
- API.

## 31.3 Compare with Avocat CI target

For each target capability classify it as:

```text
EXISTS AND COMPATIBLE
EXISTS BUT NEEDS ADAPTATION
PARTIAL
ABSENT
DO NOT CREATE YET
```

## 31.4 Produce gap analysis

For each gap report:

```text
Current state
Target state
Gap
Impact
Risk
Recommended action
```

## 31.5 Stop before implementation

During audit mode do not:

- add contexts;
- create entities;
- create migrations;
- rename namespaces;
- delete code;
- install dependencies;
- rewrite architecture;
- implement business features.

Stop after the audit report and integration plan unless explicitly instructed otherwise.

---

# 32. Tests

When behavior changes:

1. identify existing testing conventions;
2. add or update focused tests;
3. run those tests;
4. run broader relevant tests;
5. run PHPStan if configured;
6. run coding standards checks if configured.

Never remove tests merely to obtain a green build.

---

# 33. Completion checks

Before completing the task verify:

- requirement implemented;
- correct Bounded Context;
- smallest coherent scope respected;
- architecture rules respected;
- business rules respected;
- permissions respected;
- public/protected access behavior respected;
- controllers remain thin;
- Queries do not mutate;
- Doctrine stays in Infrastructure;
- Domain remains framework-independent;
- mappings follow project conventions;
- tests cover relevant behavior;
- relevant checks pass.

---

# 34. Completion report

At the end of an implementation task report:

## Implemented

What was implemented.

## Files

Main files added or modified.

## Tests

Tests and checks executed.

## Remaining

What remains for the current module.

## Notes

Any assumption, architectural inconsistency or business ambiguity discovered.

## Commit

Suggest a Conventional Commit message when quality gates are green.

---

# 35. Audit report format

At the end of an audit task report:

## Executive Summary

Overall repository state and readiness.

## Current Architecture

Contexts, layers and conventions actually found.

## Reusable Foundations

Existing technical components that should be preserved.

## Gaps

Differences between current repository and Avocat CI target.

## Risks

Technical, security, business and migration risks.

## Recommended Adaptations

Smallest justified changes.

## Integration Order

Recommended phased implementation sequence.

## Open Questions

Business or architecture decisions still requiring validation.

Do not begin implementation unless explicitly requested.
