<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use GuzzleHttp\Client;

class StreamProxyController extends Controller
{
    /**
     * Proxy an HLS playlist
     */
    public function proxyPlaylist($streamId)
    {
        // Handle OPTIONS request for CORS preflight
        if (request()->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }
        
        // Record the stream request details
        Log::info("Stream playlist request received", [
            'streamId' => $streamId,
            'userAgent' => request()->header('User-Agent'),
            'referrer' => request()->header('Referer'),
            'ip' => request()->ip()
        ]);
        
        // Try to find the stream (can be either output or input stream)
        $outputStream = \App\Models\OutputStream::find($streamId);
        $inputStream = null;
        
        if (!$outputStream) {
            $inputStream = \App\Models\InputStream::find($streamId);
        }
        
        if (!$outputStream && !$inputStream) {
            Log::warning("Stream not found", ['streamId' => $streamId]);
            return $this->addCorsHeaders(response()->json(['error' => 'Stream not found'], 404));
        }
        
        // Get path to playlist file
        $streamDir = storage_path("app/public/streams/{$streamId}");
        $playlistPath = $streamDir . '/playlist.m3u8';
        
        // Check if the playlist file exists
        if (file_exists($playlistPath) && is_readable($playlistPath)) {
            // Get the original content
            $originalContent = file_get_contents($playlistPath);
            
            // Replace direct segment URLs with proxy URLs
            $proxyBaseUrl = url("/stream-proxy/{$streamId}/");
            $storageBaseUrl = url("/storage/streams/{$streamId}/");
            
            // Simple string replacement for .ts files, preserving all tags
            $modifiedContent = str_replace(
                $storageBaseUrl, 
                $proxyBaseUrl, 
                $originalContent
            );
            
            // Replace relative segment URLs with proxy URLs, ensuring proper format with slash
            $modifiedContent = preg_replace(
                '/^(segment_\d+\.ts)/m', 
                $proxyBaseUrl . 'segment/$1', 
                $modifiedContent
            );
            
            // Fix URLs with missing slash between streamId and segment
            $incorrectPattern = url("/stream-proxy/{$streamId}segment/");
            $correctPattern = url("/stream-proxy/{$streamId}/segment/");
            $modifiedContent = str_replace($incorrectPattern, $correctPattern, $modifiedContent);
            
            // Return the modified playlist
            return $this->addCorsHeaders(response($modifiedContent, 200, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Cache-Control' => 'must-revalidate, no-cache, no-store, private'
            ]));
        }
        
        // If the playlist doesn't exist, create the directory if needed
        if (!file_exists($streamDir)) {
            try {
                mkdir($streamDir, 0755, true);
            } catch (\Exception $e) {
                Log::error("Failed to create stream directory", [
                    'streamDir' => $streamDir,
                    'error' => $e->getMessage()
                ]);
                return $this->addCorsHeaders(response()->json(['error' => 'Failed to create stream directory'], 500));
            }
        }
        
