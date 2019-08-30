<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Reader;

use Port\Reader\ReaderFactory;

class JsonReaderFactory implements ReaderFactory
{
    public function getReader(\SplFileObject $file)
    {
        return new JsonReader($file);
    }

}