<?php
use App\Models\Course;
use Illuminate\Support\Collection;
use App\Models\ResultSetting;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

if (!function_exists('active_exist')) {
    function active_exist()
    {
        $c = Course::where('is_active', true)->first();
        return $c ? $c->id : null;
    }
}
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
     * Calculate the result *name* based on raw score clamped to 0–100.
     *
     * @param  int         $baseScore  Starting score (usually 100)
     * @param  Collection  $records    Collection of mistake records (each one counts once)
     * @param  Collection  $values     Map of mistake_id → penalty value
     * @param  string      $type       'recitation' or 'sabr'
     * @return string                 One of your ResultSetting::name
     *
     * @throws ValidationException    if computed raw score ever falls outside 0–100
     */
    function assessmentResultName(
        int $baseScore,
        Collection $records,
        Collection $values,
        string $type
    ): string {
        Log::debug("'calculating Sabr Result name , recordes :'.{$records}");
        Log::debug("'calculating Sabr Result name , values :'.{$values}");

        $totalPenalty = $records->sum(fn($r) => $values->get($r->mistake_id, 0));

        Log::debug("'calculating Sabr Result name , penalty :'.{$totalPenalty}");
        $raw = $baseScore - $totalPenalty;
        Log::debug("'calculating Sabr Result name , raw :'.{$raw}");
        if ($raw > 100) {
            throw ValidationException::withMessages([
                'mistakes' => [
                    'نتيجة ' . ($type === 'recitation' ? 'التسميع' : 'السبر') .
                    'لا يمكن أن تكون أكثر من 100.'
                ]
            ]);
        }


        $setting = ResultSetting::where('type', $type)
            ->where('min_res', '<=', $raw)
            ->where('max_res', '>=', $raw)
            ->first();

        Log::debug("'calculating Sabr Result name , setting :'.{$setting->name}");
        return $setting ? $setting->name : 'error';
    }
}

if (!function_exists('assessmentRawScore')) {
    /**
     * Compute raw score (0–100) based on one record = one penalty.
     *
     * @param  Model  $model  Recitation or Sabr model
     * @return int
     */
    function assessmentRawScore(Model $model): int
    {
        $base = 100;

        $type = $model instanceof \App\Models\Recitation ? 'recitation' : 'sabr';

        // penalty values per mistake ID for this level
        $penalties = $model
            ->level
            ->mistakes()
            ->where('mistakes.type', $type)
            ->pluck('level_mistakes.value', 'mistakes.id');

        // each record counts as one occurrence
        $records = $model->mistakesRecords()->get();

        $totalPenalty = $records->sum(fn($r) => $penalties->get($r->mistake_id, 0));

        $raw = $base - $totalPenalty;

        return (int) min(100, $raw);
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
