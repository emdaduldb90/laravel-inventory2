<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\TenantSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CompanyRegisterController extends Controller
{
    public function create()
    {
        return view('auth.signup');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'company_name' => ['required','string','max:255'],
            'email'        => ['required','email','max:255','unique:users,email'],
            'name'         => ['required','string','max:255'],
            'password'     => ['required','string','min:6','max:100','confirmed'],
        ]);

        // Unique slug generator
        $base = Str::slug($data['company_name']);
        $slug = $base; $i = 1;
        while (Company::where('slug',$slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        // Company create
        $company = Company::create([
            'name' => $data['company_name'],
            'slug' => $slug,
        ]);

        // User create (auto-hash via 'password' => 'hashed' cast)
        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
            'company_id' => $company->id,
            'is_owner'   => true,
        ]);

        // Ensure role exists & assign Owner
        Role::findOrCreate('Owner');
        $user->syncRoles(['Owner']);

        // Bootstrap tenant defaults (warehouse, parties, payment methods…)
        app(TenantSetupService::class)->bootstrap($company->id);

        Auth::login($user);

        return redirect('/admin')->with('status', 'Welcome! Your company is ready.');
    }
}
