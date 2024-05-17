<?php

namespace PHPObjectSymbolResolver\PE;

class ImportLookupTableEntry extends Symbol {
    public bool $isOrdinal;
    public int $ordinal;
    public int $hint;

    public string $dllFilename;
}
