<?php

namespace PHPObjectSymbolResolver\MachO;

class TbdObjectFile implements \PHPObjectSymbolResolver\ObjectFile {
    public string $installName;
    public array $symbols;

    public static function readForObject($objectFile): self {
        return self::readTdb(self::searchTdbFile($objectFile));
    }

    public static function searchTdbFile($objectFile): string {
        return "/Applications/Xcode.app/Contents/Developer/Platforms/MacOSX.platform/Developer/SDKs/MacOSX.sdk" . preg_replace('(.dylib$)', "", $objectFile) . ".tbd";
    }

    public static function readTdb($filename): self {
        $reader = new self;
        $reader->readSymbols(file_get_contents($filename));
        return $reader;
    }

    public function readSymbols($file) {
        preg_match_all('(symbols:\s*\[\K[^]]*)', $file, $m);
        $symbols = [];
        foreach ($m[0] as $match) {
            $symbols[] = preg_split('(\'?\s*,\s*\'?)', trim($match, " \n'"));
        }
        $this->symbols = array_merge(...$symbols);

        preg_match('(install-name:\s*+\'?+([^\']*))', $file, $m);
        $this->installName = $m[1];
    }

    public function is32Bit(): bool {
        return false;
    }

    public function is64Bit(): bool {
        return true;
    }

    public function hasLowestByteFirst(): bool {
        return true;
    }

    public function resolveDependentObjectsRecursively(&$objects = []) {
        $objects[$this->installName] = $this;
        return $objects;
    }

    public function getAllSymbols(): array {
        return $this->symbols;
    }

    public function getAllSymbolsRecursively(): array {
        return $this->getAllSymbols();
    }
}
