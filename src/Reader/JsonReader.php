<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Reader;

use Port\Reader\CountableReader;

class JsonReader implements CountableReader, \SeekableIterator
{
    protected $json;

    public function __construct(\SplFileObject $file)
    {
        $content = file_get_contents($file->getFilename());

        $this->json = json_decode($content);
    }

    public function current()
    {

    }

    public function next()
    {

    }

    public function key()
    {

    }

    public function valid()
    {

    }

    public function rewind()
    {

    }

    public function count()
    {

    }

    public function seek($position)
    {

    }
}