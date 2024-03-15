<p align="center">
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases">
        <img src="https://img.shields.io/github/v/release/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=brightgreen" alt="GitHub Latest Release (by date)" title="GitHub Latest Release (by date)">
    </a>
</p>
<p align="center">
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases">
        <img src="https://img.shields.io/github/downloads/imfx77/Dark-Lyrics-Plugin-Synology/total?style=for-the-badge&color=orange" alt="GitHub All Releases" title="GitHub All Downloads">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases">
        <img src="https://img.shields.io/github/directory-file-count/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=orange" alt="GitHub Repository File Count" title="GitHub Repository File Count">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases">
        <img src="https://img.shields.io/github/repo-size/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=orange" alt="GitHub Repository Size" title="GitHub Repository Size">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases">
        <img src="https://img.shields.io/github/languages/code-size/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=orange" alt="GitHub Code Size" title="GitHub Code Size">
    </a>
</p>
<p align="center">
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/discussions">
        <img src="https://img.shields.io/github/discussions/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=blue" alt="GitHub Discussions" title="Read Discussions">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/compare">
        <img src="https://img.shields.io/github/commits-since/imfx77/Dark-Lyrics-Plugin-Synology/latest?include_prereleases&style=for-the-badge&color=blue" alt="GitHub Commits Since Last Release" title="GitHub Commits Since Last Release">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/compare">
        <img src="https://img.shields.io/github/commit-activity/m/imfx77/Dark-Lyrics-Plugin-Synology?style=for-the-badge&color=blue" alt="GitHub Commit Monthly Activity" title="GitHub Commit Monthly Activity">
    </a>
</p>

-------


DarkLyrics.com Lyrics Plugin for Synology Audio Station
=======================================================

Wazzup, metalheads?

