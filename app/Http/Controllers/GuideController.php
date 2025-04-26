<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuideController extends Controller
{
    /**
     * Display a listing of all guides.
     */
    public function index()
    {
        $guides = [
            [
                'title' => 'Stream Management Guide',
                'description' => 'Learn how to set up, start, and manage streams in the TV Monitor System.',
                'slug' => 'stream-management-guide',
                'icon' => 'heroicon-o-play',
            ],
            [
                'title' => 'Demo Data Guide',
                'description' => 'Explore the demo data included with the system and learn how to use it.',
                'slug' => 'demo-data-guide',
                'icon' => 'heroicon-o-beaker',
            ],
            [
                'title' => 'Output Stream Configuration Guide',
                'description' => 'Learn how to create, edit, and monitor output streams in the TV Monitor System.',
                'slug' => 'output-stream-guide',
                'icon' => 'heroicon-o-arrow-path',
            ],
            [
                'title' => 'Input Stream Management',
                'description' => 'Configure and manage input video streams from various sources.',
                'slug' => 'input-stream-guide',
                'icon' => 'heroicon-o-arrow-down-on-square',
            ],
            [
                'title' => 'Multiview Layout Configuration',
                'description' => 'Design custom layouts for your multiview displays.',
                'slug' => 'multiview-layout-guide',
                'icon' => 'heroicon-o-squares-2x2',
            ],
            [
                'title' => 'Stream Health Monitoring',
                'description' => 'Monitor the health and status of your streams in real-time.',
                'slug' => 'stream-health-guide',
                'icon' => 'heroicon-o-heart',
            ],
        ];
        
        return view('guides.index', compact('guides'));
    }

    /**
     * Display the specified guide.
     */
    public function show($slug)
    {
        // Map slugs to view names
        $viewMap = [
            'stream-management-guide' => 'guides.stream-management-guide',
            'demo-data-guide' => 'guides.demo-data-guide',
            'output-stream-guide' => 'guides.output-stream-guide',
            'input-stream-guide' => 'guides.input-stream-guide',
            'multiview-layout-guide' => 'guides.multiview-layout-guide',
            'stream-health-guide' => 'guides.stream-health-guide',
        ];
        
        if (!isset($viewMap[$slug])) {
            abort(404);
        }
        
        return view($viewMap[$slug]);
    }
} 