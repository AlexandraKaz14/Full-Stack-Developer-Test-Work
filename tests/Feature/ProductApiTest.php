<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;
    protected $headers;

    /**
     * Настройка тестовой среды
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json'
        ];

        Category::factory(5)->create();
    }

    /**
     * Тест получения списка товаров (GET /api/products)
     */
    public function test_can_get_paginated_products(): void
    {
        Product::factory(25)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'description', 'price', 'category_id', 'created_at', 'updated_at', 'deleted_at']
                ],
            ])
            ->assertJsonCount(15, 'data');
    }

    /**
     * Тест создания товара без аутентификации (должен вернуть 401)
     */
    public function test_cannot_create_product_without_authentication(): void
    {
        $category = Category::first();
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category_id' => $category->id
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(401);
    }

    /**
     * Тест создания товара с аутентификацией (POST /api/products)
     */
    public function test_can_create_product_with_authentication(): void
    {
        $category = Category::first();
        $productData = [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 149.99,
            'category_id' => $category->id
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/products', $productData);

        $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'name', 'description', 'price', 'category_id', 'created_at', 'updated_at'
        ])
            ->assertJson([
                'name' => 'New Product',
                'price' => 149.99,
                'category_id' => $category->id
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 149.99
        ]);
    }

    /**
     * Тест валидации при создании товара
     */
    public function test_validation_for_creating_product(): void
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/products', [
                'name' => '',
                'price' => -10,
                'category_id' => 9999
            ]);

        $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price', 'category_id']);
    }

    /**
     * Тест получения одного товара (GET /api/products/{id})
     */
    public function test_can_get_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price
            ]);
    }

    /**
     * Тест получения несуществующего товара
     */
    public function test_cannot_get_nonexistent_product(): void
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'No query results for model [App\\Models\\Product] 99999']);
    }

    /**
     * Тест обновления товара (PUT /api/products/{id})
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();
        $newCategory = Category::factory()->create();

        $updateData = [
            'name' => 'Updated Product Name',
            'price' => 199.99,
            'category_id' => $newCategory->id
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => 'Updated Product Name',
                'price' => 199.99,
                'category_id' => $newCategory->id
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'price' => 199.99
        ]);
    }

    /**
     * Тест частичного обновления товара (PATCH /api/products/{id})
     */
    public function test_can_partially_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->patchJson("/api/products/{$product->id}", [
                'price' => 299.99
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $product->id,
                'name' => $product->name,
                'price' => 299.99
            ]);
    }

    /**
     * Тест удаления товара (DELETE /api/products/{id})
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson(['status' => true]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Тест удаления несуществующего товара
     */
    public function test_cannot_delete_nonexistent_product(): void
    {
        $response = $this->withHeaders($this->headers)
            ->deleteJson('/api/products/99999');

        $response->assertStatus(404);
    }

    /**
     * Тест фильтрации товаров по категории
     */
    public function test_can_filter_products_by_category(): void
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        Product::factory(10)->create(['category_id' => $category1->id]);
        Product::factory(5)->create(['category_id' => $category2->id]);

        $response = $this->getJson("/api/products?category_id={$category1->id}");

        $response->assertStatus(200);

        $response->assertJsonMissing(['category_id' => $category2->id]);
    }

    /**
     * Тест поиска товаров по имени
     */
    public function test_can_search_products_by_name(): void
    {
        Product::factory()->create(['name' => 'iPhone 15 Pro']);
        Product::factory()->create(['name' => 'Samsung Galaxy S23']);
        Product::factory()->create(['name' => 'Google Pixel 8']);

        $response = $this->getJson('/api/products?search=iPhone');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'iPhone 15 Pro'])
            ->assertJsonMissing(['name' => 'Samsung Galaxy S23'])
            ->assertJsonMissing(['name' => 'Google Pixel 8']);
    }

    /**
     * Тест создания товара с минимальными данными
     */
    public function test_can_create_product_with_minimal_data(): void
    {
        $category = Category::first();

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/products', [
                'name' => 'Minimal Product',
                'price' => 1.00,
                'category_id' => $category->id
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => 'Minimal Product',
            'price' => 1.00,
            'description' => null
        ]);
    }

    /**
     * Тест уникальности имени товара
     */
    public function test_product_name_must_be_unique(): void
    {
        $category = Category::first();
        Product::factory()->create(['name' => 'Unique Product']);

        $response = $this->withHeaders($this->headers)
            ->postJson('/api/products', [
                'name' => 'Unique Product', // То же имя
                'price' => 100,
                'category_id' => $category->id
            ]);

         $response->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
    }

    /**
     * Тест массового создания товаров
     */
    public function test_performance_for_many_products(): void
    {
        Product::factory(1000)->create();

        $startTime = microtime(true);

        $response = $this->getJson('/api/products?per_page=100');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        $this->assertLessThan(2.0, $executionTime, 'Product listing is too slow');
    }

    /**
     * Тест структуры ответа при ошибке валидации
     */
    public function test_validation_error_response_structure(): void
    {
        $response = $this->withHeaders($this->headers)
            ->postJson('/api/products', [
                'name' => '',
                'price' => 'not-a-number',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'name',
                    'price',
                    'category_id'
                ]
            ]);
    }
}
