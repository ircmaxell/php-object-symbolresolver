<?php

namespace PHPObjectSymbolResolver;

abstract class Parser {
	public string $data;
	public ObjectFile $obj;

	public abstract function parse(string $file): ObjectFile;

	public static function parserFor(string $file): Parser {
		$magic = file_get_contents($file, false, null, 0, 4);
		if ($magic === ELF\Parser::HEADER) {
			return new ELF\Parser;
		} elseif (MachO\Parser::isValidMagic($magic)) {
			return new MachO\Parser;
		}

		throw new \LogicException("File is neither in ELF nor Mach-O format");
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

	protected function parseNullTerminatedString(int $width, int &$offset): string {
		$result = substr($this->data, $offset, $width);
		$offset += $width;
		$data = strstr($result, "\0", true);
		return $data === false ? $result : $data;
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
