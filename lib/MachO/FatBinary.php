<?php

namespace PHPObjectSymbolResolver\MachO;

class FatBinary {
    const MAGIC = "\xca\xfe\xba\xbe";

    const CPU_TYPE_MC680x0 = 6;
    const CPU_TYPE_X86 = 7;

    const CPU_SUBTYPE_WILDCARD = [
        self::CPU_TYPE_MC680x0 => 1,
        self::CPU_TYPE_X86 => 3,
    ];

    const CPU_ARCH_ABI64 = 0x1000000;

    public string $filename;
    public array $architectures = [];

    public function __construct(string $file) {
        $this->filename = $file;

        $archs = unpack("N", file_get_contents($file, false, null, 4, 4))[1];

        $archHeaders = file_get_contents($file, false, null, 8, $archs * 20);

        for ($i = 0; $i < $archs; ++$i) {
            $this->architectures[] = new FatBinaryArch(substr($archHeaders, $i * 20, 20));
        }
    }

    public function getOffsetForLocalArch(): int {
        [, $cpuType, $cpuSubtype] = unpack("L2", shell_exec("sysctl -b hw.cputype hw.cpusubtype"));

        foreach ($this->architectures as $arch) {
            if ($cpuType === $arch->cpuType || (PHP_INT_SIZE === 8 && ($cpuType | self::CPU_ARCH_ABI64) === $arch->cpuType)) {
                $archType = $arch->cpuSubtype & 0xffffff;
                if ($archType === $cpuSubtype || $archType === 0xffffff || $archType === (self::CPU_SUBTYPE_WILDCARD[$cpuType] ?? 0)) {
                    return $arch->offset;
                }
            }
        }

        throw new \LogicException("Found no match for CPU type $cpuType and subtype $cpuSubtype in {$this->filename}");
    }
}
