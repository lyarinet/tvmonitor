<?php

namespace App\Services\Monitoring;

use App\Models\InputStream;
use App\Models\OutputStream;
use App\Services\FFmpeg\FFmpegService;
use Illuminate\Support\Facades\Log;

class StreamMonitoringService
{
    protected FFmpegService $ffmpegService;
    
    public function __construct(FFmpegService $ffmpegService)
    {
        $this->ffmpegService = $ffmpegService;
    }
    
    /**
     * Monitor all input streams and update their status.
     */
    public function monitorInputStreams(): array
    {
        $results = [];
        $inputStreams = InputStream::where('status', '!=', 'inactive')->get();
        
        foreach ($inputStreams as $inputStream) {
            $results[$inputStream->id] = $this->monitorInputStream($inputStream);
        }
        
        return $results;
    }
    
    /**
     * Monitor a specific input stream and update its status.
     */
    public function monitorInputStream(InputStream $inputStream): array
    {
        try {
            // Get health data from FFmpeg service (now includes the command)
            $healthData = $this->ffmpegService->checkStreamHealth($inputStream);
            
            // Get current metadata and ensure it's an array
            $metadata = $inputStream->metadata;
            
            // If metadata is null, a string, or not an array, initialize it as an empty array
            if ($metadata === null || !is_array($metadata)) {
                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $metadata = [];
                }
            }
            
            // Add the commands to the metadata
            $metadata['last_command'] = $healthData['command'] ?? 'Command not available';
            if (isset($healthData['ffmpeg_command'])) {
                $metadata['ffmpeg_command'] = $healthData['ffmpeg_command'];
            }
            
            // Update the input stream with the health data
            $inputStream->update([
                'status' => $healthData['status'],
                'metadata' => array_merge($metadata, [
                    'health' => $healthData,
                    'last_checked' => now()->toIso8601String(),
                ]),
            ]);
            
            if ($healthData['status'] === 'error') {
                // Get current error_log and ensure it's an array
                $errorLog = $inputStream->error_log;
                
                // If error_log is null, a string, or not an array, initialize it as an empty array
                if ($errorLog === null || !is_array($errorLog)) {
                    $errorLog = !empty($errorLog) ? json_decode($errorLog, true) : [];
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errorLog = [];
                    }
                }
                
                $errorEntry = [
                    'timestamp' => now()->toIso8601String(),
                    'message' => $healthData['error'] ?? 'Unknown error',
                    'command' => $healthData['command'] ?? 'Command not available',
                ];
                
                // Add the ffmpeg command if available
                if (isset($healthData['ffmpeg_command'])) {
                    $errorEntry['ffmpeg_command'] = $healthData['ffmpeg_command'];
                }
                
                $inputStream->update([
                    'error_log' => array_merge($errorLog, [$errorEntry]),
                ]);
                
                Log::warning("Stream health check failed for input stream {$inputStream->id}: " . ($healthData['error'] ?? 'Unknown error'));
            }
            
