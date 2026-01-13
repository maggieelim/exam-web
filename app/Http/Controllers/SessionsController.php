<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function create()
    {
        return view('session.login-session');
    }

    private function redirectToRoleHome($user)
    {
        return redirect()->route('dashboard');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($attributes)) {
            session()->regenerate();
            $user = Auth::user();
            $student = Student::where('user_id', $user->id)->first();
            $type = $student->type;
            if ($user->hasAnyRole('admin', 'lecturer', 'koordinator')) {
                session(['context' => 'pssk']);
            } elseif ($user->hasRole('student')) {
                session(['context' => strtolower($type)]);
            }
            return $this->redirectToRoleHome($user)->with(['success' => 'Welcome back!']);
        } else {
            return back()->withErrors(['email' => 'Email or password invalid.']);
        }
    }

    public function destroy()
    {
        Auth::logout();

        return redirect('/login')->with(['success' => 'You\'ve been logged out.']);
    }
}
