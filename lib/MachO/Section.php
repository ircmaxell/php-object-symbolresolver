<?php

namespace PHPObjectSymbolResolver\MachO;

class Section {
    public string $name;
    public string $segname;

    public int $addr;
    public int $size;
    public int $offset;
    public int $align;
    public int $reloff;
    public int $nreloc;
    public int $flags;
    public int $reserved1;
    public int $reserved2;
}