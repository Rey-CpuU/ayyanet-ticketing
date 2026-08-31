---
name: agentic-fullstack-workflow
description: >-
  Guides the agent to systematically build a full-stack feature by reading a PRD. Covers database, backend, APIs, logic, middleware, and UI implementation.
---

# Agentic Fullstack Workflow

## Overview
This skill provides a structured prompt for agents to systematically build full-stack features from a Product Requirements Document (PRD). It ensures all aspects of modern web development (backend, APIs, UI, logic, middleware) are covered sequentially and thoughtfully.

## Quick Start
"Implement the feature described in `feature-prd.md` using the agentic-fullstack-workflow."

## Workflow

### 1. Analyze PRD & Context
- Read the provided PRD or feature request.
- Review project rules (e.g., `AGENTS.md`) and any existing design system documents.
- If there are ambiguities, missing details, or conflicting requirements, **ask the user for clarification** before proceeding.

### 2. Architecture & Planning
- Draft a quick plan outlining:
  - Database schema changes.
  - API endpoint contracts (Request/Response).
  - Frontend component structure.
- Propose this plan to the user and **wait for explicit approval** before writing any code.

### 3. Backend & Database
- Implement database schema changes or ORM models.
- Create or update queries.
- Run migrations or generate commands (e.g., `drizzle generate`) as per project rules.

### 4. API & Middleware
- Implement API routes/endpoints.
- Add necessary middleware (e.g., authentication, validation, logging).
- Implement core business logic.

### 5. UI & Frontend Logic
- Build the frontend UI components following the project's design system.
- Connect the frontend to the APIs.
- Handle loading states, error states, and UI logic.

### 6. Testing & Polish
- Write tests (unit/integration) for the new feature.
- Run linting, type checks, and build commands (e.g. `next build`) to verify code quality.
- Review the implemented feature against the original PRD to ensure all requirements are met.

## Common Mistakes
- Skipping the Architecture & Planning phase and immediately writing code.
- Failing to ask for clarification on ambiguous PRD requirements.
- Forgetting to run necessary schema migrations (e.g. `drizzle generate` / `drizzle migrate`) or type checks after making backend changes.
