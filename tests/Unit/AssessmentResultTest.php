<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use App\Models\ResultSetting;

class AssessmentResultTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed four recitation buckets covering 0–100:
        ResultSetting::insert([
            ['type' => 'recitation', 'name' => 'راسب', 'min_res' => 0, 'max_res' => 29, 'points' => 0],
            ['type' => 'recitation', 'name' => 'وسط', 'min_res' => 30, 'max_res' => 60, 'points' => 0],
            ['type' => 'recitation', 'name' => 'جيد', 'min_res' => 61, 'max_res' => 80, 'points' => 0],
            ['type' => 'recitation', 'name' => 'جيد جداً', 'min_res' => 81, 'max_res' => 90, 'points' => 0],
            ['type' => 'recitation', 'name' => 'ممتاز', 'min_res' => 91, 'max_res' => 100, 'points' => 0],
        ]);
    }

    /**
     * Replicate the standalone name-lookup logic.
     */
    private function assessmentResultName(
        int $baseScore,
        Collection $records,
        Collection $values,
        string $type
    ): string {
        $penalty = $records->sum(fn($r) => $values->get($r->mistake_id, 0) * $r->quantity);
        $score = max(0, $baseScore - $penalty);
        $setting = ResultSetting::where('type', $type)
            ->where('min_res', '<=', $score)
            ->where('max_res', '>=', $score)
            ->first();
        return $setting->name;
    }

    public function test_penalty_42_yields_good_or_middle()
    {
        $baseScore = 100;
        $values = collect([1 => 2, 2 => 5, 3 => 10]);
        $records = collect([
            (object) ['mistake_id' => 1, 'quantity' => 1], //  2
            (object) ['mistake_id' => 2, 'quantity' => 2], // 10
            (object) ['mistake_id' => 3, 'quantity' => 3], // 30
        ]);
        // total penalty = 42 → score = 58 → falls in 30–60 so "وسط"
        $this->assertSame(
            'وسط',
            $this->assessmentResultName($baseScore, $records, $values, 'recitation')
        );
    }

    public function test_over_penalty_clamps_to_zero()
    {
        $baseScore = 100;
        $values = collect([1 => 200]);
        $records = collect([(object) ['mistake_id' => 1, 'quantity' => 1]]);
        // score clamps to 0, so "راسب"
        $this->assertSame(
            'راسب',
            $this->assessmentResultName($baseScore, $records, $values, 'recitation')
        );
    }

    public function test_no_records_gives_excellent()
    {
        $baseScore = 100;
        $this->assertSame(
            'ممتاز',
            $this->assessmentResultName($baseScore, collect(), collect(), 'recitation')
        );
    }
}
