# Avocat CI — Agent Instructions

## Project

Avocat CI is a professional digital platform built with Symfony for lawyers and legal professionals in Côte d’Ivoire.

The platform is designed as a modular application combining several business capabilities, including:

- public content management;
- events and media publication;
- lawyer contribution management;
- e-learning;
- classical online training;
- YouTube-based training videos;
- YouTube Live training sessions;
- payments;
- notifications;
- member services;
- public Frontoffice Twig;
- authenticated member area;
- administrative Backoffice;
- API capabilities for future mobile clients.

The primary product objectives are:

- clarity;
- maintainability;
- security;
- modularity;
- consistency;
- usability;
- progressive delivery;
- respect for the existing repository architecture.

Do not introduce unnecessary complexity.

---

# Documentation

Before implementing or modifying a feature, read the relevant documentation.

The project documentation is located in:

- `docs/PROJECT_CONTEXT.md`
- `docs/ARCHITECTURE.md`
- `docs/DOMAIN.md`
- `docs/PRODUCT_REQUIREMENTS.md`
- `docs/BUSINESS_RULES.md`
- `docs/PERMISSIONS.md`
- `docs/ROADMAP.md`
- `docs/UI_UX_GUIDELINES.md`

When available, also read the current target specifications for Avocat CI, especially the latest technical specification document.

These files are the product and architecture reference.

Existing stable implementation remains an important reference.

When documentation and existing implementation differ:

1. identify the inconsistency;
2. do not silently invent a new rule;
3. preserve stable existing behavior unless the requested task explicitly changes it;
4. report the inconsistency;
5. distinguish clearly between current state and target state.

---

# Current-state and target-state rule

The repository may contain existing technical foundations, legacy structures, historical permissions or previous implementation choices.

These elements must be treated as the **current state** of the repository.

The current Avocat CI specifications represent the **target state**.

Never assume that the target architecture must replace the current implementation automatically.

Before changing architecture:

1. inspect the current implementation;
2. identify reusable foundations;
3. identify actual gaps;
4. preserve healthy existing patterns;
5. propose changes only where justified.

Do not rewrite stable architecture merely to make the repository look identical to a specification document.

---

# Existing Bounded Contexts

The project already contains the following contexts:

- `AdminContext`
- `AuthContext`
- `IdentityContext`
- `SharedContext`
- `LogContext`
- `NotificationContext`
- `WebContext`

These contexts already exist and must not be recreated, renamed or reorganized without an explicit architectural task.

Their existing implementation must be inspected before introducing new abstractions.

---

# Existing context responsibilities

## AdminContext

Responsible for technical and system administration according to the existing implementation.

Examples may include:

- system settings;
- technical configuration;
- application administration;
- administration infrastructure.

`AdminContext` must not automatically become the location for every business administration feature.

Business administration screens should remain owned by their business context whenever that is consistent with the project architecture.

---

## AuthContext

Responsible for authentication workflows.

Examples:

- login;
- logout;
- password workflows;
- authentication mechanisms;
- session-related authentication concerns.

---

## IdentityContext

Responsible for users, identity and authorization-related concepts.

Examples:

- users;
- roles;
- permissions;
- user profile;
- professional identity when already owned by this context.

Reuse the existing implementation before creating new user-related concepts elsewhere.

---

## SharedContext

Only genuinely shared technical or cross-cutting concepts belong here.

Do not place business features in `SharedContext` simply because several contexts use them.

---

## LogContext

Responsible for application logging, business activity logging or audit-related capabilities according to the existing implementation.

Inspect the context before introducing a separate audit mechanism.

---

## NotificationContext

Responsible for application notification mechanisms according to the existing implementation.

Possible capabilities include:

- in-app notifications;
- email notifications;
- notification dispatching;
- notification history.

Do not add SMS, WhatsApp or other external channels unless explicitly required.

---

## WebContext

Responsible for the existing public website capabilities according to the current repository implementation.

Before introducing `ContentContext`, public Presenter layers or new public routing structures, inspect `WebContext` carefully.

