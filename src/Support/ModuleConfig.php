<?php

namespace InterNACHI\Modular\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\SplFileInfo;

class ModuleConfig implements Arrayable
{
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 */
	public $base_path;
	
	/**
	 * @var Collection
	 */
	public $namespaces;
	
	public static function fromComposerFile(SplFileInfo $composer_file): self
	{
		$composer_config = json_decode($composer_file->getContents(), true, 16, JSON_THROW_ON_ERROR);
		
		$base_path = rtrim($composer_file->getPath(), DIRECTORY_SEPARATOR);
		
		$name = basename($base_path);
		
		$namespaces = Collection::make($composer_config['autoload']['psr-4'] ?? [])
			->mapWithKeys(function($src, $namespace) use ($base_path) {;
				$path = $base_path.DIRECTORY_SEPARATOR.self::normalize_separator($src);
				return [$path => $namespace];
			});
		
		return new static($name, $base_path, $namespaces);
	}
	
	public function __construct($name, $base_path, Collection $namespaces = null)
	{
		$this->name = $name;
		$this->base_path = self::normalize_separator($base_path);
		$this->namespaces = $namespaces ?? new Collection();
	}
	
	public function path(string $to = ''): string
	{
		return rtrim($this->base_path.DIRECTORY_SEPARATOR.self::normalize_separator($to), DIRECTORY_SEPARATOR);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	static public function normalize_separator($path): string
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}
	
	public function namespace(): string
	{
		return $this->namespaces->first();
	}

	public function qualify(string $class_name): string
	{
		return $this->namespace().ltrim($class_name, '\\');
	}

	public function toArray(): array
	{
		return [
			'name' => $this->name,
			'base_path' => $this->base_path,
			'namespaces' => $this->namespaces->toArray(),
		];
	}
}
