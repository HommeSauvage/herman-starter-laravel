# Testing

Tests exist to catch the bugs their author did not see. A test written by the
same head that wrote the code, from the same wrong assumption, proves nothing —
it just doubles the confidence in a bug. Write tests against the requirement,
not against the diff.

## Principles

1. **Test behavior, not implementation.** Assert what the user or caller
   observes: a response, a redirect, a stored row, a rendered page. Never assert
   on private methods, internal call order, or how a value was computed.
   Behavior tests survive refactors; implementation tests false-alarm on them.

2. **Derive tests from the requirement, not from the code you just wrote.** If
   you write the assertion by reading the implementation, you are encoding your
   own bug as the expected result.

3. **Prefer integration, then e2e, then nothing.** Exercise the real stack —
   request, route, middleware, validation, database, response. Integration
   tests catch wiring, auth, validation and persistence bugs that unit tests
   structurally cannot.

4. **Do not write unit tests for trivial code.** Getters, casts, simple
   transformations and one-line helpers do not earn a test. If a typo in it
   would fail the suite elsewhere, it is already covered.

5. **One test per behavior or risk, not per method.** A single journey through
   create, read, update and delete beats five per-endpoint tests. Cover the
   seams and the invariants; leave the rest.

6. **Sharp edges are worth more than a repeated happy path.** Authorization
   boundaries, validation failures, empty states and limits are where the bugs
   are. Name the edge cases; test those.

7. **A test that cannot fail is noise.** If the assertion would still pass with
   the feature deleted, it is decoration. Ask the negative question: does
   breaking the code break the test?

## Tiers

Pick the lowest tier that proves the behavior.

### Integration — the default (tests/Feature)

Drive the real HTTP stack: route → middleware → controller → validation →
database → Inertia response. RefreshDatabase is applied to tests/Feature in
tests/Pest.php, so there is no per-test setup.

    test('a member can create a note', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notes.store'), ['title' => 'Q3', 'body' => '## Goals'])
            ->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'title' => 'Q3',
            'team_id' => $user->current_team_id,
        ]);

        $this->get(route('notes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('notes.data', 1));
    });

- Build state with factories, never raw inserts.
- Assert behavior, not internals: assertOk, assertRedirect, assertSessionHasErrors,
  assertDatabaseHas / assertDatabaseMissing, and assertInertia for the page
  component and its props.
- URL defaults come from the acting user's current team. When a test creates a
  second user, pass current_team explicitly or the request targets the wrong team.

### Smoke — one per route surface

A cheap guard that every page still boots. Loop named routes and assert none
fails.

    test('public pages render', function (string $route) {
        $this->get(route($route))->assertOk();
    })->with(['home', 'posts.index']);

### End-to-end — browser, opt-in (tests/Browser)

Use only when behavior depends on real JS: hydration, focus, drag, client state.
It is not installed by default. To enable:

    composer require pestphp/pest-plugin-browser --dev
    bun add -d playwright
    bunx playwright install

Run it with php artisan test tests/Browser. Browser tests drive a real browser
(visit, click, type, assertSee) and are the slowest tier — keep them few and
journey-shaped.

## Commands

- composer run test — config clear, Pint check, PHPStan, full Pest suite.
- composer run ci:check — the contract: adds ESLint, Prettier, tsc, fallow, soak.
- php artisan test --compact --filter=Note — the fast loop while iterating.

## What not to do

- Tests that re-implement the code under test and assert the two agree.
- Mocking the database, HTTP layer or framework so heavily that the test
  exercises the mocks instead of the app.
- Snapshot assertions nobody reads, kept green by regenerating them.
- Tests written to reach a coverage number.
- A test file per class, a test per method.
- Tests for a bug that keep asserting the buggy behavior.

## Cost

Tests are not free: every one is tokens to write, time to run, and maintenance
forever. Spend that budget on the few integration tests that cover a real user
journey and its sharp edges, not on breadth.