Do not assume automatically that:

```text
WebContext = ContentContext
```

or that:

```text
WebContext must be removed
```

Its long-term role must be decided from the audit and actual implementation.

---

# Candidate business contexts

The target platform may require business contexts such as:

- `ContentContext`
- `ContributionContext`
- `LearningContext`
- `PaymentContext`
- `MediaContext`

These are candidate business boundaries from the Avocat CI target architecture.

They must not all be created automatically.

Before introducing a new context:

1. inspect existing contexts;
2. inspect `docs/ARCHITECTURE.md`;
3. identify the business responsibility;
4. determine whether an existing context already owns that responsibility;
5. confirm that the boundary is justified;
6. introduce the context only when its first real feature requires it.

Do not create one Bounded Context per entity, page or menu entry.

---

# Platform surfaces

The platform must distinguish the following presentation surfaces when relevant.

## Frontoffice

Public Twig website.

Typical public capabilities may include:

- homepage;
- news;
- events;
- videos;
- photo galleries;
- public documents;
- training catalog;
- course detail pages;
- Live training detail pages;
- public search;
- institutional pages.

Public visibility does not imply unrestricted access to protected resources.

---

## Member Area

Authenticated Twig area for members and lawyers.

Typical capabilities may include:

- profile;
- contributions;
- payments;
- enrolled trainings;
- upcoming Lives;
- learning progress;
- certificates;
- notifications.

---

## Backoffice

Administrative Twig interface.

Business administration must respect ownership by context.

Examples:

- content administration belongs to the content business context;
- training administration belongs to the learning business context;
- contribution administration belongs to the contribution business context.

Do not move all business administration into `AdminContext` only because the routes are under `/admin`.

---

## API

API endpoints may support future mobile or external clients.

The backend remains authoritative for:

- authorization;
- validation;
- business rules;
- financial calculations;
- training access;
- contribution state;
- payment state.

Do not create an API unless the requested feature requires it or the architecture task explicitly asks for it.

---

# Mandatory architecture

The project uses Symfony with a Bounded Context + CQRS architecture.

Contexts follow this structure when the corresponding folders are actually needed:

```text
src/
└── <Context>/
    ├── Application/
    │   ├── Service/
    │   ├── Event/
    │   └── UseCase/
    │       ├── Command/
    │       ├── CommandHandler/
    │       ├── Query/
    │       └── QueryHandler/
    │
    ├── Domain/
    │   ├── Enum/
    │   ├── Exception/
    │   ├── Event/
    │   ├── Model/
    │   └── Repository/
    │
    ├── Infrastructure/
    │   ├── Listener/
    │   ├── Persistence/
    │   │   └── Doctrine/
    │   │       ├── Entity/
    │   │       └── Repository/
    │   ├── Factory/
    │   └── Validator/
    │
    └── Presenter/
        ├── Component/
        ├── Controller/
        ├── Form/
        ├── Service/
        └── Twig/
```

Do not create empty directories merely to reproduce this structure.

Create only the classes and directories required by the feature.

When a context serves multiple interfaces, Presenter may be refined according to actual project needs, for example:

```text
Presenter/
├── Frontoffice/
├── Member/
├── Backoffice/
└── Api/
```

Do not introduce this subdivision blindly if the existing project uses another healthy convention.

---

# Dependency rules

Respect the following dependency direction:

```text
Presenter -> Application -> Domain
Infrastructure -> Domain
```

Domain must remain independent.

Domain MUST NOT depend on:

- Symfony;
- Doctrine;
- Twig;
- HTTP;
- Presenter;
- Infrastructure.

---

# Persistence rules

Doctrine entities belong only in:

```text
Infrastructure/Persistence/Doctrine/Entity
```

Doctrine repositories belong only in:

```text
Infrastructure/Persistence/Doctrine/Repository
```

Repository interfaces belong in:

```text
Domain/Repository
```

Domain models belong in:

```text
Domain/Model
```

Doctrine entities must not be exposed directly to Twig or controllers when the project convention expects a Model, DTO or read model.

