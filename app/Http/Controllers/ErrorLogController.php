<?php

namespace App\Http\Controllers;

use App\Models\InputStream;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorLogController extends Controller
{
    /**
     * Clear a specific error log entry for an input stream.
     */
    public function clearEntry(Request $request)
    {
        $request->validate([
            'stream_id' => 'required|integer|exists:input_streams,id',
            'index' => 'required|integer|min:0',
        ]);
        
        $streamId = $request->input('stream_id');
        $index = $request->input('index');
        
        $stream = InputStream::findOrFail($streamId);
        
        // Get the current error log
        $errorLog = $stream->error_log;
        
        // Check if the index exists
        if (!is_array($errorLog) || !isset($errorLog[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'Error log entry not found',
            ], 404);
        }
        
        // Remove the entry at the specified index
        unset($errorLog[$index]);
        
        // Reindex the array to ensure sequential keys
        $errorLog = array_values($errorLog);
        
        // Update the stream with the modified error log
        $stream->update([
            'error_log' => $errorLog,
        ]);
        
        Log::info("Error log entry {$index} cleared for input stream {$streamId}");
        
        return response()->json([
            'success' => true,
            'message' => 'Error log entry cleared successfully',
        ]);
    }
} 