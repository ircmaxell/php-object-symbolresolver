<?php

namespace PHPObjectSymbolResolver;

use PHPObjectSymbolResolver\MachO\FatBinary;

abstract class Parser {
	public string $data;
	public ObjectFile $obj;

	public abstract function parse(string $file): ObjectFile;

	public static function parseFor(string $file): ObjectFile {
        if (!file_exists($file) && file_exists($tbdFile = MachO\TbdObjectFile::searchTdbFile($file))) {
            return MachO\TbdObjectFile::readTdb($tbdFile);
        }

		$magic = file_get_contents($file, false, null, 0, 4);
		if ($magic === ELF\Parser::HEADER) {
			return (new ELF\Parser)->parse($file);
		} elseif (MachO\Parser::isValidMagic($magic)) {
			return (new MachO\Parser)->parse($file);
		} elseif ($magic === FatBinary::MAGIC) {
            $fatBinary = new FatBinary($file);
            return (new MachO\Parser)->parse($file, $fatBinary->getOffsetForLocalArch());
		} elseif (PE\Parser::isPortableExecutable($file)) {
			return (new PE\Parser)->parse($file);
        } elseif (substr($magic, 0, 2) === PE\Parser::HEADER) {
			throw new \LogicException(
				"File is MS-DOS compatible but is not in the PE format. " .
				"MS-DOS executables are not supported."
			);
		}

		throw new \LogicException("File is neither in ELF, Mach-O nor PE format");
	}

	protected function parseAddr(int &$offset): string {
		return $this->parseOff($offset);
	}

	protected function parseHalf(int &$offset): int {
		return $this->parseWidth(2, $offset);
	}

	protected function parseWord(int &$offset): int {
		return $this->parseWidth(4, $offset);
	}

	protected function parseXWord(int &$offset): int {
		if ($this->obj->is64Bit()) {
			return $this->parseWidth(8, $offset);
		}
		return $this->parseWidth(4, $offset);
	}

	protected function parseUChar(int &$offset): int {
		return $this->parseWidth(1, $offset);
	}

	protected function parseOff(int &$offset): int {
		if ($this->obj->is32Bit()) {
			return $this->parseWidth(4, $offset);
		} elseif ($this->obj->is64Bit()) {
			return $this->parseWidth(8, $offset);
		}
	}

	protected function parseWidth(int $width, int &$offset): int {
		$result = substr($this->data, $offset, $width);
		$offset += $width;
		return $this->stringToInt($result);
	}

	protected function parseNullTerminatedString(int &$offset, int $width = 0): string {
        if ($width === 0) {
            return substr($this->data, $offset, strpos($this->data, "\0", $offset) - $offset);
        } else {
            $result = substr($this->data, $offset, $width);
            $offset += $width;
            $data = strstr($result, "\0", true);
            return $data === false ? $result : $data;
        }
	}

	protected function stringToInt(string $string): int {
		$result = 0;
		if ($this->obj->hasLowestByteFirst()) {
			for ($i = strlen($string) - 1; $i >= 0; $i--) {
				$result = ($result << 8) | ord($string[$i]);
			}
		} else {
			for ($i = 0, $n = strlen($string); $i < $n; $i++) {
				$result = ($result << 8) | ord($string[$i]);
			}
		}
		return $result;
	}
}
