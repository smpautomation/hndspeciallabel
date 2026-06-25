<?php

namespace App\Http\Controllers;

use App\Models\HNDModels;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function index(){
        return Inertia::render('Admin', [
            'models'    => HNDModels::select('id', 'model_name', 'fixed_value')->get(),
            'flash'     => session()->only(['error', 'success']),
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if ($request->password !== env('ADMIN_PASSWORD')) {
            return back()->withErrors([
                'password' => 'Incorrect password. Please try again.',
            ]);
        }

        session(['admin_verified' => true]);

        return back();
    }


    public function store(Request $request){
        try{
            $validated = $request->validate([
                'model_name' => 'required|string|max:9|min:9',
                'fixed_value' => 'required|string|max:5|min:5'
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            return to_route('admin')->with('error', 'Validation Error: ' . $e->getMessage());
        }

        if(!str_contains($validated['model_name'], 'HND-')){
            return to_route('admin')->with('error', 'Validation Error: ' . "Make sure that the model name have a '-' (e.g HND-0000G)");
        }


        HNDModels::create([
            'model_name' => $validated['model_name'],
            'fixed_value' => $validated['fixed_value']
        ]);

        return to_route('admin')->with('success', 'Saved Successfully');
    }

    public function destroy($id){
        HNDModels::findOrFail($id)->delete();
        return to_route('admin')->with('success', 'Deleted Successfully');
    }
}
