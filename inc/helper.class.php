<?php


class PluginAdvbalancerHelper
{
    static function writeToLog(string $filename, $data, string $title = '')
    {
        if (!is_dir(pathinfo($filename, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($filename, PATHINFO_DIRNAME), 0755, true);
        }

        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s") . "\n";
        $log .= (strlen($title) > 0 ? $title : 'LOG DATA') . "\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";

        if(file_put_contents($filename, $log, FILE_APPEND)) {
            return true;
        }

    }
}