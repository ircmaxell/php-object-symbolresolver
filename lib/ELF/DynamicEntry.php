<?php

namespace PHPObjectSymbolResolver\ELF;

class DynamicEntry {
    public int $tag;
    public int $value;

    const DT_NULL = 0;
    const DT_NEEDED = 1;
    const DT_PLTRELSZ = 2;
    const DT_PLTGOT = 3;
    const DT_HASH = 4;
    const DT_STRTAB = 5;
    const DT_SYMTAB = 6;
    const DT_RELA = 7;
    const DT_RELASZ = 8;
    const DT_RELAENT = 9;
    const DT_STRSZ = 10;
    const DT_SYMENT = 11;
    const DT_INIT = 12;
    const DT_FINI = 13;
    const DT_SONAME = 14;
    const DT_RPATH = 15;
    const DT_SYMBOLIC = 16;
    const DT_REL = 17;
    const DT_RELSZ = 18;
    const DT_RELENT = 19;
    const DT_PLTREL = 20;
    const DT_DEBUG = 21;
    const DT_TEXTREL = 22;
    const DT_JMPREL = 23;
    const DT_LOPROC = 0x70000000;
    const DT_HIPROC = 0x7fffffff;

    public ?string $string;

}
