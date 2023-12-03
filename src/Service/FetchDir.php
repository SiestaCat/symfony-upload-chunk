<?php declare( strict_types = 1 );

namespace Siestacat\UploadChunkBundle\Service;

class FetchDir
{
    public function __construct(public string $tmp_path)
    {
        if(!in_array(substr($this->tmp_path, -1), ['/', '\\'])) $this->tmp_path .= DIRECTORY_SEPARATOR;
    }

    public function createDir(string $dir_path):string
    {
        if(!is_dir($dir_path)) mkdir($dir_path, 0777, true);

        return $dir_path;
    }

    public function getFile(string $rel_path):string
    {
        $file_path = $this->tmp_path . $rel_path;

        $this->createDir(dirname($file_path));

        if(!is_file($file_path)) touch($file_path);

        return $file_path;
    }
}
