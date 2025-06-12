<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\HelperTeacher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Permission;

class HelperTeacherController extends Controller
{
    /**
     * Display a listing of the resource.
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

            $helper_teachers = HelperTeacher::with('user')
                ->whereHas('user', function ($query) use ($request) {
                    $query->where('role_id', 3);
                    if ($v = $request->search_value) {
                        $query->where('name', 'like', "%{$v}%");
                    }
                })
                ->orderByDesc('created_at')
                ->paginate(10)
                ->withQueryString();
            $hid = $helper_teachers->pluck('id');
            // dd($helper_teachers);
            return view('dashboard.helperteachers.index', compact('helper_teachers'));
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->with('danger', 'تأكد من صحة بيانات البحث.');
        } catch (Exception $e) {
            Log::error('Error listing teachers', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', ' حدث خطأ أثناء جلب قائمة الأساتذة المساعدين.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $permissions = Permission::orderBy('name')->get();
            $current = [];
            return view('dashboard.helperteachers.add', compact('permissions', 'current'));
        } catch (Exception $e) {
            Log::error('Error opening teacher create', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج إضافة أستاذ.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'teacher_name' => 'required|string|max:255',
                'password' => 'required|string|min:8|max:255',
                'phone' => 'nullable|string|max:255',
                'permissions' => 'required|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ], [
                'teacher_name.required' => 'اسم الأستاذ مطلوب.',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            ]);
            DB::transaction(function () use ($data, &$helper_teacher) {
                $user = User::create([
                    'name' => $data['teacher_name'],
                    'password' => bcrypt($data['password']),
                    'role_id' => 3,
                ]);
                $helper_teacher = HelperTeacher::create([
                    'user_id' => $user->id,
                    'phone' => $data['phone'] ?? null,
                ]);
                $helper_teacher->permissions()->sync($data['permissions']);
            });

            return redirect()
                ->route('admin.helper-teachers.show', $helper_teacher->id)
                ->with('success', 'تم إنشاء الأستاذ المساعد بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'يرجى تصحيح الأخطاء أدناه.');
        } catch (Exception $e) {
            Log::error('Error storing teacher', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withInput()
                ->with('danger', 'حدث خطأ أثناء إنشاء الأستاذ المساعد.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(HelperTeacher $helperTeacher)
    {
        try {

            $helperTeacher->load(['user', 'permissions']);
            $password = session('password');
            $user = $helperTeacher->user;
            return view('dashboard.helperteachers.show', compact(
                'helperTeacher',
                'password',
                'user'
            ));
        } catch (Exception $e) {
            Log::error('Error showing teacher', [
                'id' => $helperTeacher->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()
                ->with('danger', 'تعذّر عرض بيانات الأستاذ.');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HelperTeacher $helperTeacher)
    {
        try {
            // load all permissions, and the ones this helper already has
            $permissions = Permission::orderBy('name')->get();
            $current = $helperTeacher->permissions->pluck('id')->all();

            return view('dashboard.helperteachers.edit', compact(
                'helperTeacher',
                'permissions',
                'current'
            ));
        } catch (Exception $e) {
            Log::error('Error opening teacher edit', ['id' => $helperTeacher->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'تعذّر فتح نموذج تعديل الأستاذ.');
        }
    }

    public function update(Request $request, HelperTeacher $helperTeacher)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8',
                'admin_password' => 'required_with:password|string',
                'permissions' => 'nullable|array',
                'permissions.*' => 'integer|exists:permissions,id',
            ];
            $messages = [
                'admin_password.required_with' => 'تأكيد كلمة مرور المشرف مطلوب عند تغيير كلمة المرور.',
            ];
            $data = $request->validate($rules, $messages);

            // if changing password, confirm admin
            if (!empty($data['password'])) {
                $admin = $request->user();
                if (!Hash::check($data['admin_password'], $admin->password)) {
                    return back()
                        ->withErrors(['admin_password' => 'كلمة المرور خاطئة.'])
                        ->with('danger', 'كلمة المرور خاطئة.')
                        ->withInput();
                }
            }

            DB::transaction(function () use ($helperTeacher, $data) {
                // update user
                $helperTeacher->user->update([
                    'name' => $data['name'],
                    'password' => $data['password']
                        ? bcrypt($data['password'])
                        : $helperTeacher->user->password,
                ]);

                // update phone
                $helperTeacher->update([
                    'phone' => $data['phone'] ?? null,
                ]);

                // sync permissions
                $helperTeacher->permissions()->sync($data['permissions'] ?? []);
            });

            return redirect()
                ->route('admin.helper-teachers.show', $helperTeacher->id)
                ->with('success', 'تم تحديث بيانات الأستاذ بنجاح.');
        } catch (ValidationException $ve) {
            return redirect()->back()
                ->withErrors($ve->errors())
                ->withInput()
                ->with('danger', 'تأكد من صحة البيانات المدخلة.');
        } catch (Exception $e) {
            Log::error('Error updating teacher', ['id' => $helperTeacher->id, 'error' => $e->getMessage()]);
            return redirect()->back()
                ->with('danger', 'حدث خطأ أثناء تحديث بيانات الأستاذ.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HelperTeacher $helperTeacher)
    {
        //
    }
}