<?php
spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

class ImFxDarkLyrics
{
    private $sitePrefix = 'http://www.darklyrics.com';
    private $DEBUG = false;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function getLyricsList($artist, $title, $info)
    {
        return $this->search($info, $artist, $title);
    }
    public function getLyrics($id, $info)
    {
        return $this->get($info, $id);
    }

    public function search($handle, $artist, $title)
    {
        $count = 0;

        $normalized_artist = ImFxCommon::normalizeString($artist);
        $normalized_title = ImFxCommon::normalizeString($title);

        $search_url = sprintf(
            "%s/search?q=%s",
            $this->sitePrefix, urlencode(sprintf('%s %s', $normalized_artist, $normalized_title))
        );

        $lastvisitts = self::calculateLastVisitCookie();
        $headers = array('Cookie: lastvisitts='.$lastvisitts . '; PHPSESSID='.session_id());
        $content = ImFxCommon::getContent($search_url, $headers);
        if (!$content) {
            if ($this->DEBUG) {
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

        if ($this->DEBUG && !$count) {
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

        if ($this->DEBUG) {
            $lyric = sprintf("DEBUG\n%s\n", $id);
            $handle->addLyrics($lyric, $id);
            return true;
        }

        $url = sprintf("%s/%s", $this->sitePrefix, $id);

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
        /*
        var lastvisitts = 'Nergal' + Math.ceil(new Date().getTime() / (60 * 60 * 6 * 1000)).toString();
        var lastvisittscookie = 0;
        for (var i = 0; i < lastvisitts.length; i++) {
            lastvisittscookie = ((lastvisittscookie << 5) - lastvisittscookie) + lastvisitts.charCodeAt(i);
            lastvisittscookie = lastvisittscookie & lastvisittscookie;
        }
        document.cookie = 'lastvisitts=' + lastvisittscookie + '; domain=.darklyrics.com; path=/';
        */

        // replica to the above calculations from DarkLyrics script
        $ts = sprintf('Nergal%s', ( ceil(time() / (60 * 60 * 6)) )); // time in seconds, hence skip division by 1000
        $cookie = 0;

        $ts_len = strlen($ts);
        for ($i = 0; $i < $ts_len; $i++) {
            $shift_val = $cookie << 5;
            $shift_val = $shift_val & 0xffffffff;                                   // limit to DWORD
            if ($shift_val > 0x7fffffff) $shift_val = $shift_val - 0xffffffff - 1;  // simulate DWORD overflow
            $cookie = ($shift_val - $cookie) + ord(substr($ts, $i, 1));
            $cookie = $cookie & 0xffffffff;                                         // limit to DWORD
            if ($cookie > 0x7fffffff) $cookie = $cookie - 0xffffffff - 1;           // simulate DWORD overflow
        }

        return $cookie;
    }
}
