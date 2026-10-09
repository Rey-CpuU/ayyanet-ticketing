<?php

use App\Models\User;

/**
 * Render a page as the given user and return only the <nav> markup, so page bodies
 * (which may link to the same routes) do not influence the assertions.
 */
function navHtml(User $user, string $url = '/my-tickets'): string
{
    $html = test()->actingAs($user)->get($url)->assertOk()->getContent();

    expect(preg_match('/<nav\b.*?<\/nav>/s', $html, $matches))->toBe(1);

    return $matches[0];
}

function navHrefs(string $nav): array
{
    preg_match_all('/href="([^"]+)"/', $nav, $matches);

    return $matches[1];
}

test('admin sees daily links, the full Kelola menu and the new ticket button', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $nav = navHtml($admin, '/dashboard');
    $hrefs = navHrefs($nav);

    foreach (['dashboard', 'tickets.index', 'customers.index', 'settings.index', 'reports.index', 'status-banners.index', 'users.index', 'tickets.create'] as $route) {
        expect($hrefs)->toContain(route($route));
    }

    expect($nav)->toContain('Kelola')
        ->toContain('Status Banner')
        ->toContain('Tiket Baru')
        ->toContain('data-nav-new-ticket')
        ->toContain('data-nav="mobile-tabs"')
        ->toContain('Lainnya')
        ->not->toContain(route('my.tickets').'"');
});

test('cs sees Kelola without Users', function () {
    $cs = User::factory()->create(['role' => 'cs']);
    $nav = navHtml($cs, '/dashboard');
    $hrefs = navHrefs($nav);

    foreach (['dashboard', 'tickets.index', 'customers.index', 'settings.index', 'reports.index', 'status-banners.index', 'tickets.create'] as $route) {
        expect($hrefs)->toContain(route($route));
    }

    expect($hrefs)->not->toContain(route('users.index'));
    expect($nav)->toContain('Kelola')->toContain('Tiket Baru')->toContain('Lainnya');
});

test('lapangan only sees Tiket Saya, notifications and profile', function () {
    $lapangan = User::factory()->create(['role' => 'lapangan']);
    $nav = navHtml($lapangan);
    $hrefs = navHrefs($nav);

    expect($hrefs)->toContain(route('my.tickets'))
        ->toContain(route('profile.edit'));

    foreach (['dashboard', 'tickets.index', 'customers.index', 'settings.index', 'reports.index', 'status-banners.index', 'users.index', 'tickets.create'] as $route) {
        expect($hrefs)->not->toContain(route($route));
    }

    expect($nav)->toContain('Tiket Saya')
        ->toContain('Notifikasi')
        ->toContain('data-nav-bell')
        ->not->toContain('Kelola')
        ->not->toContain('Tiket Baru')
        ->not->toContain('Lainnya')
        ->not->toContain('data-nav-new-ticket');
});

test('Kelola is highlighted on management pages only', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    foreach (['/reports', '/status-banners', '/users'] as $url) {
        expect(navHtml($admin, $url))->toMatch('/data-nav-kelola\s+class="[^"]*border-\[var\(--accent\)\]/');
    }

    expect(navHtml($admin, '/dashboard'))->not->toMatch('/data-nav-kelola\s+class="[^"]*border-\[var\(--accent\)\]/');
});

test('page headers no longer duplicate the new ticket button', function () {
    $cs = User::factory()->create(['role' => 'cs']);

    foreach (['/dashboard', '/tickets'] as $url) {
        $html = $this->actingAs($cs)->get($url)->assertOk()->getContent();
        $withoutNav = preg_replace('/<nav\b.*?<\/nav>/s', '', $html);

        expect($withoutNav)->not->toContain('class="btn-primary"');
    }
});
