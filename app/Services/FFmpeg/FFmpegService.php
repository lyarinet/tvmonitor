<?php

namespace App\Services\FFmpeg;

use App\Models\InputStream;
use App\Models\LayoutPosition;
use App\Models\MultiviewLayout;
use App\Models\OutputStream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process as SymfonyProcess;

class FFmpegService
{
    /**
     * Generate FFmpeg command for a multiview layout.
     */
    public function generateMultiviewCommand(MultiviewLayout $layout, OutputStream $outputStream): string
    {
        $layoutPositions = $layout->layoutPositions()->with('inputStream')->get();
        
        // Base command
        $command = 'ffmpeg ';
        
        // Track input streams and their indexes
        $inputCounter = 0;
        $inputStreamIds = [];  // Keep track of which input stream is at which index
        $inputStreamOptions = []; // Track options for each input
        
        // Add input streams
        foreach ($layoutPositions as $position) {
            if ($position->inputStream) {
                $inputStream = $position->inputStream;
                $inputUrl = $inputStream->processed_url ?? $inputStream->url;
                
                // Add protocol-specific options for input
                $options = '';
                switch ($inputStream->protocol) {
                    case 'rtsp':
                        $options = '-rtsp_transport tcp -i';
                        break;
                    case 'udp':
                        $options = '-thread_queue_size 1024 -fflags +nobuffer+genpts -i';
                        break;
                    case 'hls':
                        $options = '-i';
                        break;
                    default:
                        $options = '-i';
                        break;
                }
                
                // Add authentication if provided
                if ($inputStream->username && $inputStream->password) {
                    $protocol = parse_url($inputUrl, PHP_URL_SCHEME);
                    $host = parse_url($inputUrl, PHP_URL_HOST);
                    $path = parse_url($inputUrl, PHP_URL_PATH);
                    $query = parse_url($inputUrl, PHP_URL_QUERY);
                    
                    $inputUrl = "{$protocol}://{$inputStream->username}:{$inputStream->password}@{$host}{$path}";
                    if ($query) {
                        $inputUrl .= "?{$query}";
                    }
                }
                
                $command .= "{$options} \"{$inputUrl}\" ";
                
                // Track the input stream's position
                $inputStreamIds[$position->input_stream_id] = $inputCounter;
                
                // Store post-input options for proper sequencing
                $postInputOptions = '';
                
                // Add UDP-specific options
                if ($inputStream->protocol === 'udp') {
                    // Add program mapping if specified
                    if (!empty($inputStream->program_id)) {
                        $postInputOptions .= " -map {$inputCounter}:p:{$inputStream->program_id}";
                    }
                    
                    // Add ignore_unknown option if enabled
                    if (isset($inputStream->ignore_unknown) && $inputStream->ignore_unknown) {
                        $postInputOptions .= ' -ignore_unknown';
                    }
                    
                    // Add map -d option if enabled (disable data streams)
                    if (isset($inputStream->map_disable_data) && $inputStream->map_disable_data) {
                        $postInputOptions .= " -map -{$inputCounter}:d";
                    }
                    
                    // Add map -s option if enabled (disable subtitle streams)
                    if (isset($inputStream->map_disable_subtitles) && $inputStream->map_disable_subtitles) {
                        $postInputOptions .= " -map -{$inputCounter}:s";
                    }
                    
                    // Add any additional options
                    if (!empty($inputStream->additional_options) && is_array($inputStream->additional_options)) {
                        foreach ($inputStream->additional_options as $key => $value) {
                            if (!empty($key)) {
                                $postInputOptions .= " {$key}";
                                if (!empty($value)) {
                                    $postInputOptions .= " {$value}";
                                }
                            }
                        }
                    }
                }
                
                // Store post-input options
                $inputStreamOptions[$inputCounter] = $postInputOptions;
                
                $inputCounter++;
            }
        }
        
        // Check if we have any inputs
        if ($inputCounter === 0) {
            throw new \Exception("No input streams found for multiview layout");
        }
        
        // Add all post-input options
        for ($i = 0; $i < $inputCounter; $i++) {
            if (!empty($inputStreamOptions[$i])) {
                $command .= $inputStreamOptions[$i] . ' ';
            }
        }
        
        // Filter complex for multiview
        $command .= '-filter_complex "';
        
        // Create a background
        $command .= "color=c={$layout->background_color}:s={$layout->width}x{$layout->height}[base]; ";
        
        // Add each input stream to the layout
        $inputIndex = 0;
        $lastTmpIndex = 'base'; // Start with the base
        
        foreach ($layoutPositions as $position) {
            if ($position->inputStream) {
                // Get the correct input index for this position
                $inputIdx = $inputStreamIds[$position->input_stream_id];
                
                // Scale the input to fit the position
                $command .= "[{$inputIdx}:v]scale={$position->width}:{$position->height}[v{$inputIndex}]; ";
                
                // Overlay the input on the base at the specified position
                $command .= "[{$lastTmpIndex}][v{$inputIndex}]overlay={$position->position_x}:{$position->position_y}";
                
                // Add label if enabled
                if ($position->show_label && $position->inputStream) {
                    $labelText = $position->inputStream->name;
                    $labelX = $position->position_x + 10;
                    $labelY = $position->position_y + $position->height - 30;
                    
                    if ($position->label_position === 'top') {
                        $labelY = $position->position_y + 10;
                    } elseif ($position->label_position === 'left') {
                        $labelX = $position->position_x + 10;
                        $labelY = $position->position_y + $position->height / 2;
                    } elseif ($position->label_position === 'right') {
                        $labelX = $position->position_x + $position->width - 100;
                        $labelY = $position->position_y + $position->height / 2;
                    }
                    
                    $command .= ",drawtext=text='{$labelText}':fontcolor=white:fontsize=24:x={$labelX}:y={$labelY}:box=1:boxcolor=black@0.5:boxborderw=5";
                }
                
                // Set the output of this overlay as the input for the next one
                if ($inputIndex < count($layoutPositions) - 1) {
                    $nextTmpIndex = $inputIndex + 1;
                    $command .= "[tmp{$nextTmpIndex}]; ";
                    $lastTmpIndex = "tmp{$nextTmpIndex}";
                } else {
                    // Last overlay gets the [out] label
                    $command .= "[out]\" ";
                }
                
                $inputIndex++;
            }
        }
        
        // Map the output
        $command .= '-map "[out]" ';
        
        // Add output options based on protocol
        $command .= $this->getOutputCommand($outputStream);
        
        return $command;
    }
    
