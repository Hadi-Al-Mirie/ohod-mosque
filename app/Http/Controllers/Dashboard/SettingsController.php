<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\LevelMistake;
use App\Models\Mistake;
use App\Models\AttendanceType;
use App\Models\ResultSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Recitation;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SettingsController extends Controller
{
    public function edit()
    {
        try {
            $resultSet = ResultSetting::all();
            $levels = Level::all();
            $mistakes = Mistake::all();
            $attendanceTypes = AttendanceType::all();

            return view('dashboard.settings.edit', compact(
                'resultSet',
                'levels',
                'mistakes',
                'attendanceTypes'
            ));
        } catch (\Exception $e) {
            Log::error('error', ['error' => $e->getMessage()]);
            return back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء جلب الإعدادات.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'result_settings' => ['required', 'array', 'size:8'],
            'result_settings.*.min_res' => ['required', 'integer', 'between:0,100'],
            'result_settings.*.max_res' => ['required', 'integer', 'between:0,100', 'gte:result_settings.*.min_res'],
            'result_settings.*.points' => ['required', 'numeric', 'min:0', 'max:100'],
            'level_mistakes' => ['required', 'array'],
            'level_mistakes.*' => ['required', 'array'],
            'level_mistakes.*.*' => ['required', 'integer', 'min:0'],
            'attendance_types' => ['required', 'array', 'size:4'],
            'attendance_types.*' => ['required', 'integer'],
        ]);
        $validator->after(function ($v) use ($request) {
            $submitted = $request->input('result_settings', []);
            $idsInDb = ResultSetting::pluck('id')->sort()->values()->all();
            $keys = array_map('intval', array_keys($submitted));
            if (array_diff($keys, $idsInDb) || array_diff($idsInDb, $keys)) {
                $v->errors()->add('result_settings', 'قائمة result_settings لا تطابق السجلات في قاعدة البيانات.');
            }
            $counts = ['recitation' => 0, 'sabr' => 0];
            $rangesByType = ['recitation' => [], 'sabr' => []];
            foreach ($submitted as $id => $vals) {
                $setting = ResultSetting::find($id);
                if (!$setting) {
                    $v->errors()->add("result_settings.{$id}", "الإعداد {$id} غير صالح.");
                    continue;
                }
                $type = $setting->type;
                $counts[$type]++;
                $rangesByType[$type][] = [
                    (int) $vals['min_res'],
                    (int) $vals['max_res']
                ];
            }
            foreach (['recitation', 'sabr'] as $type) {
                if ($counts[$type] !== 4) {
                    $v->errors()->add(
                        'result_settings',
                        "يجب أن يكون لديك 4 إعدادات للـ {$type}، ولم يتم العثور على {$counts[$type]}."
                    );
                }
            }
            foreach (['recitation', 'sabr'] as $type) {
                $ranges = $rangesByType[$type];
                usort($ranges, function ($a, $b) {
                    return $a[0] <=> $b[0];
                });
                $cursor = 0;
                foreach ($ranges as [$min, $max]) {
                    if ($min !== $cursor) {
                        $v->errors()->add(
                            'result_settings',
                            "توجد ثغرة أو تداخل في نطاقات الـ {$type} (بدأ عند {$cursor}, وجدنا {$min})."
                        );
                        break;
                    }
                    $cursor = $max + 1;
                }
                if ($cursor !== 101) {
                    $v->errors()->add(
                        'result_settings',
                        "نطاقات الـ {$type} لا تكمل حتى 100 (انتهت عند " . ($cursor - 1) . ")."
                    );
                }
            }
            $levelsInDb = Level::pluck('id')->all();
            $mistakesInDb = Mistake::pluck('id')->all();
            foreach ($request->input('level_mistakes', []) as $lvlId => $mists) {
                if (!in_array((int) $lvlId, $levelsInDb, true)) {
                    $v->errors()->add("level_mistakes.{$lvlId}", "المستوى {$lvlId} غير موجود.");
                }
                foreach ($mists as $mistId => $val) {
                    if (!in_array((int) $mistId, $mistakesInDb, true)) {
                        $v->errors()->add(
                            "level_mistakes.{$lvlId}.{$mistId}",
                            "الخطأ {$mistId} غير موجود."
                        );
                    }
                }
            }
        });
        $data = $validator->validate();
        DB::transaction(function () use ($data) {
            foreach ($data['result_settings'] as $id => $attrs) {
                ResultSetting::findOrFail($id)->update([
                    'min_res' => $attrs['min_res'],
                    'max_res' => $attrs['max_res'],
                    'points' => $attrs['points'],
                ]);
            }
            foreach ($data['level_mistakes'] as $lvlId => $mists) {
                foreach ($mists as $mistId => $value) {
                    LevelMistake::updateOrCreate(
                        ['level_id' => $lvlId, 'mistake_id' => $mistId],
                        ['value' => $value]
                    );
                }
            }
            foreach ($data['attendance_types'] as $typeId => $val) {
                $atty = AttendanceType::findOrFail($typeId);
                $atty->value = $val;
                $atty->save();
                Log::error('Error deleting recitation', ['id' => $atty->id, 'value' => $atty->value]);
            }
            $activeCourse = Course::where('is_active', true)->first();
            if ($activeCourse) {
                $recitations = Recitation::where('course_id', $activeCourse->id)->get();
                foreach ($recitations as $rec) {
                    $raw = assessmentRawScore($rec);
                    $setting = ResultSetting::where('type', 'recitation')
                        ->where('min_res', '<=', $raw)
                        ->where('max_res', '>=', $raw)
                        ->first();
                    if ($setting && $setting->id === 4 && $rec->is_final) {
                        $rec->update(['is_final' => false]);
                    } elseif ($setting && $setting->id != 4 && $rec->is_final == false) {
                        $rec->update(['is_final' => true]);
                    }
                }
            }
        });
        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'تم حفظ الإعدادات بنجاح.');
    }
}
