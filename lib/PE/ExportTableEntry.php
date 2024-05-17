<?php

namespace PHPObjectSymbolResolver\PE;

class ExportTableEntry extends Symbol {
    /**
     * This structure is 20 bytes long.
     */
    const SIZEOF = 20;

    public int $nameRva;
    public int $ordinal;
    public int $address;
    public bool $isForwarder;
    public ?string $forwarderString = null;
}
