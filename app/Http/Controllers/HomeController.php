<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Home;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Home::query();

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        $homes = $query->get();

        return response()->json($homes);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'price' => 'required|numeric',
            'images' => 'required|array',
            'images.*' => 'required|string', // Expect base64 encoded image strings
        ]);

        // Ensure the user is authenticated
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated['contact_number'] = $user->phone; 
        $validated['user_id'] = $user->id;

        // Decode and store images from base64
        $imagePaths = [];
        foreach ($request->input('images') as $base64Image) {
            // Extract image data (assuming base64 data is in the format "data:image/jpeg;base64,...")
            $imageData = explode(',', $base64Image)[1]; // Get the part after the comma (base64 string)
            $imageName = uniqid() . '.jpg'; // Generate a unique name for the image

            // Decode the base64 string
            $imageDecoded = base64_decode($imageData);

            // Store the image in the 'public/homes' directory
            $imagePath = 'homes/' . $imageName;
            Storage::disk('public')->put($imagePath, $imageDecoded);

            // Add the path to the imagePaths array
            $imagePaths[] = $imagePath;
        }

        $validated['images'] = $imagePaths;

        // Create and return the Home record
        $home = Home::create($validated);

        return response()->json($home, 201);
    }

    public function show(Home $home)
    {
        return $home;
    }

    public function update(Request $request, Home $home)
    {
        if (auth()->user()->id !== $home->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'images' => 'sometimes|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('homes', 'public');
            }
            $validated['images'] = $imagePaths;
        }

        $home->update($validated);

        return response()->json($home);
    }

    public function destroy(Home $home)
    {
        if (auth()->user()->id !== $home->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $home->delete();

        return response()->json(null, 204);
    }
}
