<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

class ImFxDarkLyrics
{
    private static $DEBUG;
    private static $sitePrefix;
    private static $cacheFile;

    public function __construct()
    {
        self::$DEBUG = false;
        self::$sitePrefix = 'http://www.darklyrics.com';
        self::$cacheFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cache.dat';
    }

    public function getLyricsList($artist, $title, $info)
    {
        $this->ensureSession();
        return $this->search($info, $artist, $title);
    }
    public function getLyrics($id, $info)
    {
        $this->ensureSession();
        return $this->get($info, $id);
    }

    public function ensureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function search($handle, $artist, $title)
    {
        $count = 0;

        $normalized_artist = ImFxCommon::normalizeString($artist);
        $normalized_title = ImFxCommon::normalizeString($title);

        $search_url = sprintf(
            "%s/search?q=%s",
            self::$sitePrefix, urlencode(sprintf('%s %s', $normalized_artist, $normalized_title))
        );

        $lastvisitts = self::calculateLastVisitCookie();
        $headers = array('Cookie: lastvisitts='.$lastvisitts . '; PHPSESSID='.session_id());
        $content = ImFxCommon::getContent($search_url, $headers);
        if (!$content) {
            if (self::$DEBUG) {
                $handle->addTrackInfoToList(
                    $normalized_artist,
                    $normalized_title,
                    $search_url,
                    ''
                );
                return 1;
            }
            return $count;
        }

        $list = $this->parseSearchResult($content);

        $count = count($list);
        for ($idx = 0; $idx < $count; $idx++) {
            $obj = $list[$idx];

            $handle->addTrackInfoToList(
                $obj['artist'],
                $obj['title'],
                $obj['id'],
                $obj['partial']
            );
        }

        if (self::$DEBUG && !$count) {
            $handle->addTrackInfoToList(
                $normalized_artist,
                $normalized_title,
                $content,
                ''
            );
            return 1;
        }

        return $count;
    }

    public function get($handle, $id)
    {
        $lyric = '';

        if (self::$DEBUG) {
            $ts = filemtime(self::$cacheFile);
            $cookie = file_get_contents(self::$cacheFile);
            $lyric = sprintf(
                "DEBUG\n%s\n%s\n%s\n",
                $ts . ' / ' . $cookie,
                self::$sitePrefix,
                $id
                );

            $handle->addLyrics($lyric, $id);
            return true;
        }

        $url = sprintf("%s/%s", self::$sitePrefix, $id);

        $content = ImFxCommon::getContent($url);
        if (!$content) {
            return false;
        }

        $pattern = '/<h1><a href=".+?">(.*?)<\/a><\/h1>/';
        $value = ImFxCommon::getFirstMatch($content, $pattern);
        if (!$value) {
            return false;
        }
        $artist = $value;
        $artist = preg_replace('/ LYRICS/', '', $artist);

        $pattern = '/<h2>(.*?)<\/h2>/';
        $value = ImFxCommon::getFirstMatch($content, $pattern);
        if (!$value) {
            return false;
        }
        $album = $value;
        $album = preg_replace('/album: /', '', $album);

        $title_number = intval( substr($url, strpos($url, "#") + 1) );

        $pattern = '/<h3><a name="'.$title_number.'">(.*?)<\/a><\/h3>/';
        $value = ImFxCommon::getFirstMatch($content, $pattern);
        if (!$value) {
            return false;
        }
        $title = $value;

        $prefix = '<h3><a name="'.$title_number.'">'.$title.'</a></h3>';
        $prefix_regex = '/<h3><a name=\"'.$title_number.'\">'.preg_quote($title).'<\/a><\/h3>/';
        $suffix = '<h3><a name="'.($title_number + 1).'">';
        $suffix_regex = '/<h3><a name=\"'.($title_number + 1).'\">/';
        $value = ImFxCommon::getSubString($content, $prefix, $suffix);

        if (!$value || $value === $content) {
            $suffix = '<div class="thanks">';
            $suffix_regex = '/<div class=\"thanks\">/';
            $value = ImFxCommon::getSubString($content, $prefix, $suffix);

            if (!$value || $value === $content) {
                $suffix = '<div class="note">';
                $suffix_regex = '/<div class=\"note\">/';
                $value = ImFxCommon::getSubString($content, $prefix, $suffix);

                if (!$value || $value === $content) {
                    $suffix = '</div>';
                    $suffix_regex = '/<\/div>/';
                    $value = ImFxCommon::getSubString($content, $prefix, $suffix);

                    if (!$value || $value === $content) {
                        return false;
                    }
                }
            }
        }

        $body = $value;
        $body = preg_replace($prefix_regex, '', $body);
        $body = preg_replace($suffix_regex, '', $body);
        $body = preg_replace('/<br \/>/', '', $body);
        $body = preg_replace('/<i>/', '', $body);
        $body = preg_replace('/<\/i>/', '', $body);
        $body = preg_replace('/<b>/', '', $body);
        $body = preg_replace('/<\/b>/', '', $body);

        $lyric = sprintf(
            "%s\n\n%s\n%s\n\n%s\n\n\n%s",
            'lyric from DarkLyrics',
            $artist,
            $album,
            $title,
            $body
        );

        $handle->addLyrics($lyric, $id);

        return true;
    }

