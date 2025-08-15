<?php

class Router {
    public function handleRequest() {
        $channelId = $_GET['channel_id'] ?? null;

        if (!$channelId) {
            http_response_code(400);
            return json_encode(['error' => 'Channel ID is required']);
        }

        $url = "https://www.youtube.com/feeds/videos.xml?channel_id=" . urlencode($channelId);
        $xmlData = @file_get_contents($url);

        if ($xmlData === false) {
            http_response_code(404);
            return json_encode(['error' => 'Channel not found or unable to retrieve data']);
        }

        $lastClosedTagPos = strrpos($xmlData, '>');
        if ($lastClosedTagPos === false) {
            http_response_code(500);
            return json_encode(['error' => 'Invalid XML data']);
        }
        // Remove anything after the last closing tag
        $xmlData = substr($xmlData, 0, $lastClosedTagPos + 1);

        // The router tries to serve JSON, so try to convert XML to JSON
        $xmlObject = simplexml_load_string($xmlData);
        if ($xmlObject === false) {
            http_response_code(500);
            return json_encode(['error' => 'Failed to parse XML data']);
        }

        // Get channel title
        $title = (string)($xmlObject->title ?? '');

        // Get up to 3 newest entries
        $entries = [];
        if (isset($xmlObject->entry)) {
            $count = 0;
            foreach ($xmlObject->entry as $entry) {
                if ($count++ >= 3) break;
                // Extract thumbnail URL if available
                $thumbnail = '';
                if (isset($entry->children('media', true)->group->thumbnail)) {
                    $thumbnail = (string)$entry->children('media', true)->group->thumbnail->attributes()->url;
                }
                $href = (string)$entry->link['href'];
                $entries[] = [
                    'title' => (string)$entry->title,
                    'href' => $href,
                    'published' => (string)$entry->published,
                    'thumbnail' => $thumbnail
                ];
            }
        }

        return json_encode([
            'title' => $title,
            'entries' => $entries
        ]);
    }
}