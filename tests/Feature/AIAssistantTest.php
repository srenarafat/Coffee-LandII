<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tests\TestCase;

class AIAssistantTest extends TestCase
{
    use RefreshDatabase;
    private function writeFallbackCsv(): void
    {
        $csv = "Date,Product,Qty,Total\n".
            "2024-04-01 08:00,Latte,1,4\n".
            "2024-04-02 09:15,Cappuccino,3,12\n".
            "2024-04-02 12:30,Espresso,2,8\n".
            "2024-04-03 14:00,Mocha,1,5\n";
        $path = Storage::disk('local')->path('fallback_sales.csv');
        file_put_contents($path, $csv);
    }

    public function test_top_products_from_fallback_data(): void
    {
        Carbon::setTestNow('2024-04-05');
        $this->writeFallbackCsv();
        Http::fake(fn() => throw new \Exception('fail'));
        Log::spy();

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)
            ->postJson('/admin/ai-assistant', ['message' => 'Top products this week']);

        $response->assertOk();
        $response->assertJson([
            'reply' => "\xF0\x9F\x93\x8A Top products this week:\n\xF0\x9F\xA5\x87 **Cappuccino** \xE2\x80\x94 \$12.00\n\xF0\x9F\xA5\x88 **Espresso** \xE2\x80\x94 \$8.00\n\xF0\x9F\xA5\x89 **Mocha** \xE2\x80\x94 \$5.00"
        ]);
    }

    public function test_slow_products_from_fallback_data(): void
    {
        Carbon::setTestNow('2024-04-05');
        $this->writeFallbackCsv();
        Http::fake(fn() => throw new \Exception('fail'));
        Log::spy();

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)
            ->postJson('/admin/ai-assistant', ['message' => 'Slow selling items']);

        $response->assertOk();
        $response->assertJson([
            'reply' => "\xF0\x9F\xA7\x8A Slow-selling products:\n\xF0\x9F\x95\x90 **Latte** \xE2\x80\x94 1 sold\n\xF0\x9F\x95\x90 **Mocha** \xE2\x80\x94 1 sold\n\xF0\x9F\x95\x90 **Espresso** \xE2\x80\x94 2 sold"
        ]);
    }

    public function test_daily_totals_simple_fallback(): void
    {
        $this->writeFallbackCsv();
        Http::fake(fn() => Http::response(['choices' => [['message' => ['content' => null]]]]));
        Log::spy();

        $user = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($user)
            ->postJson('/admin/ai-assistant', ['message' => 'Total sales today']);

        $response->assertOk();
        $response->assertJson([
            'reply' => "\xF0\x9F\x94\xA2 Ask me something like \"Total sales this week\""
        ]);
    }

    public function test_latest_sale_item_is_included_in_assistant_payload(): void
    {
        Carbon::setTestNow('2024-05-10 10:00:00');

        $captured = [];
        Http::fake(function ($request) use (&$captured) {
            $captured = $request->data();
            return Http::response(['choices' => [['message' => ['content' => 'ok']]]]);
        });

        $user = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Coffee']);
        $product = Product::create(['name' => 'Latte', 'price' => 5, 'category_id' => $category->id]);

        for ($i = 1; $i <= 101; $i++) {
            $sale = Sale::create([
                'invoice_no' => sprintf('INV%03d', $i),
                'user_id' => $user->id,
                'subtotal' => 5,
                'tax' => 0,
                'total' => 5,
                'created_at' => Carbon::now()->subMinutes(101 - $i),
            ]);

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 5,
                'total' => 5,
                'created_at' => Carbon::now()->subMinutes(101 - $i),
            ]);
        }

        $this->actingAs($user)->postJson('/admin/ai-assistant', ['message' => 'hello']);

        $content = $captured['messages'][1]['content'];
        $this->assertStringContainsString('INV101', $content);
        $this->assertGreaterThan(strpos($content, 'INV100'), strpos($content, 'INV101'));
    }
}