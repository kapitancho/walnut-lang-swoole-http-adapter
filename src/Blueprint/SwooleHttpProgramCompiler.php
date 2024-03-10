<?php

namespace Walnut\Lang\NativeConnector\SwooleHttp\Blueprint;

use Walnut\Lang\Blueprint\Compilation\Source;

interface SwooleHttpProgramCompiler {
	public function compileHttpProgram(
        Source $source,
        string $entryPointName = 'handleRequest'
    ): SwooleHttpEntryPoint;
}