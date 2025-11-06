<?php

namespace App\Http\Controllers\API;

use App\Jobs\LinkHit;
use App\Models\links;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StorelinksRequest;

class LinksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorelinksRequest $request)
    {
        $validated = $request->validated();

        $link = Links::create([
            'slug' => $validated['slug'],
            'target_url' => $validated['target_url'],
            'is_active' => $validated['is_active'],
        ]);

        if ($link->is_active) {
            $key = ":link-{$link->slug}";
            Cache::put(
                $key,
                [
                    'id' => $link->id,
                    'target_url' => $link->target_url
                ],
                null
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Link created successfully',
            'data' => $link
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function redirect(Request $request, $slug)
    {
        if (empty($slug)) {
            return response()->json([
                'success' => false,
                'error' => 'Bad Request',
                'message' => "Required parameter 'slug' is missing or empty",
            ], 400);
        }

        $cLink = Cache::get(":link-{$slug}");

        $data = [
            'slug' => $slug,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'Unknown',
        ];

        if ($cLink) {
            $data['link_id'] = $cLink['id'];

            LinkHit::dispatch($data);

            return redirect()
                ->away($cLink['target_url'])
                ->with([
                    'success' => true,
                    'message' => 'Link is active and exists in caching',
                ]);
        } else {
            $dbLink = Links::where('slug', $slug)->first();

            if ($dbLink) {
                if (!$dbLink->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No redirect, Link is not active'
                    ], 404);
                } elseif ($dbLink->is_active) {
                    $key = ":link-{$dbLink->slug}";
                    Cache::put(
                        $key,
                        [
                            'id' => $dbLink->id,
                            'target_url' => $dbLink->target_url
                        ],
                        null
                    );
                    $data['link_id'] = $dbLink->id;

                    LinkHit::dispatch($data);

                    return redirect()->away($dbLink->target_url)->with([
                        'success' => true,
                        'message' => 'Link is active but not found in cache',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Slug not found in the database',
        ], 404);
    }

    public function statistics($slug)
    {
        if (empty($slug)) {
            return response()->json([
                'success' => false,
                'message' => "Required parameter 'slug' is missing or empty",
            ], 400);
        }

        try {
            $statistics = Cache::get(":link-statistics-{$slug}");

            if ($statistics) {
                return response()->json([
                    'success' => true,
                    'message' => "Get statistics from the cache",
                    'data' => $statistics
                ]);
            }

            // DB::enableQueryLog();
            $link = Links::select('id', 'target_url')->where('slug', $slug)->withCount('hits')->with('lastHits')->first();
            // dd(DB::getQueryLog());

            if ($link) {
                $formattedIp = $link->getFormattedLastHits();

                $key = ":link-statistics-{$slug}";

                $data = [
                    'id' => $link->id,
                    'target_url' => $link->target_url,
                    'hits_count' => $link->hits_count,
                    'formatted_ips' => $formattedIp
                ];

                Cache::put(
                    $key,
                    $data,
                    60
                );

                return response()->json([
                    'success' => true,
                    'message' => "Get statistics from the database",
                    'data' => $data
                ]);

            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Slug not found in the database',
                ], 404);
            }
            ;
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
