<?php

namespace PHPObjectSymbolResolver\MachO;

class DylibLoadCommand {
    public string $name;
    public int $timestamp;
    public int $currentVersion;
    public int $compatibilityVersion;
}