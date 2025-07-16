<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ServicesController extends Controller
{
    public function services(Request $request)
    {
        $services = Service::paginate(5);
        return view('backend.admin-services', compact('services'));
    }
    public function searchService(Request $request)
    {
        $searchService = $request->input('searchServices');

        $services = Service::when($searchService, function ($query, $searchService) {
            return $query->where('service_name', 'LIKE', "{$searchService}%");
        })->paginate(10);

        $services->appends(['searchServices' => $searchService]);
        return view('backend.admin-services', compact('services', 'searchService'));
    }


    public function newServices(Request $request)
    {
        $filename = null;
        if ($request->hasFile('service_image')) {
            try {
                $file = $request->file('service_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/images/services'), $filename);
                Log::info('File uploaded successfully', ['filename' => $filename]);
            } catch (\Exception $e) {
                Log::error('File upload error', ['error' => $e->getMessage()]);
                return redirect()->back()->with('error', 'File upload failed.');
            }
        }


        $service = new Service();
        $service->service_name = $request->service_name;
        $service->service_short_description = $request->service_short_description;
        $service->service_long_description = $request->service_long_description;
        $service->service_image = $filename ?? null;
        $service->save();

        return redirect()->back()->with('success', 'Service added successfully.');
    }

    public function getUpdatedServicesTable()
    {
        $services = Service::paginate(5);
        return response()->json([
            'data' => $services->items(),
            'links' => (string) $services->links('vendor.pagination.bootstrap-5')
        ]);
    }

    public function fetchService($id)
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        return response()->json([
            'service_name' => $service->service_name,
            'service_short_description' => $service->service_short_description,
            'service_long_description' => $service->service_long_description,
            'service_image' => $service->service_image,  // filename only
        ]);

    }

    public function updateService(Request $request)
    {
        try {
            $service = Service::findOrFail($request->input('info_id'));

            $service->service_name = $request->input('service_name');
            $service->service_short_description = $request->input('service_short_description');
            $service->service_long_description = $request->input('service_long_description');
            if ($request->hasFile('service_image')) {
                $file = $request->file('service_image');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('assets/images/services'), $filename);
                $service->service_image = $filename;
            }

            $service->save();

            return response()->json(['status' => true, 'message' => 'Service updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Service update failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Service update failed']);
        }
    }

    public function deleteService(Request $request)
    {
        $serviceId = $request->input('service_id');

        try {
            $service = Service::findOrFail($serviceId);
            $service->delete();

            return response()->json([
                'status' => true,
                'message' => 'Service deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Service deletion failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
