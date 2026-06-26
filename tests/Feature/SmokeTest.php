<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_all_main_pages_load(): void
    {
        $this->actingAs($user = $this->user());

        foreach (['/transactions', '/categories', '/categories/create', '/calendar', '/csv', '/loans'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_category_crud(): void
    {
        $this->actingAs($user = $this->user());

        $this->post('/categories', ['name' => 'Salary', 'color' => '#16a34a'])
            ->assertRedirect('/categories');

        $this->assertDatabaseHas('categories', ['name' => 'Salary', 'user_id' => $user->id]);
    }

    public function test_transaction_with_categories_and_receipt(): void
    {
        Storage::fake('public');
        $this->actingAs($user = $this->user());
        $cat = Category::factory()->for($user)->create();

        $this->post('/transactions', [
            'title'      => 'Groceries',
            'type'       => 'expense',
            'amount'     => 42.50,
            'status'     => 'pending',
            'event_date' => now()->toDateString(),
            'categories' => [$cat->id],
            // create() with an explicit mime avoids needing the GD extension in CI.
            'receipt'    => UploadedFile::fake()->create('receipt.jpg', 40, 'image/jpeg'),
        ])->assertRedirect('/transactions');

        $tx = Transaction::first();
        $this->assertNotNull($tx);
        $this->assertTrue($tx->categories->contains($cat));   // many-to-many works
        $this->assertNotNull($tx->receipt_path);              // file stored
        Storage::disk('public')->assertExists($tx->receipt_path);
    }

    public function test_note_type_allows_empty_amount(): void
    {
        $this->actingAs($this->user());

        $this->post('/transactions', [
            'title'  => 'Pay rent reminder',
            'type'   => 'note',
            'status' => 'pending',
        ])->assertRedirect('/transactions');

        $this->assertDatabaseHas('transactions', ['title' => 'Pay rent reminder', 'amount' => null]);
    }

    public function test_json_api_endpoints(): void
    {
        $this->actingAs($user = $this->user());
        Transaction::factory()->for($user)->create(['event_date' => now()]);

        $this->getJson('/api/transactions')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/events?month=' . now()->format('Y-m'))
            ->assertOk()->assertJsonStructure(['month', 'count', 'events']);
    }

    public function test_csv_export_and_import(): void
    {
        $this->actingAs($user = $this->user());
        Transaction::factory()->for($user)->create(['title' => 'Exported row', 'type' => 'income', 'amount' => 100]);

        $this->get('/csv/export')->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = "title,description,type,amount,status,event_date,deadline\n"
             . "Imported salary,,income,500,cleared,2026-06-01,\n"
             . "Bad row,,wrongtype,1,pending,,\n";
        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        $this->post('/csv/import', ['file' => $file])->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', ['title' => 'Imported salary', 'amount' => 500]);
        $this->assertDatabaseMissing('transactions', ['title' => 'Bad row']);
    }

    public function test_loan_calculator_math(): void
    {
        $this->actingAs($this->user());

        // $10,000 at 12% for 12 months -> ~$888.49/month.
        $this->post('/loans', [
            'title' => 'Car Loan', 'principal' => 10000, 'annual_rate' => 12, 'months' => 12,
        ])->assertRedirect('/loans');

        $loan = \App\Models\Loan::first();
        $this->assertEqualsWithDelta(888.49, (float) $loan->monthly_payment, 0.5);
        $this->assertGreaterThan(10000, (float) $loan->total_payable);
    }

    public function test_ownership_middleware_blocks_other_users(): void
    {
        $owner = $this->user();
        $intruder = $this->user();
        $tx = Transaction::factory()->for($owner)->create();

        $this->actingAs($intruder)->get("/transactions/{$tx->id}/edit")->assertForbidden();
    }
}
