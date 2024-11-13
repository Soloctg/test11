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