---

# Mapping

Doctrine Entity <-> Domain Model mapping belongs in:

```text
Infrastructure/Factory
```

Before creating a new Factory or Mapper:

1. inspect existing contexts;
2. find the current mapping convention;
3. reuse that convention.

Prefer explicit mapping.

Do not introduce automatic mapping libraries without an explicit requirement.

---

# CQRS

State-changing operations use Commands.

Examples:

- create;
- edit;
- publish;
- archive;
- activate;
- disable;
- enroll;
- schedule;
- cancel;
- confirm;
- pay;
- issue;
- complete.

Commands belong in:

```text
Application/UseCase/Command
```

Command handlers belong in:

```text
Application/UseCase/CommandHandler
```

Read operations use Queries.

Examples:

- list;
- search;
- display;
- catalog;
- dashboard;
- autocomplete;
- reporting;
- statistics.

Queries belong in:

```text
Application/UseCase/Query
```

Query handlers belong in:

```text
Application/UseCase/QueryHandler
```

Queries must never mutate application state.

---

# Controllers

Controllers must remain thin.

A controller should generally:

1. receive HTTP input;
2. validate or hydrate presentation input;
3. create and dispatch a Command or Query;
4. transform the application result for presentation when necessary;
5. return the appropriate Symfony Response.

Controllers must not contain business rules.

Controllers must not contain Doctrine QueryBuilder logic.

---

# Forms

Forms belong in:

```text
Presenter/Form
```

Forms handle user input and presentation validation.

Business invariants do not belong in FormType classes.

Follow the existing project approach regarding DTO or Model form binding.

---

# Business logic

Business logic belongs primarily in:

- Domain Models;
- Domain services when justified;
- Application handlers for orchestration.

Business logic must not be placed in:

- Controllers;
- Twig templates;
- Doctrine repositories;
- FormType classes.

---

# Validation

Input validation:

```text
Presenter / Symfony Validator
```

Business invariants:

```text
Domain
```

Persistence constraints:

```text
Infrastructure / Doctrine / database
```

Do not confuse presentation validation with business rules.

---

# Domain model rule

Do not create an anemic Domain Model automatically.

When a business invariant belongs naturally to a model, keep it close to that model.

Examples:

```text
Training::publish()
Training::archive()
Enrollment::complete()
Contribution::markPaid()
Content::publish()
```

may be appropriate when they enforce actual business behavior.

Do not add methods merely to make the model appear "rich".

---

# Enums

Use enums for stable finite business states.

Examples may include:

- training type;
- training status;
- enrollment status;
- content status;
- contribution status;
- payment status.

Do not use enums for values that should be managed dynamically through administration.

Before adding an enum, inspect `docs/BUSINESS_RULES.md`.

Do not invent enum values.

---

# Cross-context rules

Avoid sharing Doctrine entities between contexts.

A context must not directly manipulate another context's persistence model.

When communication between contexts is required, prefer existing project conventions for:

- identifiers;
- application services;
- Commands / Queries;
- domain events;
- application events.

Avoid circular dependencies.

Do not create direct persistence coupling for convenience.

---

# Permissions

Before exposing a controller action or UI operation:

1. inspect `docs/PERMISSIONS.md`;
2. inspect existing IdentityContext security conventions;
3. reuse the existing permission engine when healthy;
4. reuse existing role naming when appropriate;
5. avoid duplicate authorization mechanisms.

Backend authorization is mandatory.

Hiding a button in Twig is not sufficient security.

Public Frontoffice routes must be explicitly distinguished from protected routes.

A resource may be publicly discoverable while remaining protected for consumption.

Example:

```text
Training visibility = PUBLIC
Training access = PAID
```

This means the training detail page is public, but protected learning content still requires authorization.

---

# Business rule safety

Do not invent business rules.

When information is missing:

1. inspect `docs/`;
2. inspect existing implementation;
3. inspect the latest Avocat CI specification;
4. prefer the simplest behavior compatible with current requirements;
5. explicitly report assumptions.

Never silently introduce:

