<?php

namespace IgI\SypexGeo;

use Composer\Script\Event;

class Composer
{

    /**
     * @param Event $event
     * @return bool
     */
    public static function installDatabases(Event $event)
    {
        $event->getIO()->write("<warning> *** SxGeo database update *** </warning>");

        $extra = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extra['sypexgeo_remote'])) {
            $extra['sypexgeo_remote'] = 'https://sypexgeo.net/files/SxGeoCountry.zip';
            $event->getIO()->write("<info>No database update url `sypexgeo_remote` specified in composer extra, using default...</info>");
        }
        $event->getIO()->write(sprintf("Database update url is `%s`...", $extra['sypexgeo_remote']));

        $event->getIO()->write("Starting download...");

        $tmpDir = sys_get_temp_dir();
        $zipFile = implode(DIRECTORY_SEPARATOR, array(
            $tmpDir,
            "sypex_update" . md5(microtime()) . ".zip",
        ));
        $zipResource = fopen($zipFile, "w");

        $last = null;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $extra['sypexgeo_remote']);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_NOPROGRESS, 0);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($clientp, $dltotal, $dlnow) use ($event, & $last) {
            if ($dltotal != 0) {
                $now = number_format($dlnow / (1024 * 1024), 2);
                $total = number_format($dltotal / (1024 * 1024), 2);
                if ($last != $now) {
                    $percent = $now / ($total / 100);
                    $event->getIO()->overwrite($now . "MB/" . $total . "MB" . ", " . number_format($percent, 2) . "%", false);
                    $last = $now;
                }
            }
        });
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $result = curl_exec($ch);
        if (!$result) {
            $event->getIO()->write(sprintf("<error>Download failed: %s</error>", curl_error($ch)));
        }
        curl_close($ch);

        $event->getIO()->write(sprintf("Downloaded to `%s`.", $zipFile));
        $event->getIO()->write("Download complete.");
        $event->getIO()->write("Starting extraction...");

        $zip = new \ZipArchive();
        $extractPath = implode(DIRECTORY_SEPARATOR, [
            $tmpDir,
            "sypex_update" . md5(microtime())
        ]);
        $zipResult = $zip->open($zipFile);
        if ($zipResult != true) {
            $event->getIO()->write(sprintf("<error>Extraction failed: error code %s</error>", $zipResult));
        }

        $defaultFileName = $zip->getNameIndex(0);

        /* Extract Zip File */
        $zip->extractTo($extractPath);
        $zip->close();

        $event->getIO()->write(sprintf("Extracted to `%s`.", $extractPath));
        $event->getIO()->write("Extraction complete.");

        if (!isset($extra['sypexgeo_local'])) {
            $extra['sypexgeo_local'] = __DIR__ . DIRECTORY_SEPARATOR . $defaultFileName;
            $event->getIO()->write("<info>No database install path `sypexgeo_local` specified in composer extra, using default...</info>");
        }
        $event->getIO()->write(sprintf("Database install path is `%s`...", $extra['sypexgeo_local']));

        $event->getIO()->write("Starting file copy...");
        $copyResult = copy(
                $extractPath . DIRECTORY_SEPARATOR . $defaultFileName
                , $extra['sypexgeo_local']
        );

        if ($copyResult) {
            $event->getIO()->write("Copy complete.");
        } else {
            $event->getIO()->write("<error>Copy failed</error>");
        }

        $event->getIO()->write("<warning> *** SxGeo database update finished *** </warning>");
    }

}
