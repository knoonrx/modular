<?php

namespace InterNACHI\Modular\Console\Commands\Make;

use Illuminate\Support\Str;
use InterNACHI\Modular\Support\ModuleConfig;

trait Modularize
{
	use \InterNACHI\Modular\Console\Commands\Modularize;
	protected function getDefaultNamespace($rootNamespace)
	{
		$namespace = parent::getDefaultNamespace($rootNamespace);
		$module = $this->module();

		if ($module && false === strpos($rootNamespace, $module->namespaces->first())) {
			$find = rtrim($rootNamespace, '\\');
			$replace = rtrim($module->namespaces->first(), '\\');
			$namespace = str_replace($find, $replace, $namespace);
		}

		return $namespace;
	}

	protected function qualifyClass($name)
	{
		$name = ltrim($name, '\\/');

		if ($module = $this->module()) {
			if (Str::startsWith($name, $module->namespaces->first())) {
				return $name;
			}
		}

		return parent::qualifyClass($name);
	}

	protected function qualifyModel(string $model)
	{
		if ($module = $this->module()) {
			$model = str_replace('/', '\\', ltrim($model, '\\/'));

			if (Str::startsWith($model, $module->namespace())) {
				return $model;
			}

			return $module->qualify('Models\\'.$model);
		}

		return parent::qualifyModel($model);
	}

	protected function getPath($name)
	{
		if ($module = $this->module()) {
			$name = Str::replaceFirst($module->namespaces->first(), '', $name);
		}

		$path = ModuleConfig::normalize_separator(parent::getPath($name));

		if ($module) {
			// Set up our replacements as a [find -> replace] array
			$replacements = [
				ModuleConfig::normalize_separator($this->laravel->path()) => $module->namespaces->keys()->first(),
				ModuleConfig::normalize_separator($this->laravel->basePath('tests'. DIRECTORY_SEPARATOR .'Tests')) => $module->path('tests'),
				ModuleConfig::normalize_separator($this->laravel->databasePath()) => $module->path('database'),
			];

			// Normalize all our paths for compatibility's sake
			$normalize = function($path) {
				$result = trim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
				if(windows_os()) {
					return $result;
				}

				return DIRECTORY_SEPARATOR . $result;
			};

			$find = array_map($normalize, array_keys($replacements));
			$replace = array_map($normalize, array_values($replacements));

			// And finally apply the replacements
			$path = str_replace($find, $replace, $path);
		}

		return $path;
	}

	public function call($command, array $arguments = [])
	{
		// Pass the --module flag on to subsequent commands
		if ($module = $this->option('module')) {
			$arguments['--module'] = $module;
		}

		return $this->runCommand($command, $arguments, $this->output);
	}
}
