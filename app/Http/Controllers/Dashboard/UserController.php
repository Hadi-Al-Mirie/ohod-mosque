<?php

namespace App\Http\Controllers\Dashboard;
use App\Models\Role;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search_type' => 'nullable|in:all_users,user_name,full_name,role,phone',
        ]);
        $query = User::query();
        $query->where('id', '!=', auth()->id());
        if ($request->filled('search_type') && $request->filled('search_value')) {
            $searchType = $request->search_type;
            $searchValue = $request->search_value;
            switch ($searchType) {
                case 'role':
                    $query->where('role_id', $searchValue);
                    break;
                case 'full_name':
                    $query->whereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchValue . '%']);
                    break;
                case 'user_name':
                case 'phone':
                    $query->where($searchType, 'like', '%' . $searchValue . '%');
                    break;
                case 'all_users':
                    break;
                default:
                    break;
            }
        }
        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('dashboard.users.index', compact('users'));
    }
    public function create()
    {
        $roles = Role::where('id', '!=', 1)->get();
        return view('dashboard.users.add', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'location' => 'required|string',
            'role_id' => 'required|exists:roles,id',
        ]);
        $userName = $this->generateUserName();
        $password = $this->generatePassword();
        User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'location' => $request->location,
            'password' => $password,
            'role_id' => $request->role_id,
            'user_name' => $userName,
            'mother_name' => "XY"
        ]);
        return view("dashboard.users.confirm", compact('userName', 'password'));
    }
    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return view('dashboard.users.show', compact('user'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    private function generateUserName()
    {
        do {
            $char = chr(rand(65, 90));
            $char2 = chr(rand(65, 90));
            $num1 = rand(1, 9);
            $num2 = rand(1, 9);
            $userName = $char . $char2 . $num1 . $num2;
        } while (User::where('user_name', $userName)->exists());
        return $userName;
    }
    private function generatePassword()
    {
        $c1 = chr(rand(97, 122));
        $c2 = chr(rand(97, 122));
        $c3 = chr(rand(97, 122));
        $c4 = chr(rand(97, 122));
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $password = $c1 . $c2 . $c3 . $c4 . $n1 . $n2 . $n3 . $n4;
        return $password;
    }
}



/*
    if ($request->filled('search_type') && $request->filled('search_value')) {
        $searchType = $request->search_type;
        $searchValue = $request->search_value;
        switch ($searchType) {
            case 'role':
                $query->where('role_id', $searchValue);
                break;
            case 'full_name':
                $query->whereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $searchValue . '%']);
                break;
            case 'user_name':
            case 'phone':
                $query->where($searchType, 'like', '%' . $searchValue . '%');
                break;
            case 'all_users':
                break;
        }
    }
if ($request->filled('search_type') && $request->filled('search_value')) {
    if ($request->search_type === 'role') {
        $roleId = $request->search_value;
        $query->where('role_id', $roleId);
    } else {
        switch ($request->search_type) {
            case 'full_name':
                $query->whereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ['%' . $request->search_value . '%']);
                break;
            case 'all_users':
                break;
            case 'user_name':
            case 'phone':
                $query->where($request->search_type, 'like', '%' . $request->search_value . '%');
                break;
                        default:
                        break;
                }
            }
        }
*/
