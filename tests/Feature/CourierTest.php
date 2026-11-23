<?php

use App\Models\Courier;
use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

beforeEach(function () {
    Courier::factory()->createMany([
        ['name' => 'Budi Santoso',         'level' => 2],
        ['name' => 'Agung Prasetyo',        'level' => 3],
        ['name' => 'Budiono Hadi Agung',    'level' => 1],
        ['name' => 'Cahyo Wibowo',          'level' => 2],
        ['name' => 'Dewi Lestari',          'level' => 5],
    ]);
});

// ─── INDEX ────────────────────────────────────────────────────────────────────

it('returns paginated couriers sorted by name by default', function () {
    $response = getJson('/api/couriers');

    $response->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 5);

    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names)->toBe([
        'Agung Prasetyo',
        'Budi Santoso',
        'Budiono Hadi Agung',
        'Cahyo Wibowo',
        'Dewi Lestari',
    ]);
});

it('can sort by created_at descending', function () {
    // Override created_at semua courier agar urutannya deterministik
    Courier::where('name', 'Budiono Hadi Agung')->update(['created_at' => '2024-01-01']);
    Courier::where('name', 'Budi Santoso')->update(['created_at' => '2024-02-01']);
    Courier::where('name', 'Dewi Lestari')->update(['created_at' => '2024-03-01']);
    Courier::where('name', 'Cahyo Wibowo')->update(['created_at' => '2024-04-01']);
    Courier::where('name', 'Agung Prasetyo')->update(['created_at' => '2024-05-01']);

    $response = getJson('/api/couriers?sort=created_at&direction=desc');

    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names)->toBe([
        'Agung Prasetyo',
        'Cahyo Wibowo',
        'Dewi Lestari',
        'Budi Santoso',
        'Budiono Hadi Agung',
    ]);
});

it('can search couriers by partial name match', function () {
    $response = getJson('/api/couriers?search=budi+agung');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('name')->toArray();
    // Harus match "Budiono Hadi Agung" (mengandung "budi" DAN "agung")
    expect($names)->toContain('Budiono Hadi Agung');
});

it('can filter by level', function () {
    $response = getJson('/api/couriers?level=2,3');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(3); // Budi Santoso (2), Agung Prasetyo (3), Cahyo Wibowo (2)

    $levels = collect($data)->pluck('level')->unique()->toArray();
    expect($levels)->toEqualCanonicalizing([2, 3]);
});

// ─── SHOW ─────────────────────────────────────────────────────────────────────

it('returns a single courier with all fields', function () {
    $courier = Courier::where('name', 'Budi Santoso')->first();

    $response = getJson("/api/couriers/{$courier->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $courier->id)
        ->assertJsonPath('data.name', 'Budi Santoso')
        ->assertJsonPath('data.level', 2);
});

// ─── STORE ────────────────────────────────────────────────────────────────────

it('creates a courier and persists to database', function () {
    $payload = [
        'name'          => 'Eko Nugroho',
        'phone'         => '081234567890',
        'email'         => 'eko@example.com',
        'address'       => 'Jl. Merdeka No. 1',
        'vehicle_type'  => 'Motorcycle',
        'license_plate' => 'L 1234 AB',
        'level'         => 3,
        'is_active'     => true,
    ];

    $response = postJson('/api/couriers', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Eko Nugroho')
        ->assertJsonPath('data.level', 3);

    $this->assertDatabaseHas('couriers', ['name' => 'Eko Nugroho', 'phone' => '081234567890']);
});

it('validates required fields on store', function () {
    $response = postJson('/api/couriers', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'vehicle_type', 'level']);
});

it('validates phone format on store', function () {
    $response = postJson('/api/couriers', [
        'name'         => 'Test',
        'phone'        => '12345', // format salah
        'vehicle_type' => 'Motorcycle',
        'level'        => 1,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['phone']);
});

it('validates level range on store', function () {
    $response = postJson('/api/couriers', [
        'name'         => 'Test',
        'phone'        => '081234567890',
        'vehicle_type' => 'Motorcycle',
        'level'        => 9, // di luar 1-5
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['level']);
});

it('validates vehicle_type enum on store', function () {
    $response = postJson('/api/couriers', [
        'name'         => 'Test',
        'phone'        => '081234567890',
        'vehicle_type' => 'Helicopter', // bukan Motorcycle/Car/Truck
        'level'        => 2,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['vehicle_type']);
});

// ─── UPDATE ───────────────────────────────────────────────────────────────────

it('updates a courier and persists changes', function () {
    $courier = Courier::where('name', 'Budi Santoso')->first();

    $response = putJson("/api/couriers/{$courier->id}", [
        'name'         => 'Budi Updated',
        'phone'        => '081111111111',
        'vehicle_type' => 'Car',
        'level'        => 4,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Budi Updated')
        ->assertJsonPath('data.level', 4);

    $this->assertDatabaseHas('couriers', ['id' => $courier->id, 'name' => 'Budi Updated']);
});

it('validates required fields on update', function () {
    $courier = Courier::where('name', 'Budi Santoso')->first();

    $response = putJson("/api/couriers/{$courier->id}", [
        'name' => '', // kosong
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'vehicle_type', 'level']);
});

// ─── DESTROY ──────────────────────────────────────────────────────────────────

it('deletes a courier and removes from database', function () {
    $courier = Courier::where('name', 'Budi Santoso')->first();

    $response = deleteJson("/api/couriers/{$courier->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('couriers', ['id' => $courier->id]);
});