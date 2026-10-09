<?php

namespace Tests\Feature;

use App\Models\Orders;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Hotfix #4: the AI chat previously executed LLM-generated SQL with no
 * auth and no code-level enforcement that it was actually a single SELECT.
 * These tests lock in the fix: auth is now required, and the generated
 * query is re-checked before DB::select() ever sees it.
 */
class AIChatSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_reach_the_chat_page(): void
    {
        $this->get('/chat')->assertRedirect('/');
    }

    public function test_guest_cannot_post_to_ai_chat(): void
    {
        $this->postJson('/ai-chat', ['message' => 'hi', 'model' => 'x'])->assertRedirect('/');
    }

    public function test_order_admin_can_reach_the_chat_page(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);

        $this->actingAs($admin)->get('/chat')->assertOk();
    }

    /** @dataProvider unsafeGeneratedQueries */
    public function test_an_unsafe_generated_query_is_rejected_before_execution(string $maliciousSql): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [['message' => ['content' => $maliciousSql]]],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->postJson('/ai-chat', [
            'message' => 'how many orders are there',
            'model' => 'test-model',
        ]);

        $response->assertStatus(400);
        $this->assertStringContainsString('cannot be answered safely', $response->json('reply'));
    }

    public static function unsafeGeneratedQueries(): array
    {
        return [
            'not a select' => ['DROP TABLE users'],
            'multiple statements' => ['SELECT * FROM orders; DROP TABLE orders'],
            'touches password column' => ['SELECT password FROM users LIMIT 10'],
            'unknown table' => ['SELECT * FROM information_schema.tables LIMIT 10'],
        ];
    }

    public function test_a_safe_generated_query_executes_normally(): void
    {
        $admin = User::factory()->create(['role' => 'o_admin']);
        Orders::create([
            'unique_id' => uniqid(),
            'shop' => 'BC-0001',
            'total_price' => 100,
            'time_period' => 'Morning',
            'status' => 'Pending',
        ]);

        Http::fake([
            'openrouter.ai/*' => Http::sequence()
                ->push(['choices' => [['message' => ['content' => 'SELECT COUNT(*) as total FROM orders LIMIT 100']]]])
                ->push(['choices' => [['message' => ['content' => 'There are some orders.']]]]),
        ]);

        $response = $this->actingAs($admin)->postJson('/ai-chat', [
            'message' => 'how many orders are there',
            'model' => 'test-model',
        ]);

        $response->assertOk();
        $this->assertSame('There are some orders.', $response->json('reply'));
    }
}
