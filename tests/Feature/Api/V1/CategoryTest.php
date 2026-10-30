<?php

use App\Models\Category;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();

    Sanctum::actingAs($this->user);

    $this->withHeader('Accept-Language', 'es');
});

/**
 * @return array<string, string>
 */
function categoryPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Súper',
        'icon' => 'cart',
        'color' => '#22AA66',
    ], $overrides);
}

describe('index', function () {
    it('lists only the active categories of the user sorted by name', function () {
        Category::factory()->for($this->user)->create(['name' => 'Gasolina']);
        Category::factory()->for($this->user)->create(['name' => 'Comida']);
        Category::factory()->for($this->user)->create(['name' => 'Borrada'])->delete();
        Category::factory()->create(['name' => 'Ajena']);

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonPath('meta.max', Category::MAX_PER_USER)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Comida')
            ->assertJsonPath('data.1.name', 'Gasolina')
            ->assertJsonStructure(['data' => [['id', 'name', 'icon', 'color', 'created_at', 'updated_at']]]);
    });
});

describe('store', function () {
    it('creates a category for the user', function () {
        $this->postJson('/api/v1/categories', categoryPayload())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Súper')
            ->assertJsonPath('data.color', '#22AA66');

        expect($this->user->categories()->sole()->name)->toBe('Súper');
    });

    it('rejects the category beyond the limit', function () {
        Category::factory()->for($this->user)->count(Category::MAX_PER_USER)->create();

        $this->postJson('/api/v1/categories', categoryPayload())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'límite de 10 categorías']);

        expect($this->user->categories()->count())->toBe(Category::MAX_PER_USER);
    });

    it('does not count deleted categories toward the limit', function () {
        Category::factory()->for($this->user)->count(Category::MAX_PER_USER)->create()->first()->delete();

        $this->postJson('/api/v1/categories', categoryPayload())->assertCreated();
    });

    it('rejects a name the user already has regardless of case', function () {
        Category::factory()->for($this->user)->create(['name' => 'Súper']);

        $this->postJson('/api/v1/categories', categoryPayload(['name' => 'SÚPER']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name' => 'Ya tienes una categoría con ese nombre.']);
    });

    it('allows a name used by a deleted category or by another user', function () {
        Category::factory()->for($this->user)->create(['name' => 'Súper'])->delete();
        Category::factory()->create(['name' => 'Súper']);

        $this->postJson('/api/v1/categories', categoryPayload())->assertCreated();
    });

    it('validates the fields', function (array $payload, string $field) {
        $this->postJson('/api/v1/categories', categoryPayload($payload))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    })->with([
        'missing name' => [['name' => ''], 'name'],
        'long name' => [['name' => str_repeat('a', 51)], 'name'],
        'missing icon' => [['icon' => ''], 'icon'],
        'icon with spaces' => [['icon' => 'shopping cart'], 'icon'],
        'short color' => [['color' => '#FFF'], 'color'],
        'color without hash' => [['color' => '22AA66'], 'color'],
    ]);
});

describe('update', function () {
    it('updates a category of the user', function () {
        $category = Category::factory()->for($this->user)->create(['name' => 'Super']);

        $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'Súper', 'color' => '#000000'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Súper')
            ->assertJsonPath('data.color', '#000000');
    });

    it('allows keeping the same name with a different case', function () {
        $category = Category::factory()->for($this->user)->create(['name' => 'súper']);

        $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'Súper'])->assertOk();
    });

    it('rejects renaming to the name of another category of the user', function () {
        Category::factory()->for($this->user)->create(['name' => 'Comida']);
        $category = Category::factory()->for($this->user)->create(['name' => 'Súper']);

        $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'comida'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    });

    it('forbids updating a category of another user', function () {
        $category = Category::factory()->create(['name' => 'Ajena']);

        $this->putJson("/api/v1/categories/{$category->id}", ['name' => 'Mía'])->assertForbidden();

        expect($category->fresh()->name)->toBe('Ajena');
    });
});

describe('destroy', function () {
    it('soft deletes a category of the user', function () {
        $category = Category::factory()->for($this->user)->create();

        $this->deleteJson("/api/v1/categories/{$category->id}")->assertNoContent();

        expect($category->fresh()->trashed())->toBeTrue();
    });

    it('forbids deleting a category of another user', function () {
        $category = Category::factory()->create();

        $this->deleteJson("/api/v1/categories/{$category->id}")->assertForbidden();

        expect($category->fresh()->trashed())->toBeFalse();
    });

    it('returns not found for an already deleted category', function () {
        $category = Category::factory()->for($this->user)->create();
        $category->delete();

        $this->deleteJson("/api/v1/categories/{$category->id}")->assertNotFound();
    });
});
