<?php

namespace App\Http\Controllers;

use App\Models\Links;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index()
    {
        $links = Links::latest()
        ->filterBySlug()
        ->withCount('hits')
        ->where(function ($query) {
            if( $id = request()->query('id') ) {
                $query->where('id', $id);
            }
        })
        ->paginate(10);

        $slugs = Links::select('id','slug')->distinct()->pluck('slug' , 'id');
        return view('links.index', compact('links','slugs'));
    }

    public function create() {
        return view('links.create');
    }

    public function store(Request $request) {

    }
    public function show($id) {
        view('links.show');
    }

    public function destroy($id) {
        Links::where('id', $id)->delete();
    }
}
