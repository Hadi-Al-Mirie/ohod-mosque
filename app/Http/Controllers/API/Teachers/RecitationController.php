<?php

namespace App\Http\Controllers\API\Teachers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Recitation;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Mistake;
use App\Models\ResultSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MistakesRecorde;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
class RecitationController extends Controller
{
    /**
     * GET /api/teacher/recitation/check
     *
     * Query Parameters:
     * - student_id (int, required)
     * - lessonType (string: oneTest|multiTest, required)
     * - page (int)                // when lessonType == oneTest
     * - pages (array<int>)        // when lessonType == multiTest
     */

    public function check(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => ['required', 'string', 'exists:students,qr_token'],
                'page' => ['required', 'integer', 'min:1', 'max:604'],
            ]);
            $user = Auth::user();
            $student = Student::where('qr_token', $data['student_id'])->firstOrFail();
            if ($user->role_id === 2) {
                $teacher = $user->teacher;
                if (!$teacher || $student->circle_id !== $teacher->circle_id) {
                    return response()->json([
                        'message' => 'لا يمكنك تسجيل هذا التسميع لطالب خارج حلقتك.'
                    ], 403);
                }
            }
            if ($user->role_id === 3) {
                $helper = $user->helperTeacher;
                if (!$helper || !$helper->permissions->contains('id', 1)) {
                    throw new AccessDeniedHttpException('ليس لديك صلاحية تنفيذ هذا الإجراء.');
                }
            }
            $activeCourse = Course::where('is_active', true)->first();
            $currentCId = $activeCourse?->id;
            // collect all final recitations
            $allRecs = Recitation::where('student_id', $student->id)
                ->where('is_final', true)
                ->get(['id', 'page', 'course_id']);

            $p = $data['page'];

            $result = [];
            // all recitations on this page
            $matches = $allRecs->where('page', $p);

            $hasCurrent = $matches->contains(fn($r) => $r->course_id === $currentCId);
            $hasPrevious = $matches->contains(fn($r) => $r->course_id !== $currentCId);

            if ($hasCurrent) {
                $result[] = [
                    'page' => $p,
                    'status' => 'cannot',
                    'reason' => 'already_in_current',
                ];
            } elseif ($hasPrevious) {
                $result[] = [
                    'page' => $p,
                    'status' => 'cannot',
                    'reason' => 'already_in_previous',
                ];
            } else {
                // find any recitation record anyway (could happen if is_final was false previously)
                $pastRecs = Recitation::where('student_id', $student->id)
                    ->where('page', $p)
                    ->orderBy('created_at', 'desc')
                    ->with(['mistakesRecords.mistake'])
                    ->get();

                $recs = $pastRecs->map(fn($rec) => [
                    'recitation_id' => $rec->id,
                    'course_id' => $rec->course_id,
                    'is_final' => (bool) $rec->is_final,
                    'created_at' => $rec->created_at->toDateTimeString(),
                    'mistakes' => $rec->mistakesRecords->map(fn($mr) => [
                        'id' => $mr->id,
                        'mistake_id' => $mr->mistake_id,
                        'mistake' => $mr->mistake->name,
                        'page_number' => $mr->page_number,
                        'line_number' => $mr->line_number,
                        'word_number' => $mr->word_number,
                    ]),
                ]);

                $result[] = [
                    'status' => 'can',
                    'recitations' => $recs,
                ];
            }
            return response()->json([
                'page' => $p,
                'details' => $result,
            ], 200);
        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Invalid input.',
                'errors' => $ve->errors(),
            ], 422);

        } catch (ModelNotFoundException $mnfe) {
            return response()->json([
                'message' => 'Student not found.',
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error in recitation.check', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An unexpected error occurred.',
            ], 500);
        }

    }






    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'student_id' => ['required', 'string', 'exists:students,qr_token'],
                'page' => ['required', 'integer', 'min:1', 'max:604'],
                'mistakes' => ['required', 'array', 'min:1'],
                'mistakes.*.mistake_id' => ['required', 'integer', 'exists:mistakes,id'],
                'mistakes.*.page_number' => ['required', 'integer', 'min:1', 'max:604'],
                'mistakes.*.line_number' => ['required', 'integer', 'min:0', 'max:14'],
                'mistakes.*.word_number' => ['required', 'integer', 'min:0', 'max:25'],
            ]);

            $student = Student::where('qr_token', $data['student_id'])->firstOrFail();
            $activeCourse = Course::where('is_active', true)->firstOrFail();
            $user = Auth::user();
            $byId = Auth::user()->id;
            if ($user->role_id === 2) {
                $teacher = $user->teacher;
                if (!$teacher || $student->circle_id !== $teacher->circle_id) {
                    return response()->json([
                        'message' => 'لا يمكنك تسجيل هذا التسميع لطالب خارج حلقتك.'
                    ], 403);
                }
            }
            if ($user->role_id === 3) {
                $helper = $user->helperTeacher;
                if (!$helper || !$helper->permissions->contains('id', 1)) {
                    throw new AccessDeniedHttpException('ليس لديك صلاحية تنفيذ هذا الإجراء.');
                }
            }
            $page = $data['page'];
            // Check existing final recitation in this course
            if (
                Recitation::where([
                    ['student_id', $student->id],
                    ['page', $page],
                    ['course_id', $activeCourse->id],
                    ['is_final', true],
                ])->exists()
            ) {
                throw new AccessDeniedHttpException("Page {$page} already recited in this course.");
            }
            // Or in any previous course
            if (
                Recitation::where([
                    ['student_id', $student->id],
                    ['page', $page],
                    ['is_final', true],
                ])->where('course_id', '!=', $activeCourse->id)->exists()
            ) {
                throw new AccessDeniedHttpException("Page {$page} already recited in a previous course.");
            }

            $recitation = DB::transaction(function () use ($student, $activeCourse, $byId, $page, $data) {
                // create the new recitation
                $rec = Recitation::create([
                    'student_id' => $student->id,
                    'by_id' => $byId,
                    'course_id' => $activeCourse->id,
                    'page' => $page,
                    'level_id' => $student->level_id,
                    'is_final' => true,
                ]);

                // attach each mistake-record
                foreach ($data['mistakes'] as $m) {
                    Mistake::findOrFail($m['mistake_id']); // ensure exists

                    MistakesRecorde::create([
                        'mistake_id' => $m['mistake_id'],
                        'recitation_id' => $rec->id,
                        'sabr_id' => null,
                        'type' => 'recitation',
                        'page_number' => $m['page_number'],
                        'line_number' => $m['line_number'],
                        'word_number' => $m['word_number'],
                    ]);
                }
                $raw = assessmentRawScore($rec);
                if ($raw < 0) {
                    throw ValidationException::withMessages([
                        'recitation' => ['Result raw score cannot be negative (got ' . $raw . ').']
                    ]);
                }
                // 4) Find the ResultSetting for this raw score
                $setting = ResultSetting::where('type', 'recitation')
                    ->where('min_res', '<=', $raw)
                    ->where('max_res', '>=', $raw)
                    ->first();

                // 5) If that setting’s ID is 4, unset is_final
                if ($setting && $setting->id === 4) {
                    $rec->update(['is_final' => false]);
                }
                // update the cached points
                $student->update(['cashed_points' => $student->points]);

                return $rec;
            });

            return response()->json([
                'message' => 'Recitation saved successfully.',
                'recitation_id' => $recitation->id,
                'page' => $recitation->page,
                'result' => $recitation->calculateResult(),
            ], 201);

        } catch (ValidationException $ve) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $ve->errors(),
            ], 422);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Student or Course not found.',
            ], 404);

        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);

        } catch (\Exception $e) {
            Log::error('RecitationController@store error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An unexpected error occurred.',
            ], 500);
        }
    }
}
