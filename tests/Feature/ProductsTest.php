<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductsTest extends TestCase
{

    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser();
        $this->admin = $this->createUser(isAdmin: true);
    }




    public function test_homepage_contains_empty_table(): void
    {

        //$user = User::factory()->create();
        $user = $this->createUser();

        //$response = $this->get('/products');
        //$response = $this->actingAs($user)->get('/products');
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertSee(__('No products found'));
    }

    public function test_homepage_contains_non_empty_table(): void
    {
        //Product::create([
        //    'name'  => 'Product 1',
        //    'price' => 123,
        //]);

        $product = Product::create([
            'name'  => 'Product 1',
            'price' => 123,
        ]);

        //$user = User::factory()->create();
        $user = $this->createUser();

        //$response = $this->actingAs($user)->get('/products');
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertDontSee(__('No products found'));
        $response->assertSee('Product 1');
        $response->assertViewHas('products', function (LengthAwarePaginator $collection) use ($product) {
            return $collection->contains($product);
        });
    }


    public function test_paginated_products_table_doesnt_contain_11th_record()
    {
        $products = Product::factory(11)->create();

        $lastProduct = $products->last();

        //$user = User::factory()->create();
        $user = $this->createUser();

        //$response = $this->actingAs($user)->get('/products');
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
        $response->assertViewHas('products', function (LengthAwarePaginator $collection) use ($lastProduct) {
            return $collection->doesntContain($lastProduct);
        });

    }


    public function test_admin_can_see_products_create_button()
    {
        //$admin = User::factory()->create(['is_admin' => true]);
        //$response = $this->actingAs($admin)->get('/products');
        $response = $this->actingAs($this->admin)->get('/products');

        $response->assertStatus(200);
        $response->assertSee('Add new product');
    }

    public function test_non_admin_cannot_see_products_create_button()
    {
        $response = $this->actingAs($this->user)->get('/products');


        $response->assertStatus(200);
        $response->assertDontSee('Add new product');
    }

    public function test_admin_can_access_product_create_page()
    {
        //$admin = User::factory()->create(['is_admin' => true]);
        //$response = $this->actingAs($admin)->get('/products/create');
        $response = $this->actingAs($this->admin)->get('/products/create');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_product_create_page()
    {
        $response = $this->actingAs($this->user)->get('/products/create');

        $response->assertStatus(403);
    }

    public function test_create_product_successful()
    {
        $product = [
            'name' => 'Product 123',
            'price' => 1234
        ];

        $response = $this->actingAs($this->admin)->post('/products', $product);

        $response->assertStatus(302);
        $response->assertRedirect('products');

        $this->assertDatabaseHas('products', $product);

        $lastProduct = Product::latest()->first();
        $this->assertEquals($product['name'], $lastProduct->name);
        $this->assertEquals($product['price'], $lastProduct->price);
    }


    public function test_product_edit_contains_correct_values()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->get('products/' . $product->id . '/edit');

        $response->assertStatus(200);
        $response->assertSee('value="' . $product->name . '"', false);
        $response->assertSee('value="' . $product->price . '"', false);
        $response->assertViewHas('product', $product);
    }


    public function test_product_update_validation_error_redirects_back_to_form()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->put('products/' . $product->id, [
            'name' => '',
            'price' => ''
        ]);

        $response->assertStatus(302);
        $response->assertInvalid(['name', 'price']);
        $response->assertSessionHasErrors(['name', 'price']);
    }


    public function test_product_delete_successful()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)->delete('products/' . $product->id);

        $response->assertStatus(302);
        $response->assertRedirect('products');

        $this->assertDatabaseMissing('products', $product->toArray());
        $this->assertDatabaseCount('products', 0);

        $this->assertModelMissing($product);
        $this->assertDatabaseEmpty('products');
    }


    //API
    public function test_api_returns_products_list()
    {
        $product = Product::factory()->create();
        $response = $this->getJson('/api/products');

        $response->assertJson([$product->toArray()]);
    }

    public function test_api_product_store_successful()
    {
        $product = [
            'name' => 'Product 1',
            'price' => 123
        ];
        $response = $this->postJson('/api/products', $product);

        $response->assertStatus(201);
        $response->assertJson($product);
    }

    public function test_api_product_invalid_store_returns_error()
    {
        $product = [
            'name' => '',
            'price' => 123
        ];
        $response = $this->postJson('/api/products', $product);

        $response->assertStatus(422);
    }

    public function test_unauthenticated_user_cannot_access_products_page()
    {
        $response = $this->get('/products');

        $response->assertRedirect('login');
    }



    //

    private function createUser(bool $isAdmin = false): User
    {
        return User::factory()->create([
            'is_admin' => $isAdmin,
        ]);
    }

    //private function createUser(): User
    //{
    //    return User::factory()->create();
    //}


}
