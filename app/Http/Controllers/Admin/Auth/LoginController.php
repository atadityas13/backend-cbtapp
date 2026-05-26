<?php
 
namespace App\Http\Controllers\Admin\Auth;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
 
class LoginController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }
 
    /**
     * Handle the multi-user login request
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
 
        // Attempt login using either username OR email (highly convenient!)
        $loginField = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $authAttempt = [
            $loginField => $credentials['username'],
            'password'  => $credentials['password']
        ];
 
        if (Auth::attempt($authAttempt, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'));
        }
 
        throw ValidationException::withMessages([
            'username' => [trans('auth.failed')],
        ]);
    }
 
    /**
     * Handle the admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
 
        $request->session()->invalidate();
        $request->session()->regenerateToken();
 
        return redirect()->route('login');
    }
}