    /**
     * Get the FFmpeg input command for a stream.
     */
    public function getInputCommand(InputStream $inputStream): string
    {
        $inputUrl = $inputStream->processed_url ?? $inputStream->url;
        
        // Add authentication if provided
        if ($inputStream->username && $inputStream->password) {
            $protocol = parse_url($inputUrl, PHP_URL_SCHEME);
            $host = parse_url($inputUrl, PHP_URL_HOST);
            $path = parse_url($inputUrl, PHP_URL_PATH);
            $query = parse_url($inputUrl, PHP_URL_QUERY);
            
            $inputUrl = "{$protocol}://{$inputStream->username}:{$inputStream->password}@{$host}{$path}";
            if ($query) {
                $inputUrl .= "?{$query}";
            }
        }
        
        // Add protocol-specific options
        $options = '';
        switch ($inputStream->protocol) {
            case 'rtsp':
                $options = '-rtsp_transport tcp -i';
                break;
            case 'udp':
                $options = '-thread_queue_size 1024 -fflags +nobuffer+genpts -i';
                break;
            case 'hls':
                $options = '-i';
                break;
            default:
                $options = '-i';
                break;
        }
        
        $command = "{$options} \"{$inputUrl}\"";
        
        // Add any additional FFmpeg input options
        if (method_exists($inputStream, 'getFFmpegInputOptions')) {
            $additionalOptions = $inputStream->getFFmpegInputOptions();
            if (!empty($additionalOptions)) {
                $command .= $additionalOptions;
            }
        } else {
            // Fallback for UDP advanced options if the method doesn't exist
            if ($inputStream->protocol === 'udp') {
                // Add program mapping if specified
                if (!empty($inputStream->program_id)) {
                    $command .= " -map 0:p:{$inputStream->program_id}";
                }
                
                // Add ignore_unknown option if enabled
                if (isset($inputStream->ignore_unknown) && $inputStream->ignore_unknown) {
                    $command .= ' -ignore_unknown';
                }
                
                // Add map -d option if enabled (disable data streams)
                if (isset($inputStream->map_disable_data) && $inputStream->map_disable_data) {
                    $command .= ' -map -d';
                }
                
                // Add map -s option if enabled (disable subtitle streams)
                if (isset($inputStream->map_disable_subtitles) && $inputStream->map_disable_subtitles) {
                    $command .= ' -map -s';
                }
                
                // Add any additional options
                if (!empty($inputStream->additional_options) && is_array($inputStream->additional_options)) {
                    foreach ($inputStream->additional_options as $key => $value) {
                        if (!empty($key)) {
                            $command .= " {$key}";
                            if (!empty($value)) {
                                $command .= " {$value}";
                            }
                        }
                    }
                }
            }
        }
        
        return $command;
    }
    
    /**
     * Get the FFmpeg output command for a stream.
     */
    private function getOutputCommand(OutputStream $outputStream): string
    {
        $outputOptions = '';
        
        // Add protocol-specific options
        switch ($outputStream->protocol) {
            case 'direct':
                // For direct protocol, use copy codec to avoid transcoding
                $outputOptions = "-c copy -f mpegts \"{$outputStream->processed_url}\"";
                break;
            case 'hls':
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                if (!file_exists($outputDir)) {
                    mkdir($outputDir, 0755, true);
                }
                
                $outputOptions = "-c:v libx264 -preset veryfast -g 30 -sc_threshold 0 -f hls -hls_time 2 -hls_list_size 6 -hls_flags delete_segments+append_list+program_date_time -hls_segment_type mpegts -hls_allow_cache 0 -start_number 0 -hls_segment_filename \"{$outputDir}/segment_%03d.ts\" \"{$outputDir}/playlist.m3u8\"";
                break;
            case 'rtsp':
                // For RTSP output, use libx264 for video encoding
                $outputOptions = "-c:v libx264 -preset veryfast -g 30 -f rtsp {$outputStream->processed_url}";
                break;
            case 'udp':
                // Append UDP-specific parameters if not already present
                $url = $outputStream->processed_url;
                if (strpos($url, '?') === false) {
                    $url .= '?ttl=2&pkt_size=1316';
                } elseif (!strpos($url, 'pkt_size=')) {
                    $url .= '&ttl=2&pkt_size=1316';
                }
                
                $outputOptions = "-c:v libx264 -preset veryfast -g 30 -f mpegts -flush_packets 1 \"{$url}\"";
                break;
            case 'dash':
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                if (!file_exists($outputDir)) {
                    mkdir($outputDir, 0755, true);
                }
                
                $outputOptions = "-c:v libx264 -preset veryfast -g 30 -sc_threshold 0 -f dash -use_timeline 1 -use_template 1 -window_size 5 -extra_window_size 10 -remove_at_exit 0 \"{$outputDir}/manifest.mpd\"";
                break;
            default:
                $outputOptions = "-c:v libx264 -preset veryfast -g 30 -f flv {$outputStream->processed_url}";
                break;
        }
        
        // Add custom FFmpeg options if provided
        if ($outputStream->ffmpeg_options) {
            $customOptions = $outputStream->ffmpeg_options;
            if (is_array($customOptions) && !empty($customOptions)) {
                foreach ($customOptions as $option => $value) {
                    $outputOptions .= " {$option} {$value}";
                }
            }
        }
        
        return $outputOptions;
    }
    
