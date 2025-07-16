<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Download;
use App\Models\FunctionRoom;
use App\Models\Gallery;
use App\Models\Events;
use Carbon\Carbon;


class NavbarController extends Controller
{
   public function home()
   {
      $featuredFunctionRooms = FunctionRoom::where('featured', 1)
         ->limit(4)
         ->get()
         ->map(function ($room) {
            $room->function_room_name = strtoupper($room->function_room_name ?? 'N/A');
            $room->function_room_section = strtoupper($room->function_room_section ?? 'N/A');
            $room->function_room_description = strtoupper($room->function_room_description ?? 'N/A');
            return $room;
         });

      return view('frontend.home', compact('featuredFunctionRooms'));
   }


   public function about()
   {
      return view('frontend.about');
   }
   public function services()
   {
      return view('frontend.services');
   }

   public function contact()
   {
      return view('frontend.contact');
   }

   public function downloadables()
   {
      return view('frontend.pages.downloadables');
   }
   public function sections()
   {
      return view('frontend.sections');
   }

   public function maps()
   {
      return view('frontend.maps');
   }
   public function ourTeam()
   {
      return view('frontend.pages.our-team');
   }

   public function gallery()
   {
      $images = Gallery::orderBy('created_at', 'asc')->get();

      return view('frontend.pages.gallery', compact('images'));
   }

   public function events(Request $request)
   {
      if ($request->ajax()) {
         $pastEvents = Events::whereDate('event_date', '<', now())
            ->orderBy('event_date', 'desc')
            ->skip($request->offset)
            ->take(8)
            ->get();

         return view('frontend.pages.partials.past-events-card', compact('pastEvents'))->render();
      }

      $upcomingEvents = Events::whereDate('event_date', '>=', now())->orderBy('event_date')->get();
      $pastEvents = Events::whereDate('event_date', '<', now())->orderBy('event_date', 'desc')->take(8)->get();
      $totalPastCount = Events::whereDate('event_date', '<', now())->count();

      return view('frontend.pages.events', compact('upcomingEvents', 'pastEvents', 'totalPastCount'));
   }

   public function showEventDetails($id)
   {
      $event = Events::findOrFail($id);
      return view('frontend.pages.event-details', compact('event'));
   }

   public function minorWorkPermit()
   {
      return view('frontend.online-forms.minor-work-permit');
   }

   public function getAllDownloads()
   {
      $downloads = Download::orderBy('created_at', 'asc')->get();
      $groupedDownloads = $downloads->groupBy('category_name');
      return view('frontend.pages.downloads', compact('groupedDownloads'));
   }

}
