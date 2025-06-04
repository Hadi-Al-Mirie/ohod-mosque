<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Circle;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class TeacherController extends Controller
{
    /**
     * List all teachers.
     */
    public function index(Request $request)
    {
        try {
            $request->validate([
                'search_value' => 'nullable|string|max:255|min:1',
            ], [
                'search_value.min' => 'يجب أن يحوي البحث حرفاً واحداً على الأقل.',
                'search_value.max' => 'عبارة البحث طويلة جداً (أقصى 255 حرف).',
            ]);

            $query = User::where('role_id', 2)
                ->with(['teacher.circle:id,name']);

            if ($v = $request->search_value) {
                $query->where('name', 'like', "%{$v}%");
            }

            $teachers = $query->orderBy('created_at', 'desc')
                ->paginate(10)
                ->withQueryString();

            return view('dashboard.teachers.index', compact('teachers'));
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (Exception $e) {
            Log::error('Error listing teachers', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء جلب قائمة الأساتذة.');
        }
    }

    /**
     * Show create form.
     */
    public function create()
    {
        try {
            $circles = Circle::doesntHave('teacher')
                ->select('id', 'name')
                ->get();

            if ($circles->isEmpty()) {
                return redirect()->back()
                    ->with('danger', 'يجب عليك إنشاء حلقة أولاً.');
            }

            return view('dashboard.teachers.add', compact('circles'));
        } catch (Exception $e) {
            Log::error('Error opening teacher create', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج إضافة أستاذ.');
        }
    }

    /**
     * Store a new teacher.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'teacher_name' => 'required|string|max:255',
                'password' => 'required|string|min:8|max:255',
                'circle' => 'required|exists:circles,id',
                'phone' => 'nullable|string|max:255',
            ], [
                'teacher_name.required' => 'اسم الأستاذ مطلوب.',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
                'circle.required' => 'اختيار الحلقة مطلوب.',
                'circle.exists' => 'الحلقة المختارة غير موجودة.',
            ]);

            $circle = Circle::find($data['circle']);
            if ($circle->teacher) {
                return redirect()->back()
                    ->withInput()
                    ->with('danger', 'الحلقة المختارة لديها أستاذ بالفعل!');
            }

            DB::transaction(function () use ($data, &$teacher) {
                $user = User::create([
                    'name' => $data['teacher_name'],
                    'password' => bcrypt($data['password']),
                    'role_id' => 2,
                ]);
                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'] ?? null,
                    'circle_id' => $data['circle'],
                ]);
            });

            return redirect()
                ->route('admin.teachers.show', $teacher->id)
                ->with('success', 'تم إنشاء الأستاذ بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'يرجى تصحيح الأخطاء أدناه.');
        } catch (Exception $e) {
            Log::error('Error storing teacher', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء إنشاء الأستاذ.');
        }
    }

    /**
     * Show one teacher.
     */
    public function show(Teacher $teacher)
    {
        try {
            $password = session('password');
            $user = $teacher->user;
            return view('dashboard.teachers.show', compact('teacher', 'user', 'password'));
        } catch (Exception $e) {
            Log::error('Error showing teacher', ['id' => $teacher->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر عرض بيانات الأستاذ.');
        }
    }

    /**
     * Show edit form.
     */
    public function edit(Teacher $teacher)
    {
        try {
            $circles = Circle::select('id', 'name')->get();
            return view('dashboard.teachers.edit', compact('teacher', 'circles'));
        } catch (Exception $e) {
            Log::error('Error opening teacher edit', ['id' => $teacher->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج تعديل الأستاذ.');
        }
    }

    /**
     * Update an existing teacher.
     */
    public function update(Request $request, Teacher $teacher)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'circle' => 'nullable|exists:circles,id',
                'phone' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8',
                'admin_password' => 'required_with:password|nullable|string',
            ];
            $messages = [
                'admin_password.required_with' => 'تأكيد كلمة مرور المشرف مطلوب عند تغيير كلمة المرور.',
            ];
            $data = $request->validate($rules, $messages);

            if (!empty($data['password'])) {
                $admin = $request->user();
                if (!Hash::check($data['admin_password'], $admin->password)) {
                    return back()
                        ->withErrors(['admin_password' => 'كلمة المرور خاطئة.'])
                        ->with('danger', 'كلمة المرور خاطئة.')
                        ->withInput();
                }
            }

            DB::transaction(function () use ($teacher, $data) {
                $teacher->user->update([
                    'name' => $data['name'],
                    'password' => $data['password']
                        ? $data['password']
                        : $teacher->user->password,
                ]);
                $teacher->update([
                    'phone' => $data['phone'] ?? null,
                    'circle_id' => $data['circle'],
                ]);
            });

            return redirect()
                ->route('admin.teachers.show', $teacher->id)
                ->with('success', 'تم تحديث بيانات الأستاذ بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error updating teacher', ['id' => $teacher->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء تحديث بيانات الأستاذ.');
        }
    }

    /**
     * destroy.
     */
    public function destroy(Teacher $teacher)
    {
        //
    }
}