Been searching for a Synology audio lyrics plugin to match your metal music collection?
Me too, for quite a while. And since there wasn't any, I eventually made this one.
[DarkLyrics](http://www.darklyrics.com/) currently supplies lyrics for **13 800+** albums from **4500+** bands of all metal & rock genres.

Enjoy 🤘

If you like it and use it, please, give a ⭐ !

Requirements
------------

The sandbox of the lyrics plugin is rather old and its functionality and API are quite simple and restricted. Those apparently haven't been changed since 2013, and the chance to get some enhancement or improvement from Synology after all these years is next to none. The Synology Audio Station just tries to fetch the **"artist name"** and **"song title"** for the song being played and pass them to the plugin - which then, in turn, uses them to make a search request for lyrics online and return some text to the AS sandbox.
So, please, make sure that:

* You have an Internet connection that is visible to the Synology NAS.
* If your audio files support metadata tags (e.g. ID3, vorbis, etc), then the **"Artist"** and the **"Title"** fields must be correctly filled. Audio Station handles fairly well a bunch of different [file formats](https://www.synology.com/en-nz/dsm/7.1/software_spec/audio_station) and you can manage their metadata seamlessly through the AS interface by editing **"Song information"** for a single song or for a selection of multiple songs at once.
* Otherwise, if no metadata tags are available, Audio Station will use the file names as song titles (and no separate artist tag will be passed to the plugin). In this case you should adopt some file naming policy that includes both the artist and the title e.g. **"Artist Name - Song Title.wav"**, thus the whole info will reach the plugin.

![AS Song Info](/assets/AS_SongInfo.png)

Installation
------------

1. Download the latest **ImFx_DarkLyrics-XXXX.XX.aum** from the [**Releases**](https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases) page of the repo.
2. Open **Audio Station**, make sure you login with account that has **Admin** privileges, go to **Settings**, select **Lyrics Plugin** tab.
3. Click on **Add**, browse to the downloaded plugin file, select it and add it.
4. Check the **Enabled** box in front of the added plugin.
5. If you have multiple plugins, reorder them in the desired priority. If lyrics are not found by the first active one AS will try the next one available and so on.
6. When you download a new version of a lyrics plugin, you don't need to remove the old one, but rather directly add the new version and just confirm overwriting when promted.
7. In order for a change of the plugins configuration to take effect you may need to refresh the AS session (e.g. logout and login again, but usually a simple refresh of the browser page is sufficient)

* Ready to go!

![AS Settings](/assets/AS_Settings.png)

Problems, Solving and Reporting
-------------------------------

It is possible that no lyrics can be found for some songs, or wrong lyrics are found.
Please, read the following explanations, and try resolving the problems on your own before you complain or post a bug.

1. Lyrics cannot be found

   * The simplest explanation, of course - there is no data about this band and/or song in DarkLyrics' database, nothing to do about that 😕 Except maybe submit the lyrics 😄
   * The metadata tags or the file name of your song have a misspelled artist and/or title
   * The metadata tags or the file name of your song contain extra terms - e.g. "bonus song", "intro", "instrumental", "YYYY" - that are not an actual part of the title and are bugging the search
   * On rare occasion, it might be the other way around - band or song name are misspelled on DarkLyrics' side. I don't know what is the way to issue corrections on existing bands/songs/lyrics, so this one sucks 😕
   * On even rarer occasion, the song is there, everything is named correctly on both Audio Station and DarkLyric sides, yet the search yields empty or wrong - sorry, absolutely no clue about this one, guess DarkLyrics search is not perfect after all 😉
   * The search can eventually match a song that is a cover from another artist, or is present in multiple albums (e.g. compilations, best of, remastered, etc.), this will probably show a different heading but it should be essentially the same lyrics if they were correctly submitted
2. Wrong lyrics are found

   * This mostly happens because of the above problem (1), when lyrics for the specific band and/or song cannot be found the search will return the closest matches to the given search terms, which might appear totally arbitrary
   * On rare occasion, when the search terms are a few simple words, multiple results may be listed, and even though the target song is among them the top best match might not be it
3. Steps to Check and Resolve

   * On either problem (1) or (2), the first thing to check is - go to [DarkLyrics.com](http://www.darklyrics.com/) and search manually entering `<ARTIST> <TITLE>` in the search field exactly as spelled in your song's tags. See if site's search returns a sensible result as a top match song, and if it does (while the plugin fails) then post a `bug` here, with all search data provided alongside, and I will try to pinpoint the problem.
   * If the manual search itself returns nothing or bulshit (i.e. the plugin is not to blame), then understanding what is the cause of the problem may take several steps further:
     * Try to find the band first - search for the name or better browse for it manually using the letter links in the top bar. If the band is not there - problem found. Ensure you are spelling it right! It's not absolutely impossible that a band name on DarkLyrics is misspelled but it would be really really weird 😲
     * When having found the band, then browse the albums and songs. If any of them is missing - problem found.
     * If the target song is there, then compare its title and the exact spelling of your song and the one on DarkLyrics.
     * In case of misspelled band or song name at your side, consider renaming those locally via Audio Station to match the online database - then play the song once again and observe the lyrics.

Releases
--------

Please, refer the [**Releases**](https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/releases) page of this repo for latest builds.

The version denotes the date of the build at which the plugin was compatible and correctly working against the DarkLyrics site.
As the site's search functionality, lyrics formatting or cookies policy may change over time, so should the plugin in order to match those changes, thus new versions will be issued.

* **2024.01** : `Initial Release`
* **2024.02** : `impl caching for the cookie so to reduce the complex calculations on each request`

Manual Packing of AUM
---------------------

**Linux**

> tar zcf module_name-version.aum INFO ImFxDarkLyrics.php ImFxCommon.php

**Windows 10+**

> tar -zcf module_name-version.aum INFO ImFxDarkLyrics.php ImFxCommon.php

License
-------

The plugin is provided under the [MIT](LICENSE) license.

Credits
-------

* **Ac_K** for [Genius Lyrics Plugin](https://github.com/AcK77/Genius-Lyrics-Plugin-Synology), I am actually not using a bit of it, yet originally forked from it.
* **Frank Lai** for the source code of [Fujirou Lyrics Plugins](https://github.com/franklai/synologylyric/tree/master/src) which are the main reference.
* The [Lyrics Module Development Guide](https://global.download.synology.com/download/Document/DeveloperGuide/AS_Guide.pdf) for **Synology Audio Station**, the PDF is also included in the [**/doc**](https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/tree/master/doc) folder of this repo.

-------

<p align="center">
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/stargazers" title="View Stargazers">
        <img src="https://img.shields.io/github/stars/imfx77/Dark-Lyrics-Plugin-Synology?logo=github&style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/forks" title="See Forks">
        <img src="https://img.shields.io/github/forks/imfx77/Dark-Lyrics-Plugin-Synology?logo=github&style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/blob/master/LICENSE" title="Read License">
        <img src="https://img.shields.io/github/license/imfx77/Dark-Lyrics-Plugin-Synology?style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/issues" title="Open Issues">
        <img src="https://img.shields.io/github/issues-raw/imfx77/Dark-Lyrics-Plugin-Synology?style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/issues?q=is%3Aissue+is%3Aclosed" title="Closed Issues">
        <img src="https://img.shields.io/github/issues-closed/imfx77/Dark-Lyrics-Plugin-Synology?style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/discussions" title="Read Discussions">
        <img src="https://img.shields.io/github/discussions/imfx77/Dark-Lyrics-Plugin-Synology?style=flat-square" alt="Dark-Lyrics-Plugin-Synology">
    </a>
    <a href="https://github.com/imfx77/Dark-Lyrics-Plugin-Synology/compare/" title="Latest Commits">
        <img alt="GitHub commits since latest release (by date)" src="https://img.shields.io/github/commits-since/imfx77/Dark-Lyrics-Plugin-Synology/latest?style=flat-square">
    </a>
</p>
