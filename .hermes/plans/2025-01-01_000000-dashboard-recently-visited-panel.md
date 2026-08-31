# Latest Tickets Panel — Show Most Recently Visited Tickets Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Fix the dashboard "Latest Tickets" panel so it always shows the 5 tickets most recently opened/clicked-into by agents (ordered by `last_visited_at`), regardless of which page of the paginated ticket list the user is viewing.

**Architecture:** Add a dedicated, unpaginated Eloquent query in the `/dashboard` route closure that selects the top 5 tickets by `COALESCE(last_visited_at, created_at) DESC`. Pass this as a separate `$latestTickets` variable to the view. Update the "Latest Tickets" panel in `dashboard.blade.php` to iterate over `$latestTickets` instead of `$tickets->take(5)`. Add a feature test asserting the panel shows the most-recently-visited tickets in the correct order.

**Tech Stack:** Laravel 12 (PHP 8.4), Blade, Eloquent, Pest, SQLite (test) / Postgres (prod)

---

## Current Context / Assumptions

- The `/dashboard` route is defined as an inline closure in `routes/web.php` (lines 18-67).
- `$tickets` is fetched with `->paginate(15)->withQueryString()` (line 61), ordered by `COALESCE(last_visited_at, updated_at, created_at) DESC` (line 43).
- The "Latest Tickets" panel in `resources/views/dashboard.blade.php` (lines 414-536) iterates `$tickets->take(5)`.
- **Bug:** `$tickets` is a `LengthAwarePaginator`. `->take(5)` slices the *current page's 15 items* down to 5, NOT the global top 5. On page 2, the panel shows page-2's top 5, which is incorrect.
- The `Ticket` model has a `last_visited_at` column (migration `2026_08_20_064500_add_last_visited_at_to_tickets_table.php`) and a `touchVisited()` method that updates it.
- `touchVisited()` is called in `TicketController::show()` (line 162) when a ticket detail page is viewed.
- The panel label should read "Recently Visited" (not "Latest Tickets") to accurately reflect the ordering.
- AGENTS.md rules: run lint/type-check/build after changes; use drizzle generate+migrate for schema changes (N/A here — no schema change); test with available tools.

## Proposed Approach

1. Add a `$latestTickets` query to the `/dashboard` route closure — a simple `Ticket::with([...])->orderByRaw(...)->limit(5)->get()`.
2. Pass `$latestTickets` to the view via `compact()`.
3. Update the "Latest Tickets" panel in `dashboard.blade.php` to loop over `$latestTickets` and rename the header to "Recently Visited".
4. Add a Pest feature test that creates 3 tickets, visits ticket #2's detail page (triggering `touchVisited`), then asserts the dashboard shows ticket #2 first in the "Recently Visited" panel.

---

## Step-by-Step Plan

### Task 1: Write Failing Feature Test

**Objective:** Create a Pest feature test that asserts the dashboard "Recently Visited" panel shows tickets ordered by `last_visited_at` (most recently visited first), independent of pagination.

**Files:**
- Create: `tests/Feature/DashboardLatestTicketsTest.php`

**Step 1: Write the failing test**

```php
<?php

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\User;

use function Pest\Laravel\{actingAs, get};

describe('Dashboard Recently Visited panel', function () {
    it('shows the most recently visited tickets at the top, regardless of pagination', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();

        // Three tickets created in sequence; none visited yet.
        $ticketA = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
            'title'       => 'Ticket A — oldest creation',
        ]);
        $ticketB = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
            'title'       => 'Ticket B — visited most recently',
        ]);
        $ticketC = Ticket::factory()->create([
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
            'title'       => 'Ticket C — never visited',
        ]);

        // Simulate an agent opening ticket B's detail page (triggers touchVisited).
        actingAs($admin);
        get(route('tickets.show', $ticketB));

        // Ticket B should now have a last_visited_at timestamp.
        expect($ticketB->fresh()->last_visited_at)->not->toBeNull();

        // Hit the dashboard.
        $response = get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'Ticket B — visited most recently',
            // Ticket A and C have no last_visited_at, so they fall back to
            // created_at ordering. C was created after A, so C comes next.
            'Ticket C — never visited',
            'Ticket A — oldest creation',
        ]);
    });

    it('limits the Recently Visited panel to 5 tickets', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::factory()->create();

        // Create 8 tickets and visit 6 of them so that only 5 should appear.
        $visited = Ticket::factory()->count(6)->create([
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
        ]);
        Ticket::factory()->count(2)->create([
            'customer_id' => $customer->id,
            'created_by'  => $admin->id,
        ]);

        actingAs($admin);
        foreach ($visited as $ticket) {
            get(route('tickets.show', $ticket));
        }

        $response = get(route('dashboard'));

        $response->assertStatus(200);

        // The panel should contain exactly 5 ticket rows. We assert by counting
        // occurrences of the "Recently Visited" panel's data attribute.
        // The panel uses x-data="quickChatPopover" on each row.
        $html = $response->getContent();
        $panelCount = substr_count($html, 'quickChatPopover(');
        expect($panelCount)->toBe(5);
    });
});
```

