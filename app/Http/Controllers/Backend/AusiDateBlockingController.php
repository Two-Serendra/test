<?php
namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AusiDateBlocking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AusiDateBlockingController extends Controller
{
    public function index()
    {
        $blocks = AusiDateBlocking::orderByDesc('start_date')
            ->paginate(15);

        return view('backend.ausi.ausi-date-blocking', compact('blocks'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        // Prevent overlapping date blocks.
        if (
            $this->hasOverlap(
                $validated['start_date'],
                $validated['end_date']
            )
        ) {
            throw ValidationException::withMessages([
                'start_date' => 'This date range overlaps an existing AUSI block.',
            ]);
        }

        $validated['created_by'] = Auth::id();

        AusiDateBlocking::create($validated);

        return response()->json([
            'message' => 'AUSI date block created successfully.',
        ]);
    }





    public function destroy(AusiDateBlocking $ausiDateBlocking)
    {
        $ausiDateBlocking->delete();

        return redirect()
            ->route('admin.ausi-date-blocking.index')
            ->with('success', 'AUSI date block deleted successfully.');
    }


    private function hasOverlap(
        string $startDate,
        string $endDate,
        ?int $ignoreId = null
    ): bool {
        return AusiDateBlocking::query()
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->exists();
    }

}
