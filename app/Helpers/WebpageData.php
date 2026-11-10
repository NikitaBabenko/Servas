<?php

namespace App\Helpers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Http;

class WebpageData
{
    public static function getWebPageTitle(string $url): string
    {
        // VibeNest's private profile never fetches bookmark URLs from the server.
        // This guard covers web create/update and API create, including empty titles.
        if (!config('app.fetch_link_titles', false)) {
            return mb_substr($url, 0, 255);
        }

        $filteredUrl = filter_var($url, FILTER_VALIDATE_URL);

        if ($filteredUrl === false) return '';

        try {
            $html = Http::timeout(30)->get($filteredUrl)->body();

            preg_match_all('@<head.*>.*<title.*>(.*)</title>.*</head>@sU', $html, $matches);

            if (empty($matches[1])) return '';

            $title = trim(html_entity_decode($matches[1][0]));
            $title = strip_tags($title);

            return mb_substr($title, 0, 255);
        } catch (RequestException) {

            return '';
        }
    }
}
