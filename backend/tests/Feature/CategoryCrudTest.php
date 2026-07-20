<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_category_list_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(CategoryResource::getUrl('index'))->assertSuccessful();
    }

    public function test_can_render_category_create_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(CategoryResource::getUrl('create'))->assertSuccessful();
    }

    public function test_can_create_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CreateCategory::class)
            ->fillForm([
                'name' => 'Test Category',
                'description' => 'Test Description',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
        ]);
    }

    public function test_can_render_category_edit_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Old Category',
            'description' => 'Old Description',
        ]);

        $this->get(CategoryResource::getUrl('edit', ['record' => $category]))->assertSuccessful();
    }

    public function test_can_update_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Category To Update',
            'description' => 'Old Description',
        ]);

        Livewire::test(EditCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Category',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    public function test_can_delete_category()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Category To Delete',
        ]);

        Livewire::test(EditCategory::class, [
            'record' => $category->getRouteKey(),
        ])
            ->callAction('delete');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }
}