    private function parseSearchResult($content) {
        $result = array();

        $prefix = '<h3 class="seah">Songs:</h3>';
        $suffix = '<img class="fr" src="../rightbottom.gif" alt="" />';
        $block = ImFxCommon::getSubString($content, $prefix, $suffix);
        if (!$block || $block === $content) {
            return $result;
        }

        $pattern = '/<a href=".+?".+?>.*? - (.*?)<\/a>/';
        $value = ImFxCommon::getFirstMatch($block, $pattern);
        if (!$value) {
            return $result;
        }
        $title = ImFxCommon::decodeHTML($value);

        $pattern = '/<a href=".+?".+?>(.*?) - .*?<\/a>/';
        $value = ImFxCommon::getFirstMatch($block, $pattern);
        if (!$value) {
            return $result;
        }
        $artist = ImFxCommon::decodeHTML($value);

        $pattern = '/<a href="(.+?)".+?>.*?<\/a>/';
        $value = ImFxCommon::getFirstMatch($block, $pattern);
        if (!$value) {
            return $result;
        }
        $id = $value;

        $item = array(
            'artist' => $artist,
            'title'  => $title,
            'id'     => $id,
            'partial'=> ''
        );

        array_push($result, $item);

        return $result;
    }

    private function calculateLastVisitCookie() {
        /** ----{ COOKIE CALCULATION FROM DARKLYRICS JAVASCRIPT }----
        *   var lastvisitts = 'Nergal' + Math.ceil(new Date().getTime() / (60 * 60 * 6 * 1000)).toString();
        *   var lastvisittscookie = 0;
        *   for (var i = 0; i < lastvisitts.length; i++) {
        *       lastvisittscookie = ((lastvisittscookie << 5) - lastvisittscookie) + lastvisitts.charCodeAt(i);
        *       lastvisittscookie = lastvisittscookie & lastvisittscookie;
        *   }
        *   document.cookie = 'lastvisitts=' + lastvisittscookie + '; domain=.darklyrics.com; path=/';
        */

        // calculate the custom timestamp
        $ts = ceil(time() / (3600 * 6)); // getting time in seconds, hence skipping division by 1000

        // use cached cookie from file
        if (file_exists(self::$cacheFile) && filemtime(self::$cacheFile) > ($ts - 1) * (3600 * 6)) {
            return file_get_contents(self::$cacheFile);
        }

        // replica to the above calculations from DarkLyrics javascript
        $ts_str = sprintf('Nergal%s', $ts);
        $cookie = 0;

        $ts_strlen = strlen($ts_str);
        for ($i = 0; $i < $ts_strlen; $i++) {
            $shift_val = $cookie << 5;
            $shift_val = $shift_val & 0xffffffff;                                   // limit to DWORD
            if ($shift_val > 0x7fffffff) $shift_val = $shift_val - 0xffffffff - 1;  // simulate DWORD overflow
            $cookie = ($shift_val - $cookie) + ord(substr($ts_str, $i, 1));
            $cookie = $cookie & 0xffffffff;                                         // limit to DWORD
            if ($cookie > 0x7fffffff) $cookie = $cookie - 0xffffffff - 1;           // simulate DWORD overflow
        }

        file_put_contents(self::$cacheFile, $cookie, LOCK_EX);

        return $cookie;
    }
}
