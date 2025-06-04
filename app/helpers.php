<?php
use App\Models\Course;
use Illuminate\Support\Collection;
use App\Models\ResultSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
if (!function_exists('course_id')) {
    function course_id()
    {
        $c = Course::where('is_active', true)->first();
        $ci = $c->id;
        return $ci;
    }
}
if (!function_exists('assessmentResultName')) {
    /**
     * Calculate the result *name* or throw if raw score out of range.
     *
     * @param  int         $baseScore
     * @param  Collection  $records    Collection of objects with mistake_id & quantity
     * @param  Collection  $values     map mistake_id → penalty value
     * @param  string      $type       'recitation' or 'sabr'
     * @return string                 one of your ResultSetting::name
     *
     * @throws ValidationException    if raw score < 0 or > 100
     */
    function assessmentResultName(
        int $baseScore,
        Collection $records,
        Collection $values,
        string $type
    ): string {
        $penalty = $records->sum(fn($r) => ($values->get($r->mistake_id, 0) * $r->quantity));
        $raw = $baseScore - $penalty;
        if ($raw < 0 || $raw > 100) {
            throw ValidationException::withMessages([
                'mistakes' => ['نتيجة ' . ($type === 'recitation' ? 'التسميع' : 'السبر') . ' لا يمكن أن تكون خارج 0–100.']
            ]);
        }
        $setting = ResultSetting::where('type', $type)
            ->where('min_res', '<=', $raw)
            ->where('max_res', '>=', $raw)
            ->first();
        return $setting
            ? $setting->name
            : 'error';
    }
}
if (!function_exists('assessmentRawScore')) {
    function assessmentRawScore(Model $model): int
    {
        // base score
        $base = 100;

        // get penalty values for this model’s level & type
        $type = $model instanceof \App\Models\Recitation ? 'recitation' : 'sabr';
        /** @var Collection<int,int> $penalties */
        $penalties = $model
            ->level
            ->mistakes()
            ->where('mistakes.type', $type)
            ->pluck('level_mistakes.value', 'mistakes.id');

        // sum up actual mistake records on this model
        $records = $model->mistakesRecords()->get();
        $totalPenalty = $records->sum(fn($r) => ($penalties->get($r->mistake_id, 0) * $r->quantity));

        $raw = $base - $totalPenalty;
        // clamp just in case
        return (int) max(0, min(100, $raw));
    }
}

if (!function_exists('percent_change')) {
    /**
     *
     * @param  float  $current
     * @param  float  $previous
     * @return float   Percent change, rounded to one decimal.
     */
    function percent_change(float $current, float $previous): float
    {
        if ($previous === 0.0 && $current === 0.0) {
            return 0.0;
        }

        $denominator = abs($previous) > 0.0
            ? abs($previous)
            : abs($current);

        $pct = ($current - $previous) / $denominator * 100;

        return round($pct, 1);
    }
}
