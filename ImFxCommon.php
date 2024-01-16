<?php

class ImFxCommon
{
    /**
     * send request to given url, and return content
     */
    public static function getContent($url, $headers = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($curl, CURLOPT_VERBOSE, false);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL, $url);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Using Google Search API, and return the json of results
     */
    public static function searchFromGoogle($keyword, $siteUrl)
    {
        $googleWebSearchUrl = 'https://ajax.googleapis.com/ajax/services/search/web';

        $queryString = sprintf(
            "%s site:%s",
            $keyword, $siteUrl
        );

        $searchUrl = sprintf(
            "%s?v=1.0&q=%s",
            $googleWebSearchUrl, urlencode($queryString)
        );

        $content = self::getContent($searchUrl);

        $json = json_decode($content, true);

        return $json['responseData']['results'];
    }

    /**
     * given regular expression, return the first matched string
     */
    public static function getFirstMatch($string, $pattern)
    {
        if (1 === preg_match($pattern, $string, $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * given regular expression, return all matched first group in an array
     */
    public static function getAllFirstMatch($string, $pattern)
    {
        $ret = preg_match_all($pattern, $string, $matches);
        if ($ret > 0) {
            return $matches[1];
        } else {
            return $ret;
        }
    }

    /**
     * given regular expression, return all matched groups
     */
    public static function getAllMatches($string, $pattern)
    {
        $ret = preg_match_all($pattern, $string, $matches);
        if ($ret > 0) {
            return $matches;
        } else {
            return $ret;
        }
    }

    /**
     * given prefix and suffix, return the shortest matched string.
     * returned string including prefix and suffix
     */
    public static function getSubString($string, $prefix, $suffix)
    {
        $start = strpos($string, $prefix);
        if ($start === false) {
            return $string;
        }

        $end = strpos($string, $suffix, $start);
        if ($end === false) {
            return $string;
        }

        if ($start >= $end) {
            return $string;
        }

        return substr($string, $start, $end - $start + strlen($suffix));
    }

    /**
     * normalizing string for search purposes
     */
    public static function normalizeString($string)
    {
        $normalized_string = $string;

        // remove anything but alpha-numeric and space
        $normalized_string = preg_replace('/[^\da-z -\.]/i', ' ', $normalized_string);
        // remove 2 digit numbers
        $normalized_string = preg_replace('/\b([A-Z]*\d[A-Z]*){2}\b/', ' ', $normalized_string);
        // reduce floating dashes
        $normalized_string = preg_replace('/- /', ' ', $normalized_string);
        $normalized_string = preg_replace('/ -/', ' ', $normalized_string);
        // reduce floating dots
        $normalized_string = preg_replace('/\. /', ' ', $normalized_string);
        $normalized_string = preg_replace('/ \./', ' ', $normalized_string);
        // remove all words of 1 character
        $normalized_string = preg_replace('/(\b.{1}\s)/', ' ', $normalized_string);
        // reduce multiple spaces
        $normalized_string = trim(preg_replace('/\s\s+/', ' ', $normalized_string));

        return $normalized_string;
    }

    /**
     * remove CR and LF from string
     */
    public static function toOneLine($string)
    {
        $string = str_replace(array("\n", "\r"), '', $string);
        return preg_replace('/>\s+</', '><', $string);
    }

    /**
     * decode HTML entity using UTF-8 encoding
     */
    public static function decodeHTML($string)
    {
        return html_entity_decode($string, ENT_QUOTES, 'UTF-8');
    }
}
