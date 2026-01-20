<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;

class MedicineApiController extends Controller
{
    public function search(Request $request)
    {
        $term = $request->get('q');
        
        $query = Medicine::query()
            ->where('is_active', true)
            ->select('id', 'name', 'generic_name', 'form', 'strength');

        if ($term) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('generic_name', 'like', "%{$term}%");
            });
        }

        $medicines = $query->limit(20)->get()->map(function($med) {
            return [
                'id' => $med->id,
                'text' => $med->name . ($med->strength ? " ({$med->strength})" : ''),
                'generic' => $med->generic_name,
                'form' => $med->form,
                'strength' => $med->strength
            ];
        });

        return response()->json([
            'results' => $medicines
        ]);
    }
}
