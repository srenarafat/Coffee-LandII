<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Category;
use App\Models\SystemLog;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopMultiTenantTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_admin_cannot_see_other_shop_products(): void
    {
        $shop1 = Shop::create(['name'=>'S1']);
        $shop2 = Shop::create(['name'=>'S2']);

        $admin1 = User::factory()->create(['role'=>'admin','shop_id'=>$shop1->id]);
        $admin2 = User::factory()->create(['role'=>'admin','shop_id'=>$shop2->id]);

        $cat = Category::create(['name'=>'C']);
        Product::create(['name'=>'P1','price'=>1,'category_id'=>$cat->id,'shop_id'=>$shop1->id]);
        Product::create(['name'=>'P2','price'=>1,'category_id'=>$cat->id,'shop_id'=>$shop2->id]);

        $this->actingAs($admin1)->get('/admin/products')
             ->assertSee('P1')
             ->assertDontSee('P2');
    }

    public function test_system_log_created_on_user_creation(): void
    {
        $shop = Shop::create(['name'=>'S1']);
        $admin = User::factory()->create(['role'=>'admin','shop_id'=>$shop->id]);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'C1',
            'email' => 'c1@example.com',
            'role' => 'cashier',
            'password' => 'secret',
            'password_confirmation'=>'secret'
        ])->assertRedirect('/admin/users');

        $this->assertDatabaseHas('system_logs', ['action'=>'user_created']);
    }
}