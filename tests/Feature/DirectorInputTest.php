<?php

namespace Tests\Feature;

use App\Enums\DirectorInputStatus;
use App\Enums\DirectorInputType;
use App\Enums\PriorityLevel;
use App\Enums\UserRole;
use App\Models\DirectorInput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorInputTest extends TestCase
{
    use RefreshDatabase;

    public function test_director_can_create_direction_and_tim_can_acknowledge_start_complete(): void
    {
        $director = User::factory()->create(['role' => UserRole::DIRECTOR->value, 'is_active' => true]);
        $tim = User::factory()->create(['role' => UserRole::TIM->value, 'is_active' => true]);

        // 1. Director creates direction
        $responseCreate = $this->actingAs($director)->post('/director-review', [
            'topic' => 'Tes Diskon Sepatu',
            'input_type' => DirectorInputType::STRATEGIC_DIRECTION->value,
            'direction_text' => 'Berikan diskon 5%',
            'assigned_to' => $tim->id,
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'priority' => PriorityLevel::HIGH->value,
        ]);
        $responseCreate->assertSessionHasNoErrors();

        $direction = DirectorInput::where('topic', 'Tes Diskon Sepatu')->first();
        $this->assertNotNull($direction);
        $this->assertEquals(DirectorInputStatus::OPEN, $direction->status);

        // 2. Tim acknowledges (OPEN -> ACKNOWLEDGED)
        $responseAck = $this->actingAs($tim)->patch("/my-directions/{$direction->id}/acknowledge");
        $responseAck->assertSessionHasNoErrors();
        $direction->refresh();
        $this->assertEquals(DirectorInputStatus::ACKNOWLEDGED, $direction->status);

        // 3. Tim starts (ACKNOWLEDGED -> IN_PROGRESS)
        $responseStart = $this->actingAs($tim)->patch("/my-directions/{$direction->id}/start");
        $responseStart->assertSessionHasNoErrors();
        $direction->refresh();
        $this->assertEquals(DirectorInputStatus::IN_PROGRESS, $direction->status);

        // 4. Tim completes with completion_note (IN_PROGRESS -> CLOSED)
        $responseComplete = $this->actingAs($tim)->patch("/my-directions/{$direction->id}/complete", [
            'completion_note' => 'Diskon 5% disetujui customer',
        ]);
        $responseComplete->assertSessionHasNoErrors();
        $direction->refresh();
        $this->assertEquals(DirectorInputStatus::CLOSED, $direction->status);
        $this->assertEquals('Diskon 5% disetujui customer', $direction->completion_note);
    }
}
