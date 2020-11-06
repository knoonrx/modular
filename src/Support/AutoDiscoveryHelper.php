<?php

namespace InterNACHI\Modular\Support;

use Illuminate\Filesystem\Filesystem;

class AutoDiscoveryHelper
{
	/**
	 * @var \InterNACHI\Modular\Support\ModuleRegistry 
	 */
	protected $module_registry;
	
	/**
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $filesystem;
	
	/**
	 * @var string
	 */
	protected $base_path;
	
	public function __construct(ModuleRegistry $module_registry, Filesystem $filesystem)
	{
		$this->module_registry = $module_registry;
		$this->filesystem = $filesystem;
		$this->base_path = $module_registry->getModulesPath();
	}
	
	public function commandFileFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forFiles()
			->depth('> 3')
			->path('src/Console/Commands')
			->name('*.php')
			->in($this->base_path);
	}
	
	public function factoryDirectoryFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forDirectories()
			->depth('== 2')
			->path('database/')
			->name('factories')
			->in($this->base_path);
	}
	
	public function migrationDirectoryFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forDirectories()
			->depth('== 2')
			->path('database/')
			->name('migrations')
			->in($this->base_path);
	}
	
	public function modelFileFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forFiles()
			->depth('> 2')
			->path('src/Models')
			->name('*.php')
			->in($this->base_path);
	}
	
	public function bladeComponentFileFinder() : FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forFiles()
			->depth('> 3')
			->path('src/View/Components')
			->name('*.php')
			->in($this->base_path);
	}
	
	public function routeFileFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forFiles()
			->depth(2)
			->path('routes/')
			->name('*.php')
			->in($this->base_path);
	}
	
	public function viewDirectoryFinder(): FinderCollection
	{
		if ($this->basePathMissing()) {
			return FinderCollection::empty();
		}
		
		return FinderCollection::forDirectories()
			->depth('== 2')
			->path('resources/')
			->name('views')
			->in($this->base_path);
	}
	
	protected function basePathMissing(): bool
	{
		return false === $this->filesystem->isDirectory($this->base_path);
	}
}