**Step 2: Run the test to verify failure**

Run:
```bash
php artisan test --filter=DashboardLatestTicketsTest
```
Expected: **FAIL** — the panel currently uses `$tickets->take(5)` which shows the paginated list's top 5 by creation order, not by `last_visited_at`. The `assertSeeInOrder` assertion will fail because ticket B won't be first (it has `last_visited_at` set, but the panel reuses the paginated `$tickets` collection which doesn't guarantee the global top 5).

**Step 3: Commit the failing test**

```bash
git add tests/Feature/DashboardLatestTicketsTest.php
git commit -m "test: add failing test for dashboard recently-visited panel ordering"
```

---

### Task 2: Add Dedicated `$latestTickets` Query to Dashboard Route

**Objective:** Add a separate, unpaginated query in the `/dashboard` route closure that fetches the top 5 tickets by most-recently-visited, and pass it to the view.

**Files:**
- Modify: `routes/web.php` (lines 39-66)

**Step 1: Add the query after the paginated `$tickets` fetch**

In `routes/web.php`, immediately after line 61 (`$tickets = $query->paginate(15)->withQueryString();`), add:

```php
    // Dedicated query for the "Recently Visited" panel — global top 5 by
    // last_visited_at, independent of the paginated $tickets collection.
    $latestTickets = Ticket::with([
        'customer:id,name,customer_id',
        'assignee:id,name',
    ])
        ->orderByRaw('COALESCE(last_visited_at, created_at) DESC')
        ->limit(5)
        ->get();
```

**Step 2: Add `$latestTickets` to the `compact()` call**

Change line 66 from:

```php
    return view('dashboard', compact('tickets', 'stats', 'assignableUsers', 'uniqueCustomers'));
```

to:

```php
    return view('dashboard', compact('tickets', 'stats', 'assignableUsers', 'uniqueCustomers', 'latestTickets'));
```

**Step 3: Verify the route file has no syntax errors**

Run:
```bash
php -l routes/web.php
```
Expected: `No syntax errors detected in routes/web.php`

**Step 4: Commit**

```bash
git add routes/web.php
git commit -m "feat: add dedicated latestTickets query to dashboard route"
```

---

### Task 3: Update the Dashboard Panel to Use `$latestTickets`

**Objective:** Change the "Latest Tickets" panel in `dashboard.blade.php` to iterate `$latestTickets` instead of `$tickets->take(5)`, and rename the header to "Recently Visited".

**Files:**
- Modify: `resources/views/dashboard.blade.php` (lines 417, 420)

**Step 1: Rename the panel header**

Change line 417 from:

```blade
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Latest Tickets</h3>
```

to:

```blade
                    <h3 class="font-display text-[14px] font-bold tracking-[-0.01em] text-[var(--foreground)]">Recently Visited</h3>
```

**Step 2: Change the loop to use `$latestTickets`**

Change line 420 from:

```blade
                    @forelse ($tickets->take(5) as $ticket)
```

to:

```blade
                    @forelse ($latestTickets as $ticket)
```

**Step 3: Update the empty-state message (optional, line 533)**

The current empty message is `"No tickets yet."`. Change it to be consistent with the panel's new purpose:

```blade
                    <p class="px-5 py-8 text-center text-[12.5px] text-[var(--muted)]">No recently visited tickets.</p>
```

**Step 4: Verify the Blade compiles**

Run:
```bash
php artisan view:cache
```
Expected: `INFO Blade templates cached successfully.`

**Step 5: Commit**

```bash
git add resources/views/dashboard.blade.php
git commit -m "feat: dashboard panel shows recently-visited tickets via latestTickets"
```

---

### Task 4: Run Tests and Verify

**Objective:** Confirm the failing test now passes and no existing tests broke.

**Step 1: Run the new test**

Run:
```bash
php artisan test --filter=DashboardLatestTicketsTest
```
Expected: **PASS** — 2 tests, 2 passed.

**Step 2: Run the full unit suite (fast smoke check)**

Run:
```bash
php artisan test --testsuite=Unit
```
Expected: All unit tests pass (no regression — the change only touches the dashboard route and view).

**Step 3: Run the full feature suite**

Run:
```bash
php artisan test --testsuite=Feature
```
Expected: All feature tests pass. If a database-dependent test times out (no local DB), note it as a known environment limitation — the unit suite + manual verification below cover the change.

**Step 4: Manual verification (local browser)**

1. Start the dev server: `php artisan serve`
2. Log in as an admin.
3. Open 3 different tickets from the dashboard.
4. Return to `/dashboard`.
5. Confirm the "Recently Visited" panel shows those 3 tickets at the top, in reverse-chronological order of when you clicked them.
6. Navigate to page 2 of the ticket list (append `?page=2`).
7. Confirm the "Recently Visited" panel **still** shows the same 5 most-recently-visited tickets (not page-2 items).

**Step 5: Commit if any fixes were needed**

If the test or manual check surfaced a bug, fix it and commit:
```bash
git add -A
git commit -m "fix: correct recently-visited panel behavior"
```

---

## Files Likely to Change

| File | Change |
|------|--------|
| `routes/web.php` | Add `$latestTickets` query + `compact()` entry |
| `resources/views/dashboard.blade.php` | Rename header, change loop source, update empty message |
| `tests/Feature/DashboardLatestTicketsTest.php` | New test file |

## Tests / Validation

- **New test:** `tests/Feature/DashboardLatestTicketsTest.php` — asserts ordering by `last_visited_at` and 5-item limit.
- **Existing tests:** `php artisan test --testsuite=Unit` must still pass (no regression).
- **Syntax/lint:** `php -l routes/web.php`, `php artisan view:cache`.
- **Manual:** the 7-step browser check in Task 4, Step 4.

## Risks, Tradeoffs, and Open Questions

1. **Extra DB query per dashboard load.** The new `$latestTickets` query adds one SELECT (with 2 eager-loaded relations) to every `/dashboard` request. With a `limit(5)` and the existing `last_visited_at` index, this is negligible. No caching needed unless the tickets table grows to hundreds of thousands of rows.
2. **Factory prerequisites.** The test assumes `Ticket::factory()` and `Customer::factory()` exist. If they don't, the implementer must create them first (check `database/factories/`).
3. **`touchVisited` timing.** `touchVisited()` writes directly via `DB::table('tickets')` (bypassing model events). The test calls `get(route('tickets.show', $ticket))` which triggers the controller's `touchVisited()` — this is the correct integration path. If the test environment doesn't run the middleware stack, the implementer may need to call `$ticket->touchVisited()` directly in the test instead.
4. **Label wording.** "Recently Visited" is clearer than "Latest Tickets" for this ordering. If the user prefers "Latest Tickets" to stay, keep the original label — the ordering fix is what matters.
5. **Empty `last_visited_at`.** On a fresh install with no ticket detail pages visited, all `last_visited_at` values are NULL. The `COALESCE(last_visited_at, created_at)` fallback ensures the panel still shows the 5 newest tickets by creation date — a sensible default.

## Execution Handoff

Plan complete. Ready to execute using subagent-driven-development — I'll dispatch a fresh subagent per task with two-stage review (spec compliance then code quality). Shall I proceed?
