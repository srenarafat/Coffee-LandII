<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class StockLogPresetFilterTest extends TestCase
{
    use RefreshDatabase;

    private function createLog(Product $product, Carbon $date, User $user): void
    {
        $log = StockLog::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 1,
            'note' => null,
            'user_id' => $user->id,
        ]);
        $log->created_at = $date;
        $log->save();
    }

    private function seedLogs(User $user): void
    {
        $category = Category::create(['name' => 'Drinks']);
        $todayProd = Product::create(['name' => 'TodayProd', 'price' => 1, 'category_id' => $category->id, 'stock' => 0]);
        $weekProd = Product::create(['name' => 'WeekProd', 'price' => 1, 'category_id' => $category->id, 'stock' => 0]);
        $monthProd = Product::create(['name' => 'MonthProd', 'price' => 1, 'category_id' => $category->id, 'stock' => 0]);
        $prevProd = Product::create(['name' => 'PrevMonthProd', 'price' => 1, 'category_id' => $category->id, 'stock' => 0]);

        $this->createLog($todayProd, Carbon::parse('2024-04-10 09:00:00'), $user);
        $this->createLog($weekProd, Carbon::parse('2024-04-08 09:00:00'), $user);
        $this->createLog($monthProd, Carbon::parse('2024-04-02 09:00:00'), $user);
        $this->createLog($prevProd, Carbon::parse('2024-03-15 09:00:00'), $user);
    }

    public function test_today_preset_returns_only_today_logs(): void
    {
        Carbon::setTestNow('2024-04-10 12:00:00');
        $user = User::factory()->create(['role' => 'admin']);
        $this->seedLogs($user);

        $response = $this->actingAs($user)->get('/admin/stock-logs?preset=today');

        $response->assertOk();
        $response->assertSee('TodayProd');
        $response->assertDontSee('WeekProd');
        $response->assertDontSee('MonthProd');
        $response->assertDontSee('PrevMonthProd');
        Carbon::setTestNow();
    }

    public function test_this_week_preset_returns_current_week_logs(): void
    {
        Carbon::setTestNow('2024-04-10 12:00:00');
        $user = User::factory()->create(['role' => 'admin']);
        $this->seedLogs($user);

        $response = $this->actingAs($user)->get('/admin/stock-logs?preset=this_week');

        $response->assertOk();
        $response->assertSee('TodayProd');
        $response->assertSee('WeekProd');
        $response->assertDontSee('MonthProd');
        $response->assertDontSee('PrevMonthProd');
        Carbon::setTestNow();
    }

    public function test_this_month_preset_returns_current_month_logs(): void
    {
        Carbon::setTestNow('2024-04-10 12:00:00');
        $user = User::factory()->create(['role' => 'admin']);
        $this->seedLogs($user);

        $response = $this->actingAs($user)->get('/admin/stock-logs?preset=this_month');

        $response->assertOk();
        $response->assertSee('TodayProd');
        $response->assertSee('WeekProd');
        $response->assertSee('MonthProd');
        $response->assertDontSee('PrevMonthProd');
        Carbon::setTestNow();
    }
}