    /**
     * Start a multiview process.
     */
    public function startMultiview(MultiviewLayout $layout, OutputStream $outputStream): bool
    {
        try {
            // Ensure storage directory exists if using a storage path
            if (strpos($outputStream->url, '{storage_path}') !== false) {
                $outputStream->ensureStorageDirectoryExists();
                Log::info("Ensured storage directory exists for output stream ID {$outputStream->id}");
            }
            
            $command = $this->generateMultiviewCommand($layout, $outputStream);
            
            // Log the command for debugging
            Log::info("Starting FFmpeg multiview process with command: {$command}");
            
            // Start the process in the background
            $process = Process::start($command);
            
            // Ensure metadata is an array before merging
            $metadata = $outputStream->metadata;
            if (!is_array($metadata)) {
                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                // If JSON decoding fails, just create an empty array
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $metadata = [];
                }
            }
            
            // Store the process ID
            $outputStream->update([
                'metadata' => array_merge($metadata, ['process_id' => $process->id()]),
                'status' => 'active',
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error starting multiview process: {$e->getMessage()}");
            
            $outputStream->update([
                'status' => 'error',
                'error_log' => array_merge($outputStream->error_log ?? [], [
                    'timestamp' => now()->toIso8601String(),
                    'message' => $e->getMessage(),
                ]),
            ]);
            
            return false;
        }
    }
    
    /**
     * Stop a multiview process.
     */
    public function stopMultiview(OutputStream $outputStream): bool
    {
        try {
            $metadata = $outputStream->metadata;
            
            // Ensure metadata is an array
            if (!is_array($metadata)) {
                $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $metadata = [];
                }
            }
            
            // Special handling for direct passthrough
            if (isset($metadata['direct_passthrough']) && $metadata['direct_passthrough'] === true) {
                // For direct passthrough, we just need to update the status
                Log::info("Stopping direct passthrough for stream: {$outputStream->id}");
                
                $outputStream->update([
                    'status' => 'inactive',
                    'metadata' => array_merge($metadata, ['direct_passthrough' => false])
                ]);
                
                return true;
            }
            
            $success = false;
            $killLog = [];
            $processId = $metadata['process_id'] ?? null;
            
            // Log attempt to stop the FFmpeg process
            Log::info("Attempting to stop FFmpeg processes for stream ID: {$outputStream->id}, Process ID: {$processId}");
            
            // Approach 1: Kill the main process if we have a PID
            if ($processId) {
                $killLog[] = "Attempting direct kill with SIGTERM signal on PID {$processId}";
                exec("kill {$processId} 2>&1", $output1, $code1);
                $killLog[] = "Direct kill result: " . implode("\n", $output1) . " (code: {$code1})";
                
                // Method 2: Try forceful kill if the first method failed
                if ($code1 !== 0) {
                    sleep(1);
                    $killLog[] = "Attempting forceful kill with SIGKILL signal on PID {$processId}";
                    exec("kill -9 {$processId} 2>&1", $output2, $code2);
                    $killLog[] = "Forceful kill result: " . implode("\n", $output2) . " (code: {$code2})";
                }
            }
            
            // Approach 2: Find ALL ffmpeg processes by their command line that match this output stream's ID
            $streamId = $outputStream->id;
            $killLog[] = "Searching for ALL FFmpeg processes related to stream ID {$streamId}";
            
            // Look for ffmpeg processes with this stream ID in their command line
            exec("ps aux | grep ffmpeg | grep -E '[^0-9]{$streamId}[^0-9]|[^0-9]{$streamId}\$' | grep -v grep", $relatedProcesses);
            
            $relatedPids = [];
            foreach ($relatedProcesses as $process) {
                // Extract the PID from the process line (second column in ps aux output)
                $parts = preg_split('/\s+/', trim($process));
                if (isset($parts[1]) && is_numeric($parts[1])) {
                    $relatedPids[] = $parts[1];
                }
            }
            
            if (!empty($relatedPids)) {
                $killLog[] = "Found related FFmpeg processes with PIDs: " . implode(", ", $relatedPids);
                
                // Kill each process and its children
                foreach ($relatedPids as $pid) {
                    // Kill process tree (parent and all children)
                    $killLog[] = "Killing process tree for PID {$pid}";
                    
                    // Find all child processes
                    exec("pgrep -P {$pid}", $childPids);
                    $allPids = array_merge([$pid], $childPids);
                    
                    foreach ($allPids as $targetPid) {
                        $targetPid = trim($targetPid);
                        if (!empty($targetPid) && is_numeric($targetPid)) {
                            // Try graceful termination first
                            $killLog[] = "Sending SIGTERM to PID {$targetPid}";
                            exec("kill {$targetPid} 2>&1", $outputKill, $codeKill);
                            $killLog[] = "SIGTERM result for PID {$targetPid}: " . ($codeKill === 0 ? "Success" : "Failed");
                            
                            // If that fails, use force
                            if ($codeKill !== 0) {
                                $killLog[] = "Sending SIGKILL to PID {$targetPid}";
                                exec("kill -9 {$targetPid} 2>&1", $outputForce, $codeForce);
                                $killLog[] = "SIGKILL result for PID {$targetPid}: " . ($codeForce === 0 ? "Success" : "Failed");
                            }
                        }
                    }
                }
            } else {
                $killLog[] = "No matching FFmpeg processes found by grep";
            }
            
            // Approach 3: For HLS outputs, kill any processes writing to the output directory
            if ($outputStream->protocol === 'hls') {
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                $killLog[] = "Checking for processes writing to output directory: {$outputDir}";
                
                // Find processes that are writing to the output directory
                exec("lsof +D " . escapeshellarg($outputDir) . " | grep -v grep | awk '{print \$2}' | sort | uniq", $dirPids);
                
                if (!empty($dirPids)) {
                    $killLog[] = "Found processes writing to output directory: " . implode(", ", $dirPids);
                    
                    foreach ($dirPids as $pid) {
                        if (is_numeric($pid)) {
                            $killLog[] = "Killing process writing to output directory: {$pid}";
                            // Kill with SIGKILL to ensure immediate termination
                            exec("kill -9 {$pid} 2>&1", $outputDir, $codeDir);
                            $killLog[] = "Kill result: " . ($codeDir === 0 ? "Success" : "Failed");
                        }
                    }
                } else {
                    $killLog[] = "No processes found writing to output directory";
                }
            }
            
            // Approach 4: Use pkill to find and kill any ffmpeg processes related to this stream
            $killLog[] = "Using pkill to find and kill any remaining ffmpeg processes for stream {$streamId}";
            $pkillPattern = escapeshellarg("ffmpeg.*{$streamId}");
            exec("pkill -f {$pkillPattern}", $pkillOutput, $pkillCode);
            $killLog[] = "pkill result: " . ($pkillCode === 0 ? "Success" : "No matching processes found") . " (code: {$pkillCode})";
            
            // Forceful kill with pkill -9
            exec("pkill -9 -f {$pkillPattern}", $pkillOutput9, $pkillCode9);
            $killLog[] = "pkill -9 result: " . ($pkillCode9 === 0 ? "Success" : "No matching processes found") . " (code: {$pkillCode9})";
            
            // Check if the original process is still running (if we had a process ID)
            sleep(2); // Wait a bit to let the kill take effect
            $isRunning = false;
            
            if ($processId) {
                exec("ps -p {$processId} -o pid=", $checkOutput, $checkCode);
                $isRunning = !empty($checkOutput);
                $killLog[] = "Process {$processId} is " . ($isRunning ? "still running" : "no longer running");
            }
            
            // Additional check for HLS streams - verify no new segments are being created
            $additionalChecks = [];
            if ($outputStream->protocol === 'hls') {
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                $additionalChecks['output_directory_exists'] = file_exists($outputDir);
                
                if ($additionalChecks['output_directory_exists']) {
                    // Check if new segments are being created
                    $segmentCount1 = count(glob("{$outputDir}/segment_*.ts"));
                    sleep(3); // Wait to see if new segments are created
                    $segmentCount2 = count(glob("{$outputDir}/segment_*.ts"));
                    
                    $additionalChecks['initial_segment_count'] = $segmentCount1;
                    $additionalChecks['follow_up_segment_count'] = $segmentCount2;
                    $additionalChecks['new_segments_created'] = ($segmentCount2 > $segmentCount1);
                    
                    if ($additionalChecks['new_segments_created']) {
                        $killLog[] = "WARNING: New segments are still being created despite kill attempts";
                    } else {
                        $killLog[] = "No new segments created after kill attempts";
                        $success = true; // Consider it a success if no new segments are being created
                    }
                }
            } else {
                $success = !$isRunning; // For non-HLS streams, success is when the process is not running
            }
            
            // Final check - try one more time to find any remaining ffmpeg processes for this stream
            exec("ps aux | grep ffmpeg | grep -E '[^0-9]{$streamId}[^0-9]|[^0-9]{$streamId}\$' | grep -v grep", $finalCheck);
            if (empty($finalCheck)) {
                $killLog[] = "No ffmpeg processes remain for stream {$streamId}";
                $success = true;
            } else {
                $killLog[] = "WARNING: Some ffmpeg processes may still remain: " . count($finalCheck);
                foreach ($finalCheck as $idx => $proc) {
                    $killLog[] = "Remaining process {$idx}: " . preg_replace('/\s+/', ' ', trim($proc));
                }
            }
            
            // Update the output stream status
            $outputStream->update([
                'status' => 'inactive',
                'metadata' => array_merge($metadata, [
                    'stopped_at' => now()->toIso8601String(),
                    'kill_results' => $killLog,
                    'stop_success' => $success,
                    'additional_checks' => $additionalChecks ?? [],
                ]),
            ]);
            
            // Log all the kill attempts
            Log::info("Stream stop process details for stream ID {$streamId}: " . implode("\n", $killLog));
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error("Error stopping multiview process: {$e->getMessage()}", [
                'stream_id' => $outputStream->id,
                'exception' => $e->getTraceAsString()
            ]);
            
            // Still update the status to inactive
            $outputStream->update(['status' => 'inactive']);
            
            return false;
        }
    }
    