        // No playlist found
        return $this->addCorsHeaders(response()->json(['error' => 'Playlist not found'], 404));
    }
    
    /**
     * Check if content appears to be a valid M3U8 playlist
     */
    private function isValidM3u8($content) {
        if (empty($content)) {
            return false;
        }
        
        // Basic validation - check for essential HLS tags
        if (strpos($content, '#EXTM3U') !== 0) {
            return false;
        }
        
        // Additional validation
        if (strpos($content, '#EXT-X-VERSION') === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Send a properly formatted playlist response
     */
    private function sendPlaylistResponse($content) {
        return response($content, 200, [
            'Content-Type' => 'application/vnd.apple.mpegurl',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range',
            'Cache-Control' => 'no-cache',
            'Content-Length' => strlen($content)
        ]);
    }
    
    /**
     * Generate an empty playlist with an informational message
     */
    private function generateEmptyPlaylist($message = "Stream unavailable") {
        Log::info("Generating empty playlist with message: " . $message);
        
        // Create a minimal, standards-compliant playlist
        $errorPlaylist = "#EXTM3U\n";
        $errorPlaylist .= "#EXT-X-VERSION:3\n";
        $errorPlaylist .= "#EXT-X-TARGETDURATION:2\n";
        $errorPlaylist .= "#EXT-X-MEDIA-SEQUENCE:0\n";
        $errorPlaylist .= "#EXTINF:2.0,\n";
        
        // Use the actual stream ID from the request if available
        $streamId = request()->route('streamId') ?? 0;
        $emptySegmentUrl = url("/stream-proxy/{$streamId}/empty.ts");
        
        $errorPlaylist .= "{$emptySegmentUrl}\n";
        $errorPlaylist .= "#EXT-X-ENDLIST\n";
        
        return $this->sendPlaylistResponse($errorPlaylist);
    }
    
    /**
     * Generate a playlist from segment files
     */
    private function generatePlaylistFromSegments($streamDir, $segmentFiles) {
        Log::info("Generating playlist from segments", [
            'streamDir' => $streamDir,
            'segmentCount' => count($segmentFiles)
        ]);
        
        // Sort by segment number
        usort($segmentFiles, function($a, $b) {
            preg_match('/segment_?(\d+)\.ts$/', basename($a), $matchesA);
            preg_match('/segment_?(\d+)\.ts$/', basename($b), $matchesB);
            
            $numA = isset($matchesA[1]) ? (int)$matchesA[1] : 0;
            $numB = isset($matchesB[1]) ? (int)$matchesB[1] : 0;
            
            return $numA - $numB;
        });
        
        // Get segments in order
        $segmentNumbers = [];
        foreach ($segmentFiles as $file) {
            preg_match('/segment_?(\d+)\.ts$/', basename($file), $matches);
            if (isset($matches[1])) {
                $segmentNumbers[] = (int)$matches[1];
            }
        }
        
        if (empty($segmentNumbers)) {
            Log::warning("No valid segment numbers found");
            return $this->generateEmptyPlaylist("No valid segments found");
        }
        
        // Create playlist
        $playlist = "#EXTM3U\n";
        $playlist .= "#EXT-X-VERSION:3\n";
        $playlist .= "#EXT-X-ALLOW-CACHE:NO\n";
        $playlist .= "#EXT-X-TARGETDURATION:2\n";
        $playlist .= "#EXT-X-MEDIA-SEQUENCE:{$segmentNumbers[0]}\n";
        
        // Get the stream ID from the directory path
        $stream_id = basename(dirname($streamDir));
        $proxyUrl = url("/stream-proxy/{$stream_id}/segment/");
        if (!str_ends_with($proxyUrl, '/')) {
            $proxyUrl .= '/';
        }
        
        // Calculate a timestamp for program date time tags
        $timestamp = time();
        $timestampIncrement = 2.0; // Default segment duration
        
        // Include all segments, but limit to the last 10 to avoid overwhelming the player
        $maxSegments = 10;
        $segmentsToInclude = count($segmentNumbers) > $maxSegments 
            ? array_slice($segmentNumbers, -$maxSegments) 
            : $segmentNumbers;
        
        foreach ($segmentsToInclude as $segmentNumber) {
            // Calculate timestamp for this segment
            $formattedDate = gmdate("Y-m-d\TH:i:s", $timestamp) . '.000Z';
            
            // Add segment info
            $playlist .= "#EXTINF:2.0,\n";
            $playlist .= "#EXT-X-PROGRAM-DATE-TIME:{$formattedDate}\n";
            $playlist .= "{$proxyUrl}{$segmentNumber}.ts\n";
            
            // Increment timestamp for next segment
            $timestamp += $timestampIncrement;
        }
        
        // Save the generated playlist
        file_put_contents("{$streamDir}/playlist.m3u8", $playlist);
        
        Log::info("Generated playlist from segments", [
            'segmentCount' => count($segmentsToInclude),
            'firstSegment' => reset($segmentsToInclude),
            'lastSegment' => end($segmentsToInclude)
        ]);
        
        return $playlist;
    }
    
    /**
     * Preload the next segment to help prevent buffer stalls
     * 
     * @param string $streamDir Directory containing the segments
     * @param int $currentSegment Current segment number
     * @return void
     */
    private function preloadNextSegment($streamDir, $currentSegment)
    {
        $nextSegment = $currentSegment + 1;
        
        // Check if next segment exists
        $nextSegmentPatterns = [
            "{$streamDir}/segment_{$nextSegment}.ts",
            "{$streamDir}/segment{$nextSegment}.ts",
            "{$streamDir}/{$nextSegment}.ts"
        ];
        
        foreach ($nextSegmentPatterns as $pattern) {
            if (file_exists($pattern)) {
                Log::debug("Next segment already exists, no preloading needed", [
                    'nextSegment' => $nextSegment,
                    'path' => $pattern
                ]);
                return;
            }
        }
        
        // No next segment found, start async preload job for the next segment
        // This is a non-blocking operation that runs in the background
        try {
            Log::info("Starting async job to preload next segment", [
                'currentSegment' => $currentSegment,
                'nextSegment' => $nextSegment
            ]);
            
            // You could dispatch a Laravel job here to fetch/generate the next segment
            // For now, we'll just log that it should happen
            // \App\Jobs\PreloadSegment::dispatch($streamDir, $nextSegment);
        } catch (\Exception $e) {
            Log::warning("Failed to start preload job", [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Proxy an HLS segment file
     */
    public function proxySegment($streamId, $segmentNumber)
    {
        // Handle OPTIONS request for CORS preflight
        if (request()->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range')
                ->header('Access-Control-Max-Age', '86400'); // 24 hours
        }
        
        // Handle new URL format with "segment/segment_123.ts"
        if (strpos($segmentNumber, 'segment_') === 0) {
            $segmentNumber = str_replace('segment_', '', $segmentNumber);
        }
        
        // Clean up segment number by removing file extension if present
        if (str_ends_with($segmentNumber, '.ts')) {
            $segmentNumber = substr($segmentNumber, 0, -3); // Remove .ts extension
        }
        
        Log::info("Segment request received", [
            'streamId' => $streamId,
            'segmentNumber' => $segmentNumber,
            'userAgent' => request()->header('User-Agent'),
            'ip' => request()->ip()
        ]);
        
        // Get the base storage path for this stream
            $streamDir = storage_path("app/public/streams/{$streamId}");
        
        // Define possible segment file patterns
        $segmentPatterns = [
            "{$streamDir}/segment_{$segmentNumber}.ts",
            "{$streamDir}/segment{$segmentNumber}.ts",
            "{$streamDir}/{$segmentNumber}.ts"
        ];
        
        // Also check for segments with zero-padded numbers
        for ($i = 1; $i <= 5; $i++) {
            $paddedNumber = str_pad($segmentNumber, $i, '0', STR_PAD_LEFT);
            if ($paddedNumber !== (string)$segmentNumber) {
                $segmentPatterns[] = "{$streamDir}/segment_{$paddedNumber}.ts";
                $segmentPatterns[] = "{$streamDir}/segment{$paddedNumber}.ts";
                $segmentPatterns[] = "{$streamDir}/{$paddedNumber}.ts";
            }
        }
        
        // Check if the segment exists in any of the possible patterns
        foreach ($segmentPatterns as $segmentPath) {
            if (file_exists($segmentPath)) {
                Log::info("Serving segment", [
            'streamId' => $streamId,
            'segmentNumber' => $segmentNumber,
                    'path' => $segmentPath,
                    'size' => filesize($segmentPath)
                ]);
                
                // Return the segment file with appropriate headers
                return response()->file($segmentPath, [
                    'Content-Type' => 'video/MP2T',
                    'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range'
                ]);
            }
        }
        
        // If we can't find the exact segment, find the most recent one
        $availableSegments = glob("{$streamDir}/segment_*.ts");
        if (empty($availableSegments)) {
            $availableSegments = glob("{$streamDir}/segment*.ts");
        }
        if (empty($availableSegments)) {
            $availableSegments = glob("{$streamDir}/*.ts");
        }
        
        if (!empty($availableSegments)) {
            // Extract segment numbers and sort them
            $segmentFiles = [];
            foreach ($availableSegments as $segment) {
                if (preg_match('/segment_(\d+)\.ts$/', $segment, $matches)) {
                    $num = (int)$matches[1];
                    $segmentFiles[$num] = $segment;
                } else if (preg_match('/segment(\d+)\.ts$/', $segment, $matches)) {
                    $num = (int)$matches[1];
                    $segmentFiles[$num] = $segment;
                } else if (preg_match('/(\d+)\.ts$/', $segment, $matches)) {
                    $num = (int)$matches[1];
                    $segmentFiles[$num] = $segment;
                }
            }
            
            if (!empty($segmentFiles)) {
                // Get keys (segment numbers) and sort them
                $segmentNumbers = array_keys($segmentFiles);
                sort($segmentNumbers);
                
                // If the requested segment is higher than our highest, serve the highest
                $requestedSegment = (int)$segmentNumber;
                $highestSegment = end($segmentNumbers);
                
                if ($requestedSegment >= $highestSegment) {
                    $segmentPath = $segmentFiles[$highestSegment];
                    Log::info("Requested segment is higher, serving highest available", [
                        'requested' => $requestedSegment,
                        'serving' => $highestSegment,
                        'path' => $segmentPath
                    ]);
                    
                    return response()->file($segmentPath, [
                        'Content-Type' => 'video/MP2T',
                        'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
                        'Access-Control-Allow-Origin' => '*'
                    ]);
                }
                
                // If the requested segment is lower than our lowest, serve the lowest
                $lowestSegment = reset($segmentNumbers);
                if ($requestedSegment <= $lowestSegment) {
                    $segmentPath = $segmentFiles[$lowestSegment];
                    Log::info("Requested segment is lower, serving lowest available", [
                        'requested' => $requestedSegment,
                        'serving' => $lowestSegment,
                        'path' => $segmentPath
                    ]);
                    
                    return response()->file($segmentPath, [
                        'Content-Type' => 'video/MP2T',
                        'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
                        'Access-Control-Allow-Origin' => '*'
                    ]);
                }
                
                // Otherwise, find the closest segment number
                $closest = null;
                $closestDistance = PHP_INT_MAX;
                
                foreach ($segmentNumbers as $num) {
                    $distance = abs($num - $requestedSegment);
                    if ($distance < $closestDistance) {
                        $closest = $num;
                        $closestDistance = $distance;
                    }
                }
                
                if ($closest !== null) {
                    $segmentPath = $segmentFiles[$closest];
                    Log::info("Serving closest segment", [
                        'requested' => $requestedSegment,
                        'serving' => $closest,
                        'distance' => $closestDistance,
                        'path' => $segmentPath
                    ]);
                    
                    return response()->file($segmentPath, [
                        'Content-Type' => 'video/MP2T',
                        'Cache-Control' => 'must-revalidate, no-cache, no-store, private',
                        'Access-Control-Allow-Origin' => '*'
                    ]);
                }
            }
        }
        
        // If we still can't find a segment, serve an empty one
        Log::warning("Segment not found", [
            'streamId' => $streamId,
            'segmentNumber' => $segmentNumber
        ]);
        
        // Create and serve an empty segment as a fallback
        $emptyData = str_repeat(chr(0), 188); // Minimal TS packet size
        return response($emptyData, 404, [
            'Content-Type' => 'video/MP2T',
            'Content-Length' => strlen($emptyData),
            'Cache-Control' => 'no-cache, must-revalidate',
            'Access-Control-Allow-Origin' => '*'
        ]);
    }
    
    /**
     * Proxy a DASH manifest file (.mpd)
     */
    public function proxyDashManifest($streamId)
    {
        $basePath = storage_path("app/public/streams/{$streamId}");
        $manifestPath = "{$basePath}/manifest.mpd";
        
        Log::info("Stream proxy DASH manifest request", [
            'streamId' => $streamId,
            'manifestPath' => $manifestPath
        ]);

        if (!file_exists($manifestPath)) {
            Log::warning("DASH manifest file not found", [
                'streamId' => $streamId,
                'manifestPath' => $manifestPath
            ]);
            return response()->json(['error' => 'DASH manifest not found'], 404);
        }

        // Read the manifest file
        $manifest = file_get_contents($manifestPath);
        
        // Base URL for the segments
        $proxyBaseUrl = url("/stream-proxy/{$streamId}/dash/");
        if (!str_ends_with($proxyBaseUrl, '/')) {
            $proxyBaseUrl .= '/';
        }
        
        // Check if initialization segments exist
        $initSegments = glob("{$basePath}/init-stream*.m4s");
        $mediaSegments = glob("{$basePath}/chunk-stream*.m4s");
        
        Log::info("DASH stream files", [
            'streamId' => $streamId,
            'initSegmentsCount' => count($initSegments),
            'mediaSegmentsCount' => count($mediaSegments)
        ]);
        
        // Find the SegmentTemplate elements and update them
        // We need to use DOMDocument to properly parse and modify the XML
        $tempFile = tempnam(sys_get_temp_dir(), 'dash_');
        file_put_contents($tempFile, $manifest);
        
        // Create a copy of the original manifest to work with
        $rewrittenManifest = $manifest;
        
        // Using regex to find and replace initialization and media templates
        // This handles both standard attributes and template variables
        
        // Replace initialization templates: initialization="init-stream$RepresentationID$.m4s"
        $rewrittenManifest = preg_replace(
            '/initialization="([^"]+)"/i',
            'initialization="' . $proxyBaseUrl . '$1"',
            $rewrittenManifest
        );
        
        // Replace media templates: media="chunk-stream$RepresentationID$-$Number%05d$.m4s"
        $rewrittenManifest = preg_replace(
            '/media="([^"]+)"/i',
            'media="' . $proxyBaseUrl . '$1"',
            $rewrittenManifest
        );
        
        // Handle any BaseURL elements that might be in the manifest
        $rewrittenManifest = preg_replace(
            '/<BaseURL>([^<]+)<\/BaseURL>/i',
            '<BaseURL>' . $proxyBaseUrl . '</BaseURL>',
            $rewrittenManifest
        );
        
        // Clean up the temp file
        @unlink($tempFile);
        
        Log::info("Serving rewritten DASH manifest", [
            'streamId' => $streamId,
            'sample' => substr($rewrittenManifest, 0, 200),
            'proxyBaseUrl' => $proxyBaseUrl
        ]);
        
        return response($rewrittenManifest, 200, [
            'Content-Type' => 'application/dash+xml',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }
    
    /**
     * Proxy a DASH media segment file (.m4s)
     */
    public function proxyDashSegment($streamId, $segmentPath)
    {
        $basePath = storage_path("app/public/streams/{$streamId}");
        
        // Clean up the segment path to handle any URL encodings or template variables
        $segmentPath = rawurldecode($segmentPath);
        
        // Log the original request
        Log::info("Stream proxy DASH segment request", [
            'streamId' => $streamId,
            'originalSegmentPath' => $segmentPath
        ]);
        
        // Handle template substitution if needed (in case the player sends the template variables)
        $segmentPath = preg_replace('/\$RepresentationID\$/', '*', $segmentPath);
        $segmentPath = preg_replace('/\$Number(%[^$]+)?\$/', '*', $segmentPath);
        
        // Check if the path contains wildcards and use glob to find matching files
        if (strpos($segmentPath, '*') !== false) {
            $files = glob("{$basePath}/{$segmentPath}");
            
            // Sort files by modification time and get the latest one
            if (!empty($files)) {
                usort($files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $fullPath = $files[0];
            } else {
                $fullPath = "{$basePath}/{$segmentPath}";
            }
        } else {
            $fullPath = "{$basePath}/{$segmentPath}";
        }
        
        Log::info("Resolved segment path", [
            'streamId' => $streamId,
            'originalSegmentPath' => $segmentPath,
            'resolvedPath' => $fullPath
        ]);
        
        if (!file_exists($fullPath)) {
            // If exact file doesn't exist, try to find a similar file
            $pattern = preg_replace('/[0-9]+/', '*', $segmentPath);
            $candidates = glob("{$basePath}/{$pattern}");
            
            if (!empty($candidates)) {
                // Sort by name to get the most recent one
                sort($candidates);
                $fullPath = end($candidates);
                
                Log::info("Found alternative segment", [
                    'streamId' => $streamId,
                    'originalSegmentPath' => $segmentPath,
                    'alternativePath' => $fullPath
                ]);
            } else {
                Log::warning("DASH segment file not found", [
                    'streamId' => $streamId,
                    'segmentPath' => $segmentPath,
                    'pattern' => $pattern
                ]);
                return response()->json(['error' => 'DASH segment not found'], 404);
            }
        }
        
        // Determine the content type based on file extension
        $contentType = 'video/mp4';
        if (str_ends_with($fullPath, '.m4s')) {
            $contentType = 'video/iso.segment';
        } elseif (str_ends_with($fullPath, '.mp4')) {
            $contentType = 'video/mp4';
        }
        
        Log::info("Serving DASH segment", [
            'streamId' => $streamId,
            'segmentPath' => $segmentPath,
            'fullPath' => $fullPath,
            'contentType' => $contentType
        ]);
        
        return response()->stream(
            function() use ($fullPath) {
                echo file_get_contents($fullPath);
            },
            200,
            [
                'Content-Type' => $contentType,
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With',
                'Cache-Control' => 'public, max-age=3600',
            ]
        );
    }
    
    /**
     * Proxy a remote HLS playlist
     */
    private function proxyRemotePlaylist($url)
    {
        Log::info("Proxying remote playlist", [
            'url' => $url
        ]);
        
        // Add cache busting to make sure we get the latest version
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $url = $url . $separator . '_=' . time();
        
        // Get the remote playlist
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get($url);
            $body = (string) $response->getBody();
            
            return response($body, 200, [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        } catch (\Exception $e) {
            Log::error("Error proxying remote playlist", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error proxying remote playlist: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Proxy a remote HLS segment
     */
    private function proxyRemoteSegment($baseUrl, $segmentId)
    {
        // Construct the segment URL
        $segmentUrl = rtrim($baseUrl, '/') . '/' . $segmentId;
        
        Log::info("Proxying remote segment", [
            'baseUrl' => $baseUrl,
            'segmentId' => $segmentId,
            'segmentUrl' => $segmentUrl
        ]);
        
        // Get the remote segment
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->get($segmentUrl);
            $body = (string) $response->getBody();
            
            return response($body, 200, [
                'Content-Type' => 'video/mp2t',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (\Exception $e) {
            Log::error("Error proxying remote segment", [
                'segmentUrl' => $segmentUrl,
                'error' => $e->getMessage()
            ]);
            
            // Return an empty segment as a fallback
            return $this->createEmptySegment();
        }
    }
    
    /**
     * Create an empty MPEG-TS segment as a fallback
     */
    private function createEmptySegment()
    {
        // Log the creation of an empty segment
        Log::info("Creating empty segment as fallback");
        
        // Hard-coded minimal MPEG-TS segment data (188 bytes)
        $data = $this->createHardcodedEmptySegment();
        
        return response($data, 200, [
            'Content-Type' => 'video/MP2T',
            'Content-Length' => strlen($data),
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range',
            'Cache-Control' => 'no-cache, must-revalidate'
        ]);
    }

    /**
     * Create a hardcoded empty MPEG-TS segment as a last resort fallback
     */
    private function createHardcodedEmptySegment()
    {
        Log::info("Using hardcoded empty segment as fallback");
        
        // A more robust empty MPEG-TS segment with proper headers
        // This starts with a Transport Stream header (sync byte 0x47)
        // followed by proper packet headers with adaptation fields
        $tsHeader = chr(0x47) . chr(0x40) . chr(0x00) . chr(0x10) . chr(0x00);
        $adaptationField = chr(0xB7) . str_repeat(chr(0xFF), 183); // Adaptation field with padding
        
        // Create a basic PAT and PMT structure
        $pat = chr(0x47) . chr(0x40) . chr(0x00) . chr(0x10) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) . 
               chr(0x00) . chr(0x00) . chr(0x01) . chr(0xF0) . chr(0x00);
        $pmt = chr(0x47) . chr(0x50) . chr(0x00) . chr(0x10) . chr(0x00) . chr(0x02) . chr(0xB0) . chr(0x17) . 
              chr(0x00) . chr(0x01) . chr(0xC1) . chr(0x00) . chr(0x00) . chr(0xE1) . chr(0x00) . 
              chr(0xF0) . chr(0x00);
              
        // Generate multiple null packets to create a valid segment
        $nullPackets = str_repeat($tsHeader . $adaptationField, 20);
        
        // Combine all elements into a complete TS segment
        $completeSegment = $pat . $pmt . $nullPackets;
        
        Log::debug("Created hardcoded empty segment", [
            'size' => strlen($completeSegment),
            'firstBytes' => bin2hex(substr($completeSegment, 0, 10))
        ]);
        
        return $completeSegment;
    }
    
    /**
     * Parse custom URL to check if it references another stream
     * 
     * @param string $url The custom URL to parse
     * @param int $streamId The original stream ID
     * @param int|null &$customStreamId Will be set to the custom stream ID if found
     * @param bool &$customUrlIsLocal Will be set to true if the URL is local
     * @return void
     */
    private function parseCustomStreamUrl($url, $streamId, &$customStreamId, &$customUrlIsLocal)
    {
        // Check if it's a local path pointing to another stream
        if (preg_match('#/streams/(\d+)/playlist\.m3u8$#', $url, $matches)) {
            $customStreamId = $matches[1];
            $customUrlIsLocal = true;
            Log::info("Stream has a custom URL pointing to another stream", [
                'streamId' => $streamId,
                'customStreamId' => $customStreamId,
                'customUrl' => $url
            ]);
        }
        // Also check for absolute paths to the storage folder
        else if (preg_match('#/storage/app/public/streams/(\d+)/playlist\.m3u8$#', $url, $matches)) {
            $customStreamId = $matches[1];
            $customUrlIsLocal = true;
            Log::info("Stream has a custom URL pointing to another stream (absolute path)", [
                'streamId' => $streamId,
                'customStreamId' => $customStreamId,
                'customUrl' => $url
            ]);
        }
        // Check for absolute paths to storage folder
        else if (preg_match('#' . preg_quote(storage_path('app/public/streams/')) . '(\d+)/playlist\.m3u8$#', $url, $matches)) {
            $customStreamId = $matches[1];
            $customUrlIsLocal = true;
            Log::info("Stream has a custom URL pointing to another stream (storage path)", [
                'streamId' => $streamId,
                'customStreamId' => $customStreamId,
                'customUrl' => $url
            ]);
        }
        // Check for absolute path or storage path
        else if (strpos($url, '/') === 0 || strpos($url, 'storage/') === 0) {
            $customUrlIsLocal = true;
        }
    }

    /**
     * Verify the integrity of a segment file
     */
    private function verifySegmentIntegrity($segmentPath) 
    {
        if (!file_exists($segmentPath)) {
            Log::warning("Segment file does not exist", ['path' => $segmentPath]);
            return false;
        }
        
        if (filesize($segmentPath) < 188) { // Minimum size of valid TS packet
            Log::warning("Segment file is too small to be valid", [
                'path' => $segmentPath, 
                'size' => filesize($segmentPath)
            ]);
            return false;
        }
        
        // Read the first few bytes to verify it's a valid MPEG-TS file
        // MPEG-TS files should start with the sync byte 0x47 (decimal 71)
        $handle = fopen($segmentPath, 'rb');
        $header = fread($handle, 4);
        fclose($handle);
        
        if (empty($header) || ord($header[0]) !== 0x47) {
            Log::warning("Segment doesn't start with valid TS sync byte", [
                'path' => $segmentPath,
                'firstByte' => empty($header) ? 'empty' : dechex(ord($header[0]))
            ]);
            return false;
        }
        
        // Check for multiple TS packets with proper sync bytes (every 188 bytes)
        try {
            $handle = fopen($segmentPath, 'rb');
            $isValid = true;
            $fileSize = filesize($segmentPath);
            $syncErrors = 0;
            
            // Check first 5 packets and last 5 packets (if file is large enough)
            for ($i = 0; $i < 5 && ($i * 188) < $fileSize; $i++) {
                fseek($handle, $i * 188, SEEK_SET);
                $byte = fread($handle, 1);
                if (empty($byte) || ord($byte) !== 0x47) {
                    $syncErrors++;
                }
            }
            
            // Check end of file too
            if ($fileSize > 188 * 10) {
                for ($i = 1; $i <= 5 && ($fileSize - ($i * 188)) >= 0; $i++) {
                    fseek($handle, $fileSize - ($i * 188), SEEK_SET);
                    $byte = fread($handle, 1);
                    if (empty($byte) || ord($byte) !== 0x47) {
                        $syncErrors++;
                    }
                }
            }
            
            fclose($handle);
            
            if ($syncErrors > 0) {
                Log::warning("Segment has {$syncErrors} TS sync errors", ['path' => $segmentPath]);
                return false;
            }
            
            // Enhanced validation with ffprobe to check for parsing issues
            // Check for proper demuxing ability first (simple check)
            $baseCommand = "ffprobe -v error -show_format " . escapeshellarg($segmentPath) . " 2>&1";
            exec($baseCommand, $baseOutput, $baseReturnCode);
            
            if ($baseReturnCode !== 0) {
                Log::warning("Basic ffprobe check failed, segment likely has parsing issues", [
                    'path' => $segmentPath,
                    'error' => implode("\n", $baseOutput)
                ]);
                return false;
            }
            
            // Now run a more detailed packet analysis
            $command = "ffprobe -v error -show_entries packet=pts_time,dts_time,duration_time,stream_index -of json " . escapeshellarg($segmentPath) . " 2>&1";
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                Log::warning("FFprobe failed to analyze segment", [
                    'path' => $segmentPath,
                    'error' => implode("\n", $output)
                ]);
                return false;
            }
            
            // Check if we can parse the output as valid JSON
            $jsonOutput = implode('', $output);
            $data = json_decode($jsonOutput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE || empty($data) || !isset($data['packets']) || empty($data['packets'])) {
                Log::warning("FFprobe produced invalid or empty packet data", [
                    'path' => $segmentPath,
                    'jsonError' => json_last_error_msg(),
                    'sample' => substr($jsonOutput, 0, 200)
                ]);
                return false;
            }
            
            // Check for invalid PTS or DTS values that might cause parsing errors
            $invalidTimestamps = 0;
            foreach ($data['packets'] as $packet) {
                // Check for missing or invalid timestamps
                if ((isset($packet['pts_time']) && ($packet['pts_time'] === 'N/A' || $packet['pts_time'] < 0)) ||
                    (isset($packet['dts_time']) && ($packet['dts_time'] === 'N/A' || $packet['dts_time'] < 0))) {
                    $invalidTimestamps++;
                }
            }
            
            if ($invalidTimestamps > 2) { // Allow a couple bad packets
                Log::warning("Segment has $invalidTimestamps invalid timestamps, may cause parsing errors", [
                    'path' => $segmentPath
                ]);
                return false;
            }
            
            Log::info("Segment passed integrity check", [
                'path' => $segmentPath,
                'packetCount' => count($data['packets'])
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Exception during segment integrity check", [
                'path' => $segmentPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Attempt to repair a corrupted TS segment
     */
    private function attemptSegmentRepair($segmentPath)
    {
        Log::info("Attempting to repair segment", ['segmentPath' => $segmentPath]);
        
        // Get a clean output path
        $pathInfo = pathinfo($segmentPath);
        $repairedPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_repaired.' . $pathInfo['extension'];
        
        // Backup the original file
        $backupPath = $segmentPath . '.backup';
        if (!file_exists($backupPath)) {
            copy($segmentPath, $backupPath);
        }
        
        // Approach 1: Basic MPEG-TS remux with FFmpeg
        $basicCommand = 'ffmpeg -y -i ' . escapeshellarg($segmentPath) . 
                      ' -c copy -f mpegts -bsf:v h264_mp4toannexb ' .
                      escapeshellarg($repairedPath) . ' 2>&1';
                      
        Log::info("Trying basic remux approach", ['command' => $basicCommand]);
        
        exec($basicCommand, $basicOutput, $basicReturnCode);
        
        if ($basicReturnCode === 0 && file_exists($repairedPath) && filesize($repairedPath) > 188) {
            // Verify the repaired segment
            if ($this->verifySegmentIntegrity($repairedPath)) {
                Log::info("Segment repaired successfully with basic remux", [
                    'originalPath' => $segmentPath,
                    'repairedPath' => $repairedPath
                ]);
                return $repairedPath;
            }
            
            Log::warning("Basic remux failed integrity verification");
        } else {
            Log::warning("Basic remux failed execution", [
                'returnCode' => $basicReturnCode,
                'output' => $basicOutput
            ]);
        }
        
        // Approach 2: Try a more aggressive repair method for parsing issues
        $parseFixPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_parsefix.' . $pathInfo['extension'];
        $parseFixCommand = 'ffmpeg -y -i ' . escapeshellarg($segmentPath) . 
                         ' -c:v libx264 -preset ultrafast -b:v 2000k -c:a aac -f mpegts ' .
                         escapeshellarg($parseFixPath) . ' 2>&1';
                         
        Log::info("Trying re-encode approach for parsing issues", ['command' => $parseFixCommand]);
        
        exec($parseFixCommand, $parseFixOutput, $parseFixReturnCode);
        
        if ($parseFixReturnCode === 0 && file_exists($parseFixPath) && filesize($parseFixPath) > 188) {
            // Verify the repaired segment
            if ($this->verifySegmentIntegrity($parseFixPath)) {
                Log::info("Segment repaired successfully with re-encode", [
                    'originalPath' => $segmentPath,
                    'repairedPath' => $parseFixPath
                ]);
                return $parseFixPath;
            }
            
            Log::warning("Re-encode approach failed integrity verification");
        } else {
            Log::warning("Re-encode approach failed execution", [
                'returnCode' => $parseFixReturnCode,
                'output' => $parseFixOutput
            ]);
        }
        
        // Approach 3: Last resort, create a working empty segment
        $emptyPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_empty.' . $pathInfo['extension'];
        $emptyCommand = 'ffmpeg -y -f lavfi -i anullsrc=r=44100:cl=stereo:d=2 ' .
                       '-f lavfi -i color=s=1280x720:r=30:d=2 ' .
                       '-c:a aac -ar 44100 -c:v libx264 -r 30 -pix_fmt yuv420p -f mpegts ' .
                       escapeshellarg($emptyPath);
                      
        Log::info("Creating empty segment as last resort", ['command' => $emptyCommand]);
        
        exec($emptyCommand, $emptyOutput, $emptyReturnCode);
        
        if ($emptyReturnCode === 0 && file_exists($emptyPath) && filesize($emptyPath) > 188) {
            // Empty segments should pass verification
            if ($this->verifySegmentIntegrity($emptyPath)) {
                Log::info("Empty segment created successfully", [
                    'originalPath' => $segmentPath,
                    'emptyPath' => $emptyPath
                ]);
                return $emptyPath;
            }
        }
        
        Log::error("All segment repair approaches failed");
        return false;
    }
    
    /**
     * Serve a valid segment file, falling back to an empty segment if the file is invalid
     * 
     * @param string $segmentPath Path to the segment file
     * @return \Illuminate\Http\Response
     */
    public function serveSegment($segmentPath)
    {
        if (!$this->verifySegmentIntegrity($segmentPath)) {
            Log::warning("Segment failed integrity check, attempting repair", [
                'segmentPath' => $segmentPath
            ]);
            
            // Try to repair the segment
            $repairedPath = $this->attemptSegmentRepair($segmentPath);
            
            if ($repairedPath) {
                Log::info("Serving repaired segment", [
                    'originalPath' => $segmentPath,
                    'repairedPath' => $repairedPath
                ]);
                
                return response()->file($repairedPath, [
                    'Content-Type' => 'video/MP2T',
                    'Cache-Control' => 'public, max-age=3600',
                    'Connection' => 'keep-alive',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range',
                    'X-Accel-Buffering' => 'no' // Disable nginx buffering for streaming
                ]);
            }
            
            Log::warning("Segment repair failed, serving empty segment instead", [
                'segmentPath' => $segmentPath
            ]);
            return $this->createEmptySegment();
        }
        
        Log::info("Serving valid segment", [
            'segmentPath' => $segmentPath,
            'fileSize' => filesize($segmentPath)
        ]);
        
        // Use readfile() to serve the content directly for better performance
        return response()->stream(
            function() use ($segmentPath) {
                @readfile($segmentPath);
            },
            200,
            [
                'Content-Type' => 'video/MP2T',
                'Content-Length' => filesize($segmentPath),
                'Cache-Control' => 'public, max-age=3600',
                'Connection' => 'keep-alive',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range',
                'X-Accel-Buffering' => 'no' // Disable nginx buffering for streaming
            ]
        );
    }

    private function addCorsHeaders($response)
    {
        return $response->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Request-With, Range');
    }

    /**
     * Handle requests for empty.ts segment referenced in empty playlists
     */
    public function emptySegment()
    {
        Log::info("Serving empty segment file");
        return $this->createEmptySegment();
    }
}