- statuses;
- automatic state transitions;
- permissions;
- contribution calculations;
- financial calculations;
- deletion rules;
- notification rules;
- certification rules;
- payment rules;
- approval workflows.

---

# Financial and historical data

Financial and historical facts must not be silently destroyed or rewritten.

Examples include:

- payments;
- contribution records;
- refunds;
- reversals;
- receipts;
- issued certificates;
- finalized records.

Prefer explicit business transitions, corrections or reversals.

Never delete production financial history merely to simplify an implementation.

---

# E-learning rules

The target e-learning domain uses `Training` as a central business concept.

Two important training types are expected:

```text
COURSE
LIVE
```

A Live is a training in its own right, not automatically a child module of a classical course.

Do not implement these concepts until the owning context and integration strategy have been confirmed from the repository audit.

For video delivery:

- YouTube may be used as an external provider;
- YouTube visibility is not application authorization;
- the backend remains authoritative for access rights;
- paid or protected resources must still be checked server-side.

---

# Content rules

Public editorial content may include:

- news;
- events;
- videos;
- photo galleries;
- announcements;
- documents.

Do not assume that editorial video and learning video are the same business concept merely because both can use YouTube.

Respect business ownership by context.

---

# Contribution rules

Contribution management requires business discovery before implementation.

Do not invent:

- contribution periods;
- contribution amounts;
- calculation methods;
- penalties;
- exemptions;
- regularization rules;
- receipt rules;
- accounting rules.

Inspect the existing system and validated business documentation first.

---

# Symfony UX and frontend rules

Frontend defaults are:

- Symfony Forms;
- Twig;
- Twig Components;
- Symfony UX Stimulus for standard JavaScript interactions;
- existing Tailwind / Shadcn-inspired UI conventions.

Use Symfony UX React only when genuinely justified by rich analytical or highly interactive requirements.

Do not introduce a separate SPA without an explicit architecture decision.

The backend remains authoritative for:

- authorization;
- validation;
- calculations;
- access rights;
- business rules.

Inline business scripts and inline DOM event handlers are prohibited when a Stimulus controller is appropriate.

---

# UI / UX rules

All new interfaces must prioritize:

- clarity;
- professional appearance;
- responsive design;
- accessibility;
- simple French labels;
- understandable actions;
- visible state;
- useful empty states;
- clear validation feedback;
- appropriate confirmation for destructive actions.

The platform serves several user types:

```text
PUBLIC VISITOR
MEMBER / LAWYER
BACKOFFICE USER
FUTURE MOBILE USER
```

Do not design every screen as an internal administrative screen.

Public Frontoffice pages must prioritize discovery, readability and trust.

Member screens must prioritize self-service and clarity.

Backoffice screens must prioritize operational efficiency and safety.

Follow `docs/UI_UX_GUIDELINES.md`.

---

# Implementation scope

Never attempt to implement the entire product specification in a single task.

Work incrementally.

For each requested feature:

1. identify the relevant requirement;
2. identify the owning Bounded Context;
3. inspect the existing implementation;
4. find one or more similar existing features;
5. identify dependencies;
6. implement the smallest complete vertical slice;
7. add or update tests;
8. run relevant checks;
9. report what was implemented;
10. report remaining work.

Do not implement future roadmap features unless required by the current feature.

Do not create speculative abstractions for future features.

---

# Existing implementation rule

Existing stable project conventions take precedence over theoretical architectural purity.

If a similar feature exists, use it as the primary implementation template.

Reuse when appropriate:

- naming;
- handler structure;
- factories;
- forms;
- controllers;
- templates;
- testing conventions;
- permission resolution;
- UI components.

Do not invent a different architecture for each feature.

---

# Simplicity rule

Avocat CI must remain understandable and maintainable.

Do not over-engineer features.

Avoid unless explicitly required:

- workflow engines;
- complex state machines;
- microservices;
- event sourcing;
- unnecessary interfaces;
- unnecessary Domain Services;
- unnecessary Value Objects;
- generic CRUD frameworks created only for this project;
- excessive abstractions;
- speculative infrastructure.

