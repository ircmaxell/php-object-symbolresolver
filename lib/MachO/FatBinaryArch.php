<?php

namespace PHPObjectSymbolResolver\MachO;

class FatBinaryArch {
    public int $cpuType;
    public int $cpuSubtype;
    public int $offset;
    public int $size;
    public int $align;

    public function __construct(string $data) {
        [, $this->cpuType, $this->cpuSubtype, $this->offset, $this->size, $this->align] = unpack("N5", $data);
    }
}
