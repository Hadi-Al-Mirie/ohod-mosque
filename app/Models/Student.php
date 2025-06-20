<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
class Student extends Model
{
    protected $fillable = [
        'student_phone',
        'father_phone',
        'location',
        'birth',
        'class',
        'school',
        'father_name',
        'father_job',
        'mother_name',
        'mother_job',
        'user_id',
        'circle_id',
        'level_id',
        'qr_token',
        'cashed_points'
    ];
    protected $appends = ['points'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }
    public function sabrs()
    {
        return $this->hasMany(\App\Models\Sabr::class);
    }
    public function awqafs()
    {
        return $this->hasMany(Awqaf::class);
    }
    public function recitations()
    {
        return $this->hasMany(\App\Models\Recitation::class);
    }
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function getPointsAttribute(): float
    {
        return $this->calculatePoints();
    }

    /**
     * Calculate total points for a given course.
     *
     * @param  int|null  $courseId
     * @return float
     */
    public function calculatePoints(int $courseId = null): float
    {
        if ($courseId === null) {
            $courseId = course_id();
        }
        $attendanceSettings = AttendanceType::pluck('value', 'id')->all();
        $attendancePoints = $this->attendances()
            ->where('course_id', $courseId)
            ->get()
            ->sum(fn($att) => $attendanceSettings[$att->type_id] ?? 0);
        // 2) Notes points
        $notesPoints = $this->notes()
            ->where('course_id', $courseId)
            ->where('status', 'approved')
            ->get()
            ->sum(fn($n) => $n->value);

        // 3) Sabr points via subquery + join to result_settings
        $cid = $courseId;
        $studentId = $this->id;

        $sabrScoreSub = DB::table('sabrs')
            ->select('sabrs.id', DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'))
            ->leftJoin('mistakes_recordes AS mr', 'mr.sabr_id', 'sabrs.id')
            ->leftJoin('level_mistakes AS lm', function ($join) {
                $join->on('lm.mistake_id', 'mr.mistake_id')
                    ->on('lm.level_id', 'sabrs.level_id');
            })
            // ** Subquery filters **
            ->where('sabrs.course_id', $cid)
            ->where('sabrs.student_id', $studentId)
            ->where('sabrs.is_final', true)
            ->groupBy('sabrs.id');


        $sabrPoints = DB::table('sabrs')
            ->joinSub($sabrScoreSub, 'scores', 'scores.id', 'sabrs.id')
            ->leftJoin('result_settings AS rs', function ($join) {
                $join->on('rs.type', DB::raw("'sabr'"))
                    ->on('rs.min_res', '<=', 'scores.raw_score')
                    ->on('rs.max_res', '>=', 'scores.raw_score');
            })
            ->select(DB::raw('COALESCE(SUM(rs.points), 0) AS sum_points'))
            ->value('sum_points');


        $recScoreSub = DB::table('recitations')
            ->select('recitations.id', DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'))
            ->leftJoin('mistakes_recordes AS mr', 'mr.recitation_id', 'recitations.id')
            ->leftJoin('level_mistakes AS lm', function ($join) {
                $join->on('lm.mistake_id', 'mr.mistake_id')
                    ->on('lm.level_id', 'recitations.level_id');
            })
            // ** Subquery filters **
            ->where('recitations.course_id', $cid)
            ->where('recitations.student_id', $studentId)
            ->where('recitations.is_final', true)
            ->groupBy('recitations.id');

        // 4) RECITATION: map raw_score → points, then sum
        $recitationPoints = DB::table('recitations')
            ->joinSub($recScoreSub, 'scores', 'scores.id', 'recitations.id')
            ->leftJoin('result_settings AS rs', function ($join) {
                $join->on('rs.type', DB::raw("'recitation'"))
                    ->on('rs.min_res', '<=', 'scores.raw_score')
                    ->on('rs.max_res', '>=', 'scores.raw_score');
            })
            ->select(DB::raw('COALESCE(SUM(rs.points), 0) AS sum_points'))
            ->value('sum_points');


        $awqafPoints = $this->awqafs()
            ->where('course_id', $courseId)
            ->where('type', 'success')
            ->get()
            ->sum(fn($aw) => $aw->result / 2);

        $total = round($sabrPoints + $recitationPoints + $attendancePoints + $notesPoints + $awqafPoints, 2);
        return $total;
    }

    /**
     * Calculate total points for this student up to (and including) the given date.
     *
     * @param  Carbon  $cutoff
     * @return float
     */
    public function pointsUpTo(Carbon $cutoff): float
    {
        $course = Course::where('is_active', true)->first();
        if (!$course) {
            return 0;
        }
        $courseId = $course->id;

        $attendanceSettings = AttendanceType::pluck('value', 'id')->all();
        $attendancePoints = $this->attendances()
            ->where('course_id', $courseId)
            ->whereDate('attendance_date', '<=', $cutoff)
            ->get()
            ->sum(fn($att) => $attendanceSettings[$att->type_id] ?? 0);
        // Log::info("Student {$this->id} attendancePoints: {$attendancePoints}");
        $notesPoints = $this->notes()
            ->where('course_id', $courseId)
            ->where('status', 'approved')
            ->whereDate('created_at', '<=', $cutoff)
            ->get()
            ->sum(fn($n) => $n->value);

        $courseId = Course::where('is_active', true)->first()->id;
        $studentId = $this->id;

        // 1) SABR — raw scores for each final sabr up to $cutoff
        $sabrScoreSub = DB::table('sabrs')
            ->select('sabrs.id', DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'))
            ->leftJoin('mistakes_recordes AS mr', 'mr.sabr_id', 'sabrs.id')
            ->leftJoin('level_mistakes AS lm', function ($j) {
                $j->on('lm.mistake_id', 'mr.mistake_id')
                    ->on('lm.level_id', 'sabrs.level_id');
            })
            ->where('sabrs.course_id', $courseId)
            ->where('sabrs.student_id', $studentId)
            ->where('sabrs.is_final', true)
            ->whereDate('sabrs.created_at', '<=', $cutoff->toDateString())
            ->groupBy('sabrs.id');

        // 2) SABR — join to result_settings and sum points
        $sabrPoints = DB::table('sabrs')
            ->joinSub($sabrScoreSub, 'scores', 'scores.id', 'sabrs.id')
            ->leftJoin('result_settings AS rs', function ($j) {
                $j->on('rs.type', DB::raw("'sabr'"))
                    ->on('rs.min_res', '<=', 'scores.raw_score')
                    ->on('rs.max_res', '>=', 'scores.raw_score');
            })
            ->select(DB::raw('COALESCE(SUM(rs.points), 0) AS sum_points'))
            ->value('sum_points');

        // 3) RECITATION — raw scores for each final recitation up to $cutoff
        $recScoreSub = DB::table('recitations')
            ->select('recitations.id', DB::raw('100 - COALESCE(SUM(lm.value), 0) AS raw_score'))
            ->leftJoin('mistakes_recordes AS mr', 'mr.recitation_id', 'recitations.id')
            ->leftJoin('level_mistakes AS lm', function ($j) {
                $j->on('lm.mistake_id', 'mr.mistake_id')
                    ->on('lm.level_id', 'recitations.level_id');
            })
            ->where('recitations.course_id', $courseId)
            ->where('recitations.student_id', $studentId)
            ->where('recitations.is_final', true)
            ->whereDate('recitations.created_at', '<=', $cutoff->toDateString())
            ->groupBy('recitations.id');

        // 4) RECITATION — join to result_settings and sum points
        $recitationPoints = DB::table('recitations')
            ->joinSub($recScoreSub, 'scores', 'scores.id', 'recitations.id')
            ->leftJoin('result_settings AS rs', function ($j) {
                $j->on('rs.type', DB::raw("'recitation'"))
                    ->on('rs.min_res', '<=', 'scores.raw_score')
                    ->on('rs.max_res', '>=', 'scores.raw_score');
            })
            ->select(DB::raw('COALESCE(SUM(rs.points), 0) AS sum_points'))
            ->value('sum_points');

        $awqafPoints = $this->awqafs()
            ->where('course_id', $courseId)
            ->where('type', 'success')
            ->whereDate('created_at', '<=', $cutoff)
            ->get()
            ->sum(fn($aw) => $aw->result / 2);
        return round(
            $attendancePoints
            + $notesPoints
            + $sabrPoints
            + $recitationPoints
            + $awqafPoints,
            2
        );
    }

    public static function activeCourse(): ?Course
    {
        return Course::where('is_active', true)->first();
    }

    /**
     * Number of working days since the start of the active course.
     */
    public function workingDaysCount(): int
    {
        $course = self::activeCourse();
        if (!$course)
            return 0;

        $start = Carbon::parse($course->start_date)->startOfDay();
        $today = now()->startOfDay();
        $period = Carbon::parse($start)->toPeriod($today);

        $dow = json_decode($course->working_days, true) ?: [];
        return collect($period)
            ->filter(fn($date) => in_array($date->dayOfWeek, $dow))
            ->count();
    }

    /**
     * Compute “raw” 0–100 scores for recitations or sabrs.
     */
    public function rawScores(string $type): Collection
    {
        $relation = $type === 'recitation' ? $this->recitations() : $this->sabrs();
        $relationName = $type === 'recitation' ? 'recitationMistakes' : 'sabrMistakes';

        return $relation
            ->where('course_id', self::activeCourse()->id)
            ->with(['level.mistakes', $relationName])
            ->get()
            ->map(fn($item) => $item->calculateRawScore());
    }

    /**
     * Count how many scores fall into each ResultSetting bracket.
     */
    public function resultCounts(string $type): array
    {
        $settings = ResultSetting::where('type', $type)->get();
        $raws = $this->rawScores($type);

        return $settings->mapWithKeys(fn($s) => [
            $s->name => $raws->filter(fn($r) => $r >= $s->min_res && $r <= $s->max_res)->count()
        ])->toArray();
    }

    /**
     * Attendance statistics: total, present, tardy, absent, justified, percentage.
     */
    public function attendanceStats(): array
    {
        $course = self::activeCourse();
        $records = $this->attendances()->where('course_id', $course->id)->get();
        $types = AttendanceType::pluck('value', 'id')->all();

        $total = $records->count();
        $byType = $records
            ->groupBy('type_id')
            ->map(fn(Collection $group) => $group->count())
            ->toArray();

        return AttendanceType::all()->mapWithKeys(function ($t) use ($byType, $total) {
            $count = $byType[$t->id] ?? 0;
            return [
                $t->name => [
                    'count' => $count,
                    'ratio' => $total ? round(($count / $total) * 100, 2) : 0
                ]
            ];
        })->toArray();
    }

    /**
     * Notes stats: total count, positive/negative counts and sums.
     */
    public function notesStats(): array
    {
        $course = self::activeCourse();
        $notes = $this->notes()
            ->where('course_id', $course->id)
            ->where('status', 'approved')
            ->get();

        $total = $notes->count();

        return [
            'total' => $total,
            'positive' => [
                'count' => $notes->where('type', 'positive')->count(),
                'sum' => $notes->where('type', 'positive')->sum('value')
            ],
            'negative' => [
                'count' => $notes->where('type', 'negative')->count(),
                'sum' => $notes->where('type', 'negative')->sum('value')
            ],
            'points' => $notes->sum('value'),
        ];
    }

    /**
     * Rank in circle or in mosque, optionally at a past cutoff.
     */
    public function rankInCircle(Carbon $upto = null): int
    {
        $circle = $this->circle;
        if (!$circle) {
            return 0;
        }

        // Sort the students collection by descending points,
        // taking into account $upto if given.
        $ordered = $circle->students
            ->sortByDesc(fn($s) => $upto ? $s->pointsUpTo($upto) : $s->points)
            ->pluck('id')         // now a list of [highestStudentId, nextStudentId, …]
            ->values();           // reindex 0…N-1

        // Find my ID in that ordered list, +1 for “rank” rather than zero-based index
        return $ordered->search($this->id) + 1;
    }

    public function rankInMosque(Carbon $upto = null): int
    {
        $all = Student::all();
        $ordered = $all
            ->sortByDesc(fn($s) => $upto ? $s->pointsUpTo($upto) : $s->points)
            ->pluck('id')
            ->values();

        return $ordered->search($this->id) + 1;
    }


    /**
     * Points gained between two dates (default: since $from until now).
     */
    public function pointsDelta(Carbon $from, Carbon $to = null): float
    {
        $to = $to ?: now();
        return $this->pointsUpTo($to) - $this->pointsUpTo($from);
    }


    /**
     * Sum up the points for all recitations in a given course.
     * @return float
     */
    public function recitationPoints(): float
    {
        // fetch the result_settings for recitation once
        $settings = ResultSetting::where('type', 'recitation')->get();

        return $this->rawScores('recitation') // assume rawScores can accept course filter
            ->filter()                                 // drop null or missing
            ->sum(function (int $raw) use ($settings) {
                return optional(
                    $settings->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res)
                )->points ?? 0;
            });
    }

    /**
     * Sum up the points for all sabrs in a given course.
     * @return float
     */
    public function sabrPoints(): float
    {
        $settings = ResultSetting::where('type', 'sabr')->get();
        return $this->rawScores('sabr')
            ->filter()
            ->sum(function (int $raw) use ($settings) {
                return optional(
                    $settings->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res)
                )->points ?? 0;
            });
    }

    /**
     * Build the recitation‑history rows for this student.
     *
     * @return array  [ ['page'=>1,'recited'=>bool,'result'=>string|null], … ]
     *
     * @throws \Exception on any unexpected error
     */
    public function recitationHistoryRows(): array
    {
        // 1) Which course is active?
        $activeCourse = self::activeCourse();
        if (!$activeCourse) {
            throw new \Exception("No active course found");
        }

        // 2) Load all this student's recitations
        $allRecs = $this->recitations()->get();

        // 3) Split into “active” (current course) vs previous
        $activeByPage = $allRecs
            ->where('course_id', $activeCourse->id)
            ->where('is_final', true)
            ->groupBy('page');

        $prevByPage = $allRecs
            ->where('course_id', '!=', $activeCourse->id)
            ->where('is_final', true)
            ->groupBy('page');

        // 4) What are the result‑settings for recitation?
        $settings = ResultSetting::where('type', 'recitation')
            ->orderBy('id')
            ->get();

        // 5) Build one “row” per page
        $rows = [];
        for ($page = 1; $page <= 604; $page++) {
            $recited = false;
            $displayResult = null;

            // 5a) Check current course
            if (isset($activeByPage[$page])) {
                $best = null;
                foreach ($activeByPage[$page] as $rec) {
                    $raw = assessmentRawScore($rec);
                    $setting = $settings
                        ->first(fn($s) => $raw >= $s->min_res && $raw <= $s->max_res);

                    if ($setting && (is_null($best) || $setting->id < $best->id)) {
                        $best = $setting;
                    }
                }
                if ($best) {
                    $recited = true;
                    $displayResult = $best->name;
                }

                // 5b) Otherwise check previous courses (just mark “done”)
            } elseif (isset($prevByPage[$page])) {
                foreach ($prevByPage[$page] as $rec) {
                    // we don’t need the name, just mark recited
                    $recited = true;
                    break;
                }
            }

            $rows[] = [
                'page' => $page,
                'recited' => $recited,
                'result' => $displayResult,
            ];
        }

        return $rows;
    }
}
