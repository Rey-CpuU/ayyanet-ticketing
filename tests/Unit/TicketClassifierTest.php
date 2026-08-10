<?php

use App\Support\TicketClassifier;

test('classifies hardware issues', function () {
    expect(TicketClassifier::classify('Modem rusak, indikator tidak menyala'))
        ->category->toBe('Hardware');
});

test('classifies billing issues', function () {
    expect(TicketClassifier::classify('Tagihan bulan ini lebih besar dari biasanya'))
        ->category->toBe('Billing');
});

test('classifies internet connection issues', function () {
    $result = TicketClassifier::classify('Internet sering putus di malam hari');

    expect($result['category'])->toBe('Internet')
        ->and($result['priority'])->toBe('Medium');
});

test('classifies layanan requests', function () {
    $result = TicketClassifier::classify('Lupa password wifi');

    expect($result['category'])->toBe('Layanan')
        ->and($result['priority'])->toBe('Low');
});

test('escalates widespread outage to high priority', function () {
    $result = TicketClassifier::classify('Semua pelanggan di area tidak bisa koneksi');

    expect($result['category'])->toBe('Internet')
        ->and($result['priority'])->toBe('High');
});

test('uses description when title is generic', function () {
    $result = TicketClassifier::classify('Ada masalah', 'Tagihan bulan ini terasa lebih besar');

    expect($result['category'])->toBe('Billing');
});

test('falls back to Other category', function () {
    expect(TicketClassifier::classify('Mohon konfirmasi hal lain'))
        ->category->toBe('Other');
});

test('is case insensitive', function () {
    expect(TicketClassifier::classify('MODEM TIDAK MENYALA'))
        ->category->toBe('Hardware');
});