            return $healthData;
        } catch (\Exception $e) {
            Log::error("Error monitoring input stream {$inputStream->id}: {$e->getMessage()}");
            
            // Get current error_log and ensure it's an array
            $errorLog = $inputStream->error_log;
            
            // If error_log is null, a string, or not an array, initialize it as an empty array
            if ($errorLog === null || !is_array($errorLog)) {
                $errorLog = [];
            }
            
            // Try to get the command that would have been used
            $ffmpegCommand = "Unknown command";
            try {
                // Use the FFmpegService to get the command
                $inputCommand = $this->ffmpegService->getInputCommand($inputStream);
                $ffmpegCommand = "ffprobe -i \"" . $inputStream->url . "\" -v error -show_entries stream=width,height,codec_name,bit_rate -show_entries format=duration,bit_rate -of json";
                $ffmpegFullCommand = "ffmpeg " . $inputCommand . " [output options]";
            } catch (\Exception $cmdEx) {
                // If we can't get the command, just use the exception message
                Log::error("Error getting FFmpeg command: {$cmdEx->getMessage()}");
            }
            
            $errorEntry = [
                'timestamp' => now()->toIso8601String(),
                'message' => $e->getMessage(),
                'command' => $ffmpegCommand,
            ];
            
            if (isset($ffmpegFullCommand)) {
                $errorEntry['ffmpeg_command'] = $ffmpegFullCommand;
            }
            
            $inputStream->update([
                'status' => 'error',
                'error_log' => array_merge($errorLog, [$errorEntry]),
            ]);
            
            $result = [
                'status' => 'error',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
                'command' => $ffmpegCommand,
            ];
            
            if (isset($ffmpegFullCommand)) {
                $result['ffmpeg_command'] = $ffmpegFullCommand;
            }
            
            return $result;
        }
    }
    
    /**
     * Monitor all output streams and update their status.
     */
    public function monitorOutputStreams(): array
    {
        $results = [];
        $outputStreams = OutputStream::where('status', 'active')->get();
        
        foreach ($outputStreams as $outputStream) {
            $results[$outputStream->id] = $this->monitorOutputStream($outputStream);
        }
        
        return $results;
    }
    
    /**
     * Monitor a specific output stream and update its status.
     */
    public function monitorOutputStream(OutputStream $outputStream): array
    {
        try {
            $metadata = $outputStream->metadata;
            
            // Ensure metadata is an array
            if ($metadata === null || !is_array($metadata)) {
                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $metadata = [];
                }
            }
            
            // Check if the process is still running
            if (isset($metadata['process_id'])) {
                $processId = $metadata['process_id'];
                $processCheck = shell_exec("ps -p {$processId} -o pid=");
                
                $isRunning = !empty($processCheck);
                
                if (!$isRunning && $outputStream->status === 'active') {
                    // Process has died unexpectedly
                    $errorLog = $outputStream->error_log;
                    
                    // Ensure error_log is an array
                    if ($errorLog === null || !is_array($errorLog)) {
                        $errorLog = !empty($errorLog) ? json_decode($errorLog, true) : [];
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $errorLog = [];
                        }
                    }
                    
                    $outputStream->update([
                        'status' => 'error',
                        'error_log' => array_merge($errorLog, [
                            [
                                'timestamp' => now()->toIso8601String(),
                                'message' => "Process {$processId} is no longer running",
                            ],
                        ]),
                    ]);
                    
                    Log::warning("Output stream process {$processId} for stream {$outputStream->id} is no longer running");
                    
                    return [
                        'status' => 'error',
                        'error' => "Process {$processId} is no longer running",
                        'checked_at' => now()->toIso8601String(),
                    ];
                }
                
                // Update the last checked timestamp
                $outputStream->update([
                    'metadata' => array_merge($metadata, [
                        'last_checked' => now()->toIso8601String(),
                        'is_running' => $isRunning,
                    ]),
                ]);
                
                return [
                    'status' => $isRunning ? 'active' : 'error',
                    'is_running' => $isRunning,
                    'process_id' => $processId,
                    'checked_at' => now()->toIso8601String(),
                ];
            }
            
            return [
                'status' => 'unknown',
                'error' => 'No process ID found',
                'checked_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error("Error monitoring output stream {$outputStream->id}: {$e->getMessage()}");
            
            $errorLog = $outputStream->error_log;
            
            // Ensure error_log is an array
            if ($errorLog === null || !is_array($errorLog)) {
                $errorLog = !empty($errorLog) ? json_decode($errorLog, true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errorLog = [];
                }
            }
            
            $outputStream->update([
                'error_log' => array_merge($errorLog, [
                    [
                        'timestamp' => now()->toIso8601String(),
                        'message' => $e->getMessage(),
                    ],
                ]),
            ]);
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
                'checked_at' => now()->toIso8601String(),
            ];
        }
    }
    
    /**
     * Generate thumbnails for all input streams.
     */
    public function generateThumbnails(): array
    {
        $results = [];
        $inputStreams = InputStream::where('status', 'active')->get();
        
        foreach ($inputStreams as $inputStream) {
            $results[$inputStream->id] = $this->generateThumbnail($inputStream);
        }
        
        return $results;
    }
    
    /**
     * Generate a thumbnail for a specific input stream.
     */
    public function generateThumbnail(InputStream $inputStream): ?string
    {
        try {
            $thumbnailPath = $this->ffmpegService->generateThumbnail($inputStream);
            
            if ($thumbnailPath) {
                // Get current metadata and ensure it's an array
                $metadata = $inputStream->metadata;
                
                // If metadata is null or not an array, initialize it as an empty array
                if ($metadata === null || !is_array($metadata)) {
                    $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $metadata = [];
                    }
                }
                
                // Add thumbnail information to metadata
                $metadata['thumbnail'] = $thumbnailPath;
                $metadata['thumbnail_generated_at'] = now()->toIso8601String();
                
                // Update the input stream with the new metadata
                $inputStream->update([
                    'metadata' => $metadata,
                ]);
            }
            
            return $thumbnailPath;
        } catch (\Exception $e) {
            Log::error("Error generating thumbnail for input stream {$inputStream->id}: {$e->getMessage()}");
            
            return null;
        }
    }
} 