<?php

namespace App\Http\Controllers;

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
        if ($user->hasRole('student')) {
            return redirect()->route('student.studentExams.index', ['status' => 'upcoming']);
        }

        if ($user->hasRole('lecturer')) {
            return redirect()->route('courses.index');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.users.index', ['type' => 'student']);
        }

        // fallback
        return redirect('/');
    }

    public function store()
    {
        $attributes = request()->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($attributes)) {
            session()->regenerate();
            $user = Auth::user();

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
