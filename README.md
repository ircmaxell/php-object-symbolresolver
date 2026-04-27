# PHP-ELF-SymbolResolver

## Requirements

- PHP 8.4 or later

A symbol resolver for ELF files, to extract what symbols are defined.

## Why?

I need it for FFIMe to work around some C level issues with libraries not exporting certain symbols. This lets me verify them...