A simple, explicit implementation is preferred to a generic complex implementation.

---

# Audit mode

When the requested task is an audit, architecture review or gap analysis, switch to **audit mode**.

During audit mode:

DO:

- inspect the repository;
- inspect documentation;
- inspect dependencies;
- inspect tests;
- inspect existing contexts;
- inspect database mappings;
- inspect security;
- inspect Frontoffice;
- inspect Backoffice;
- inspect Member Area;
- inspect API capabilities;
- identify current-state architecture;
- compare current state with the Avocat CI target state;
- produce gap analysis;
- identify reusable foundations;
- identify risks;
- identify obsolete or inconsistent documentation;
- propose a phased integration strategy.

DO NOT:

- create migrations;
- change the database schema;
- add new business contexts;
- rename existing contexts;
- move namespaces;
- delete code;
- delete historical data;
- implement target features;
- perform large refactoring;
- install new dependencies;
- rewrite existing documentation as if the target state were already implemented.

Audit output must distinguish:

```text
CURRENT STATE
TARGET STATE
GAPS
RISKS
REUSABLE FOUNDATIONS
RECOMMENDED CHANGES
OPEN BUSINESS QUESTIONS
IMPLEMENTATION ORDER
```

The audit must stop before implementation unless explicitly instructed otherwise.

---

# Language conventions

Code identifiers should preferably use English.

Examples:

```text
Training
Enrollment
Contribution
Payment
Content
Event
PhotoGallery
Certificate
Notification
```

User-facing labels and messages must be written in French unless an existing project convention specifies otherwise.

---

# PHP conventions

Always use:

```php
declare(strict_types=1);
```

Prefer:

- final classes when inheritance is unnecessary;
- readonly constructor dependencies;
- constructor injection;
- typed properties;
- explicit return types;
- enums for stable finite business states.

Do not create an enum when values are expected to be freely configurable by administrators.

---

# Refactoring

Do not perform unrelated refactoring.

If an architectural inconsistency is discovered:

- mention it;
- avoid propagating it when possible;
- do not redesign the entire context;
- propose a separate refactoring task if necessary.

The requested feature remains the task boundary.

---

# Database

Database migrations must contain only changes required by the current feature.

Do not alter unrelated tables.

Do not delete existing production data through migrations unless explicitly required.

Use existing Doctrine naming and mapping conventions.

---

# Tests

When business behavior changes:

- add or update tests;
- run relevant PHPUnit tests;
- run PHPStan if configured;
- run coding standards tools if configured.

Prefer focused tests during implementation.

Run broader checks before considering a feature complete when practical.

Never remove or weaken tests merely to make the suite pass.

---

# Documentation synchronization

When a feature changes:

- architecture;
- business rules;
- permissions;
- product behavior;
- roadmap;

update only the corresponding documentation that is actually affected.

Do not rewrite unrelated documentation.

Keep code, tests and documentation synchronized.

---

# Git and commits

When quality gates are green, suggest a Conventional Commit message in the completion report.

Do not auto-commit unless explicitly requested.

If the working tree contains unrelated changes, recommend targeted staging rather than `git add .`.

---

# Completion checklist

Before completing a development task verify:

- correct Bounded Context;
- requirement scope respected;
- existing patterns reused;
- dependency boundaries respected;
- no Doctrine leakage into Domain;
- no business logic in controllers;
- no business logic in Twig;
- no business logic in FormType;
- repository interface / implementation separation respected;
- mapping consistent with project conventions;
- strict types enabled;
- namespaces correct;
- imports clean;
- types explicit;
- permissions respected;
- public/protected route behavior correct;
- tests added or updated;
- relevant tests executed;
- PHPStan or equivalent checks executed if configured;
- no unrelated feature implemented.

At completion provide a concise report containing:

1. implemented functionality;
2. main files changed;
3. tests/checks executed;
4. remaining work;
5. assumptions or inconsistencies discovered;
6. suggested Conventional Commit message when appropriate.
