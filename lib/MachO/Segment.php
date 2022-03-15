<?php

namespace PHPObjectSymbolResolver\MachO;

class Segment {
    public string $name;

    public int $vmaddr;
    public int $vmsize;
    public int $fileoff;
    public int $filesize;
    public int $maxprot;
    public int $initprot;
    public int $nSects;
    public int $flags;

	public array $sections = [];
}