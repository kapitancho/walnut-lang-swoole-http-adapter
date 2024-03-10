<?php

namespace Walnut\Lang\NativeConnector\SwooleHttp\Implementation;

use Walnut\Lang\Blueprint\Compilation\ProgramCompilationContext;
use Walnut\Lang\Blueprint\Compilation\Source;
use Walnut\Lang\NativeConnector\SwooleHttp\Blueprint\SwooleHttpEntryPoint;
use Walnut\Lang\NativeConnector\SwooleHttp\Blueprint\SwooleHttpProgramCompiler;

final readonly class SwooleHttpProgramCompilerAdapter implements SwooleHttpProgramCompiler {
	public function __construct(
        private ProgramCompilationContext $programCompilationContext,
	) {}

	public function compileHttpProgram(Source $source, string $entryPointName = 'handleRequest'): SwooleHttpEntryPoint {
		return new SwooleHttpAdapter(
            $this->programCompilationContext->compileProgram($source),
            $this->programCompilationContext->nativeCodeContext(),
			$entryPointName
		);
	}
}