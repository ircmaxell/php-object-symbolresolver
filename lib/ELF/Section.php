<?php

namespace PHPObjectSymbolResolver\ELF;

class Section {
    public int $name;
    public string $nameString;

    const TYPE_NULL = 0;
    const TYPE_PROGBITS = 1;
    const TYPE_SYMTAB = 2;
    const TYPE_STRTAB = 3;
    const TYPE_RELA = 4;
    const TYPE_HASH = 5;
    const TYPE_DYNAMIC = 6;
    const TYPE_NOTE = 7;
    const TYPE_NOBITS = 8;
    const TYPE_REL = 9;
    const TYPE_SHLIB = 10;
    const TYPE_DYNSYM = 11;

    public int $type;
    public int $flags;
    public int $addr;
    public int $offset;
    public int $size;
    public int $link;
    public int $info;
    public int $addralign;
    public int $entsize;

    public array $symbols = [];
}