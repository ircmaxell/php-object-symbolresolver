<?php

namespace PHPObjectSymbolResolver\PE;

class DelayedLoadDirectoryEntry extends ImportLookupTableEntry {
    public function isWeak(): bool {
        return true;
    }
}
