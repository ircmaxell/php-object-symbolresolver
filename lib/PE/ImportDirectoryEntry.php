<?php

namespace PHPObjectSymbolResolver\PE;

class ImportDirectoryEntry {
    /**
     * This structure is 20 bytes long.
     */
    const SIZEOF = 20;

    public int $importLookupTableRva;
    public int $timeDateStamp;
    public int $forwarderChain;
    public int $nameRva;
    public int $importAddressTableRva;

    public string $dllFilename;
}