    /**
     * Check if FFmpeg is installed and available.
     */
    public function checkFFmpegInstallation(): bool
    {
        try {
            $process = Process::run('which ffmpeg');
            
            return $process->successful();
        } catch (\Exception $e) {
            Log::error("Error checking FFmpeg installation: {$e->getMessage()}");
            
            return false;
        }
    }
    
    /**
     * Get FFmpeg version information.
     */
    public function getFFmpegVersion(): ?string
    {
        try {
            $process = Process::run('ffmpeg -version');
            
            if ($process->successful()) {
                $output = $process->output();
                $versionLine = explode("\n", $output)[0];
                
                return $versionLine;
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error getting FFmpeg version: {$e->getMessage()}");
            
            return null;
        }
    }
    
    /**
     * Generate a thumbnail for an input stream.
     */
    public function generateThumbnail(InputStream $inputStream): ?string
    {
        try {
            $thumbnailDir = storage_path('app/public/thumbnails');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $thumbnailPath = "thumbnails/{$inputStream->id}.jpg";
            $fullPath = storage_path("app/public/{$thumbnailPath}");
            
            // Get the input command which handles UDP special cases properly
            $inputCommand = $this->getInputCommand($inputStream);
            
            // For UDP streams, we need a different approach with specific options
            if ($inputStream->protocol === 'udp') {
                // Create a short temporary recording and then extract a frame
                $tempFile = storage_path("app/temp_{$inputStream->id}.ts");
                
                // For UDP streams with program mapping
                $programMap = '';
                if (!empty($inputStream->program_id)) {
                    if (!str_contains($inputCommand, "-map 0:p:{$inputStream->program_id}")) {
                        $programMap = " -map 0:p:{$inputStream->program_id}";
                    }
                }
                
                // Step 1: Record a short segment first (more reliable than direct frame grabbing)
                $recordCommand = "ffmpeg {$inputCommand} -t 3 -c copy -y \"{$tempFile}\"";
                Log::info("Recording short segment for thumbnail: {$recordCommand}");
                
                $recordProcess = Process::timeout(15)->run($recordCommand);
                
                if (!$recordProcess->successful()) {
                    Log::error("Failed to record temporary segment: " . $recordProcess->errorOutput());
                    return null;
                }
                
                // Step 2: Extract a frame from the recording
                $extractCommand = "ffmpeg -i \"{$tempFile}\"{$programMap} -ss 00:00:01 -vframes 1 -vf scale=320:180 -y \"{$fullPath}\"";
                Log::info("Extracting thumbnail from recording: {$extractCommand}");
                
                $extractProcess = Process::timeout(15)->run($extractCommand);
                
                // Clean up the temporary file regardless of success
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                
                if ($extractProcess->successful()) {
                    return $thumbnailPath;
                }
                
                Log::error("Failed to extract thumbnail from recording: " . $extractProcess->errorOutput());
                return null;
            }
            
            // For non-UDP streams, use the original approach
            // For UDP streams, we need to add program mapping before the output options
            $programMap = '';
            if ($inputStream->protocol === 'udp' && !empty($inputStream->program_id)) {
                // Check if map is already in the input command
                if (!str_contains($inputCommand, "-map 0:p:{$inputStream->program_id}")) {
                    $programMap = " -map 0:p:{$inputStream->program_id}";
                }
            }
            
            // Command with proper program mapping
            $command = "ffmpeg {$inputCommand}{$programMap} -ss 00:00:01 -vframes 1 -vf scale=320:180 -y \"{$fullPath}\"";
            
            // Log the command for debugging
            Log::info("Running thumbnail generation with command: {$command}");
            
            $process = Process::timeout(30)->run($command);
            
            if ($process->successful()) {
                return $thumbnailPath;
            }
            
            // Log the error output if the command failed
            Log::error("Thumbnail generation failed: " . $process->errorOutput());
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error generating thumbnail: {$e->getMessage()}");
            
            return null;
        }
    }
    
    /**
     * Check the health of an input stream.
     */
    public function checkStreamHealth(InputStream $inputStream): array
    {
        try {
            // For ffprobe, we need to handle UDP options differently
            $inputUrl = $inputStream->processed_url ?? $inputStream->url;
            
            // Add authentication if provided
            if ($inputStream->username && $inputStream->password) {
                $protocol = parse_url($inputUrl, PHP_URL_SCHEME);
                $host = parse_url($inputUrl, PHP_URL_HOST);
                $path = parse_url($inputUrl, PHP_URL_PATH);
                $query = parse_url($inputUrl, PHP_URL_QUERY);
                
                $inputUrl = "{$protocol}://{$inputStream->username}:{$inputStream->password}@{$host}{$path}";
                if ($query) {
                    $inputUrl .= "?{$query}";
                }
            }
            
            // For UDP streams with program ID, we need to modify the URL
            if ($inputStream->protocol === 'udp' && !empty($inputStream->program_id)) {
                // Add program ID to the URL for ffprobe
                if (str_contains($inputUrl, '?')) {
                    $inputUrl .= "&program_id={$inputStream->program_id}";
                } else {
                    $inputUrl .= "?program_id={$inputStream->program_id}";
                }
            }
            
            // Build the base command with input
            $command = "ffprobe -i \"{$inputUrl}\"";
            
            // Add the standard probe options
            $command .= " -v error -show_entries stream=width,height,codec_name,bit_rate -show_entries format=duration,bit_rate -of json";
            
            // For display purposes, show what the ffmpeg command would be (with mapping options)
            $ffmpegCommand = "ffmpeg " . $this->getInputCommand($inputStream) . " [output options]";
            
            // Log the full command for debugging
            Log::info("Running stream health check with command: {$command}");
            
            $process = Process::run($command);
            
            if ($process->successful()) {
                $output = $process->output();
                $data = json_decode($output, true);
                
                $streamInfo = [
                    'status' => 'active',
                    'checked_at' => now()->toIso8601String(),
                    'command' => $command,
                    'ffmpeg_command' => $ffmpegCommand,
                ];
                
                if (isset($data['streams'][0])) {
                    $streamInfo['width'] = $data['streams'][0]['width'] ?? null;
                    $streamInfo['height'] = $data['streams'][0]['height'] ?? null;
                    $streamInfo['codec'] = $data['streams'][0]['codec_name'] ?? null;
                    $streamInfo['bitrate'] = $data['streams'][0]['bit_rate'] ?? ($data['format']['bit_rate'] ?? null);
                }
                
                if (isset($data['format']['duration'])) {
                    $streamInfo['duration'] = $data['format']['duration'];
                }
                
                return $streamInfo;
            }
            
            return [
                'status' => 'error',
                'checked_at' => now()->toIso8601String(),
                'error' => $process->errorOutput(),
                'command' => $command,
                'ffmpeg_command' => $ffmpegCommand,
            ];
        } catch (\Exception $e) {
            Log::error("Error checking stream health: {$e->getMessage()}");
            
            // Try to get the ffmpeg command
            $ffmpegCommand = "Unknown command";
            try {
                $ffmpegCommand = "ffmpeg " . $this->getInputCommand($inputStream) . " [output options]";
            } catch (\Exception $cmdEx) {
                // If we can't get the command, just use the exception message
                Log::error("Error getting FFmpeg command: {$cmdEx->getMessage()}");
            }
            
            return [
                'status' => 'error',
                'checked_at' => now()->toIso8601String(),
                'error' => $e->getMessage(),
                'command' => $command ?? 'Command generation failed',
                'ffmpeg_command' => $ffmpegCommand,
            ];
        }
    }
    
    /**
     * Scan for programs in a UDP stream.
     *
     * @param string $url The UDP stream URL
     * @param string|null $localAddress The local address to bind to
     * @return array An array of programs found in the stream
     */
    public function scanUdpPrograms(string $url, ?string $localAddress = null): array
    {
        try {
            // Make sure it's a UDP URL
            if (!str_starts_with($url, 'udp://')) {
                Log::warning("Attempted to scan non-UDP URL: {$url}");
                return ['error' => 'URL must be a UDP stream'];
            }
            
            // Add local address to URL if provided and not already in the URL
            if (!empty($localAddress) && !str_contains($url, 'localaddr=')) {
                $separator = str_contains($url, '?') ? '&' : '?';
                $url .= "{$separator}localaddr={$localAddress}";
                Log::info("Added local address to URL for scanning: {$url}");
            }
            
            // Try using a simple scan first (faster)
            $result = $this->quickUdpScan($url);
            if (!isset($result['error'])) {
                return $result;
            }
            
            Log::info("Quick UDP scan failed, trying detailed scan: " . ($result['error'] ?? 'Unknown error'));
            
            // Build the ffprobe command with additional options for faster scanning
            $command = [
                'ffprobe',
                '-v', 'quiet',
                '-print_format', 'json',
                '-probesize', '10M',            // Use larger probe size
                '-analyzeduration', '5000000',  // Analyze only 5 seconds
                '-select_streams', 'v:0',       // Only analyze first video stream
                '-show_streams',                // Show streams info instead of programs
                '-show_programs',               // Still get program info
                '-i', $url
            ];
            
            // Run the ffprobe command with a timeout
            $process = Process::timeout(30)->run($command);
            
            if (!$process->successful()) {
                Log::error("FFprobe program scan failed: " . $process->errorOutput());
                return ['error' => 'FFprobe scan failed: ' . $process->errorOutput()];
            }
            
            // Parse the output
            $output = $process->output();
            $programInfo = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse FFprobe JSON output: " . json_last_error_msg());
                return ['error' => 'Failed to parse FFprobe output'];
            }
            
            if (empty($programInfo) || empty($programInfo['programs'])) {
                return ['programs' => []];
            }
            
            // Format the results
            $result = [
                'programs' => []
            ];
            
            foreach ($programInfo['programs'] as $program) {
                $programData = [
                    'program_id' => $program['program_id'] ?? 'Unknown',
                    'program_name' => $program['tags']['service_name'] ?? null,
                    'num_streams' => count($program['streams'] ?? []),
                    'streams' => []
                ];
                
                // Add stream details
                if (!empty($program['streams'])) {
                    foreach ($program['streams'] as $stream) {
                        $streamType = $stream['codec_type'] ?? 'unknown';
                        $codecName = $stream['codec_name'] ?? 'unknown';
                        
                        $programData['streams'][] = [
                            'type' => $streamType,
                            'codec' => $codecName,
                            'index' => $stream['index'] ?? -1
                        ];
                    }
                }
                
                $result['programs'][] = $programData;
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Exception in scanUdpPrograms: " . $e->getMessage());
            return ['error' => 'Exception: ' . $e->getMessage()];
        }
    }
    
    /**
     * Perform a quick UDP scan that's less likely to time out
     */
    private function quickUdpScan(string $url): array
    {
        try {
            // Build a simpler command that's less likely to time out but still gets program info
            $command = [
                'ffprobe',
                '-v', 'quiet',
                '-print_format', 'json',
                '-show_programs',
                '-i', $url
            ];
            
            // Use a shorter timeout for the quick scan
            $process = Process::timeout(15)->run($command);
            
            if (!$process->successful()) {
                return ['error' => 'Quick scan failed'];
            }
            
            $output = $process->output();
            $info = json_decode($output, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || empty($info['programs'])) {
                // If no programs were found, try a streams-only scan as fallback
                return ['error' => 'No program information found in quick scan'];
            }
            
            // Format the results
            $result = [
                'programs' => []
            ];
            
            foreach ($info['programs'] as $program) {
                $programData = [
                    'program_id' => $program['program_id'] ?? 'Unknown',
                    'program_name' => $program['tags']['service_name'] ?? null,
                    'num_streams' => count($program['streams'] ?? []),
                    'streams' => []
                ];
                
                // Add stream details
                if (!empty($program['streams'])) {
                    foreach ($program['streams'] as $stream) {
                        $streamType = $stream['codec_type'] ?? 'unknown';
                        $codecName = $stream['codec_name'] ?? 'unknown';
                        
                        $programData['streams'][] = [
                            'type' => $streamType,
                            'codec' => $codecName,
                            'index' => $stream['index'] ?? -1
                        ];
                    }
                }
                
                $result['programs'][] = $programData;
            }
            
            return $result;
            
        } catch (\Exception $e) {
            return ['error' => 'Exception in quick scan: ' . $e->getMessage()];
        }
    }
    
    /**
     * Create a default program structure from stream information
     * (Only used as a fallback when no program information is available)
     */
    private function createDefaultProgramFromStreams(array $streams): array
    {
        $streamsList = [];
        foreach ($streams as $stream) {
            $streamType = $stream['codec_type'] ?? 'unknown';
            $codecName = $stream['codec_name'] ?? 'unknown';
            
            $streamsList[] = [
                'type' => $streamType,
                'codec' => $codecName,
                'index' => $stream['index'] ?? -1
            ];
        }
        
        return [
            'programs' => [
                [
                    'program_id' => '0',  // Default program ID
                    'num_streams' => count($streams),
                    'streams' => $streamsList
                ]
            ]
        ];
    }
    
    /**
     * Get a list of all output streams with their status.
     * 
     * @return array List of streams with their details
     */
    public function getActiveOutputStreams(): array
    {
        try {
            // Get all output streams from the database, not just active ones
            $streams = OutputStream::latest()->get();
            
            $result = [];
            foreach ($streams as $stream) {
                // Get metadata
                $metadata = $stream->metadata;
                if (!is_array($metadata)) {
                    $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $metadata = [];
                    }
                }
                
                // Get process ID
                $processId = $metadata['process_id'] ?? null;
                $isRunning = false;
                $processInfo = [];
                
                // Check if the process is actually running
                if ($processId) {
                    exec("ps -p {$processId} -o pid,cmd= 2>&1", $processOutput, $exitCode);
                    $isRunning = ($exitCode === 0);
                    $processInfo = $isRunning ? $processOutput : [];
                    
                    // If not running by direct PID, check by output URL
                    if (!$isRunning && !empty($stream->processed_url)) {
                        $escapedUrl = escapeshellarg($stream->processed_url);
                        exec("ps aux | grep ffmpeg | grep {$escapedUrl} | grep -v grep", $urlProcessOutput);
                        $isRunning = !empty($urlProcessOutput);
                        $processInfo = $isRunning ? $urlProcessOutput : [];
                    }
                }
                
                // Get output directory stats for HLS
                $outputStats = [];
                if ($stream->protocol === 'hls') {
                    $outputDir = storage_path("app/public/streams/{$stream->id}");
                    if (file_exists($outputDir)) {
                        $segments = glob("{$outputDir}/segment_*.ts");
                        $outputStats = [
                            'directory' => $outputDir,
                            'segment_count' => count($segments),
                            'latest_segment' => !empty($segments) ? basename(end($segments)) : null,
                            'playlist_exists' => file_exists("{$outputDir}/playlist.m3u8"),
                            'last_modified' => !empty($segments) ? date("Y-m-d H:i:s", filemtime(end($segments))) : null
                        ];
                    }
                }
                
                // Add to result
                $result[] = [
                    'id' => $stream->id,
                    'name' => $stream->name,
                    'url' => $stream->processed_url,
                    'protocol' => $stream->protocol,
                    'status' => [
                        'database' => $stream->status,
                        'process_running' => $isRunning
                    ],
                    'process' => [
                        'id' => $processId,
                        'info' => $processInfo
                    ],
                    'created_at' => $stream->created_at->toIso8601String(),
                    'output_stats' => $outputStats
                ];
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error("Error getting output streams: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Generate FFmpeg command for a single input stream.
     */
    public function generateSingleInputCommand(MultiviewLayout $layout, OutputStream $outputStream, InputStream $inputStream, int $positionIndex): string
    {
        // Special case for direct protocol
        if ($outputStream->protocol === 'direct') {
            $inputUrl = $inputStream->processed_url ?? $inputStream->url;
            $outputUrl = $outputStream->processed_url;
            
            // If using the exact same URL, just return a simple redirect command
            if ($inputUrl === $outputUrl) {
                return "ffmpeg -i \"{$inputUrl}\" -c copy -f mpegts \"{$outputUrl}\"";
            }
        }
        
        // Base command
        $command = 'ffmpeg ';
        
        // Add input stream
        $inputUrl = $inputStream->processed_url ?? $inputStream->url;
        
        // Add protocol-specific options for input
        $options = '';
        switch ($inputStream->protocol) {
            case 'rtsp':
                $options = '-rtsp_transport tcp -i';
                break;
            case 'udp':
                $options = '-thread_queue_size 1024 -fflags +nobuffer+genpts -i';
                break;
            case 'hls':
                $options = '-i';
                break;
            default:
                $options = '-i';
                break;
        }
        
        // Add authentication if provided
        if ($inputStream->username && $inputStream->password) {
            $protocol = parse_url($inputUrl, PHP_URL_SCHEME);
            $host = parse_url($inputUrl, PHP_URL_HOST);
            $path = parse_url($inputUrl, PHP_URL_PATH);
            $query = parse_url($inputUrl, PHP_URL_QUERY);
            
            $inputUrl = "{$protocol}://{$inputStream->username}:{$inputStream->password}@{$host}{$path}";
            if ($query) {
                $inputUrl .= "?{$query}";
            }
        }
        
        $command .= "{$options} \"{$inputUrl}\" ";
        
        // Add post-input options
        if ($inputStream->protocol === 'udp') {
            // Add program mapping if specified
            if (!empty($inputStream->program_id)) {
                $command .= " -map 0:p:{$inputStream->program_id}";
            }
            
            // Add ignore_unknown option if enabled
            if (isset($inputStream->ignore_unknown) && $inputStream->ignore_unknown) {
                $command .= ' -ignore_unknown';
            }
            
            // Add map -d option if enabled (disable data streams)
            if (isset($inputStream->map_disable_data) && $inputStream->map_disable_data) {
                $command .= " -map -0:d";
            }
            
            // Add map -s option if enabled (disable subtitle streams)
            if (isset($inputStream->map_disable_subtitles) && $inputStream->map_disable_subtitles) {
                $command .= " -map -0:s";
            }
            
            // Add any additional options
            if (!empty($inputStream->additional_options) && is_array($inputStream->additional_options)) {
                foreach ($inputStream->additional_options as $key => $value) {
                    if (!empty($key)) {
                        $command .= " {$key}";
                        if (!empty($value)) {
                            $command .= " {$value}";
                        }
                    }
                }
            }
        }
        
        // Add video codec and other output options
        $command .= " -c:v libx264 -preset veryfast -g 30";
        
        // Add output options based on protocol
        switch ($outputStream->protocol) {
            case 'hls':
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                if (!file_exists($outputDir)) {
                    mkdir($outputDir, 0755, true);
                }
                
                $command .= " -sc_threshold 0 -f hls -hls_time 2 -hls_list_size 6 -hls_flags delete_segments+append_list+program_date_time -hls_segment_type mpegts -hls_allow_cache 0 -start_number 0 -hls_segment_filename \"{$outputDir}/segment_%03d.ts\" \"{$outputDir}/playlist.m3u8\"";
                break;
            case 'rtsp':
                $command .= " -f rtsp {$outputStream->processed_url}";
                break;
            case 'udp':
                // Append UDP-specific parameters if not already present
                $url = $outputStream->processed_url;
                if (strpos($url, '?') === false) {
                    $url .= '?ttl=2&pkt_size=1316';
                } elseif (!strpos($url, 'pkt_size=')) {
                    $url .= '&ttl=2&pkt_size=1316';
                }
                
                $command .= " -f mpegts -flush_packets 1 \"{$url}\"";
                break;
            case 'dash':
                $outputDir = storage_path("app/public/streams/{$outputStream->id}");
                if (!file_exists($outputDir)) {
                    mkdir($outputDir, 0755, true);
                }
                
                $command .= " -sc_threshold 0 -f dash -use_timeline 1 -use_template 1 -window_size 5 -extra_window_size 10 -remove_at_exit 0 \"{$outputDir}/manifest.mpd\"";
                break;
            default:
                $command .= " -f flv {$outputStream->processed_url}";
                break;
        }
        
        // Add custom FFmpeg options if provided
        if ($outputStream->ffmpeg_options) {
            $customOptions = $outputStream->ffmpeg_options;
            if (is_array($customOptions) && !empty($customOptions)) {
                foreach ($customOptions as $option => $value) {
                    // Skip options we've already set
                    if (in_array($option, ['-c:v', '-preset', '-g', '-f'])) {
                        continue;
                    }
                    $command .= " {$option} {$value}";
                }
            }
        }
        
        return $command;
    }
    
    /**
     * Start a stream with a single input.
     */
    public function startSingleInput(MultiviewLayout $layout, OutputStream $outputStream, InputStream $inputStream, int $positionIndex): bool
    {
        try {
            // Ensure storage directory exists if using a storage path
            if (strpos($outputStream->url, '{storage_path}') !== false) {
                $outputStream->ensureStorageDirectoryExists();
                Log::info("Ensured storage directory exists for output stream ID {$outputStream->id}");
            }
            
            // Check if this is a direct protocol with matching URLs
            $isDirect = false;
            if ($outputStream->protocol === 'direct') {
                $inputUrl = $inputStream->processed_url ?? $inputStream->url;
                $outputUrl = $outputStream->processed_url;
                
                if ($inputUrl === $outputUrl) {
                    $isDirect = true;
                    // For true direct streaming, we don't actually start a process
                    // Just update metadata and mark as active
                    Log::info("Starting direct stream passthrough: {$inputUrl}");
                    
                    // Store the input information
                    $outputStream->update([
                        'metadata' => [
                            'direct_passthrough' => true,
                            'input_stream_id' => $inputStream->id,
                            'position_index' => $positionIndex,
                            'input_url' => $inputUrl
                        ],
                        'status' => 'active',
                    ]);
                    
                    return true;
                }
            }
            
            // For all other cases, generate and run FFmpeg command
            if (!$isDirect) {
                $command = $this->generateSingleInputCommand($layout, $outputStream, $inputStream, $positionIndex);
                
                // Log the command for debugging
                Log::info("Starting FFmpeg single input process with command: {$command}");
                
                // Start the process in the background
                $process = Process::start($command);
                
                // Ensure metadata is an array before merging
                $metadata = $outputStream->metadata;
                if (!is_array($metadata)) {
                    $metadata = !empty($metadata) ? json_decode($metadata, true) : [];
                    // If JSON decoding fails, just create an empty array
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $metadata = [];
                    }
                }
                
                // Store the process ID and input information
                $outputStream->update([
                    'metadata' => array_merge($metadata, [
                        'process_id' => $process->id(),
                        'single_input' => true,
                        'input_stream_id' => $inputStream->id,
                        'position_index' => $positionIndex
                    ]),
                    'status' => 'active',
                ]);
                
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error starting single input process: {$e->getMessage()}");
            
            $outputStream->update([
                'status' => 'error',
                'error_log' => array_merge($outputStream->error_log ?? [], [
                    'timestamp' => now()->toIso8601String(),
                    'message' => $e->getMessage(),
                ]),
            ]);
            
            return false;
        }
    }
} 