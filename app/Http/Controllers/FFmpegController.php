<?php

namespace App\Http\Controllers;

use App\Services\FFmpeg\FFmpegService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FFmpegController extends Controller
{
    protected $ffmpegService;
    
    /**
     * Create a new FFmpegController instance.
     */
    public function __construct(FFmpegService $ffmpegService)
    {
        $this->ffmpegService = $ffmpegService;
    }
    
    /**
     * Get a list of all active output streams.
     */
    public function listOutputStreams(Request $request): JsonResponse
    {
        $streams = $this->ffmpegService->getActiveOutputStreams();
        
        return response()->json([
            'success' => true,
            'data' => $streams,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
} 