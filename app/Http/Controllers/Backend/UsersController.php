<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class UsersController extends Controller
{
    public function showUser()
    {
        $users = User::paginate(10);
        return view('backend.admin-user-creation', compact('users'));
    }

    public function searchUser(Request $request)
    {
        $searchUser = $request->input('searchUsers');

        $users = User::when($searchUser, function ($query, $searchUser) {
            return $query->where('name', 'LIKE', "%{$searchUser}%");
        })
            ->paginate(10);

        $users->appends(['searchUser' => $searchUser]);

        return view('backend.admin-user-creation', compact('users', 'searchUser'));
    }
    public function storeUser(Request $request)
    {
        Log::info('User Creation Attempt:', [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'ip' => $request->ip(),
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:0,1,2,3,4',
            ]);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role,
            ]);
            Log::info('User Created Successfully:', ['user_id' => $user->id, 'email' => $user->email]);

            return redirect()->back()->with('success', 'User Created Successfully');
        } catch (\Exception $e) {
            Log::error('User Creation Failed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->withErrors(['error' => 'Failed to create user.']);
        }
    }

    public function getUpdatedUserTable()
    {
        $users = User::paginate(10);
        return response()->json([
            'data' => $users->items(),
            'links' => (string) $users->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchUser($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if (Auth::user()->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $emailChanged = $request->email !== $user->email;
        $passwordChanged = $request->filled('password');

        $user->name = $request->name;
        $user->email = $request->email;

        if ($passwordChanged) {
            $user->password = Hash::make($request->password);
        }

        $user->save();
        if ($emailChanged || $passwordChanged) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return response()->json(['success' => 'User updated successfully!']);
    }

    public function deleteUser(Request $request)
    {
        $userId = $request->input('user_id');
        try {
            $user = User::findOrFail($userId);
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User deletion failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
