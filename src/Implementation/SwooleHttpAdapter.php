<?php

namespace Walnut\Lang\NativeConnector\SwooleHttp\Implementation;

use RuntimeException;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Walnut\Lang\Blueprint\Execution\Program;
use Walnut\Lang\Blueprint\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Identifier\PropertyNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\NativeCode\NativeCodeContext;
use Walnut\Lang\Blueprint\Value\DictValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\NativeConnector\SwooleHttp\Blueprint\SwooleHttpEntryPoint;

final readonly class SwooleHttpAdapter implements SwooleHttpEntryPoint {
	public function __construct(
		private Program $program,
        private NativeCodeContext $nativeCodeContext,
		private string $entryPointName = 'handleRequest'
	) {}

	private function buildRequest(Request $request): Value {
		return $this->nativeCodeContext->valueRegistry->dict([
			'protocolVersion' => $this->nativeCodeContext->valueRegistry->enumerationValue(
				new TypeNameIdentifier('HttpProtocolVersion'),
				new EnumValueIdentifier(
					match($request->server['server_protocol']) {
						'1.0' => 'HTTP1',
						'2.0' => 'HTTP2',
						'3.0' => 'HTTP3',
						/*'1.1',*/ default => 'HTTP11'
					}
				)
			),
			'method' => $this->nativeCodeContext->valueRegistry->enumerationValue(
				new TypeNameIdentifier('HttpRequestMethod'),
				new EnumValueIdentifier(strtoupper($request->getMethod()))
			),
			//'uri' => $this->nativeCodeContext->valueRegistry->string($request->server['request_uri']),
			'requestTarget' => $this->nativeCodeContext->valueRegistry->string($request->server['request_uri']),

			'headers' => $this->nativeCodeContext->valueRegistry->dict($this->getRequestHeaders($request->header)),
			'body' => $this->nativeCodeContext->valueRegistry->string($request->rawContent())
		]);
	}

	private function convertResponse(DictValue $response, Response $target): void {
		$target->setStatusCode(
			$response->valueOf(new PropertyNameIdentifier('statusCode'))->literalValue()
		);
		$headers = $response->valueOf(new PropertyNameIdentifier('headers'));
		foreach($headers->values() as $key => $value) {
			foreach($value->values() as $headerValue) {
				$target->header(
					$key,
					$headerValue->literalValue()
				);
			}
		}
		$body = $response->valueOf(new PropertyNameIdentifier('body'));
		if ($body instanceof StringValue) {
			$target->write(($body->literalValue()));
		}
	}

	public function execute(Request $request, Response $response): void {
		$result = $this->program->callFunction(
			new VariableNameIdentifier($this->entryPointName),
			$this->nativeCodeContext->typeRegistry->withName(
				new TypeNameIdentifier('HttpRequest')
			),
			$responseType = $this->nativeCodeContext->typeRegistry->withName(
				new TypeNameIdentifier('HttpResponse')
			),
			$this->buildRequest($request)
		);
		if (!($result instanceof DictValue && $result->type()->isSubtypeOf($responseType))) {
			throw new RuntimeException(
				sprintf("Invalid result type: '%s'. HttpResponse expected", $response::class)
			);
		}
		$this->convertResponse($result, $response);
	}

	private function getRequestHeaders(array $headers): array {
		$result = [];
		foreach($headers as $headerName => $headerValues) {
			$values = [];
			$values[] = $this->nativeCodeContext->valueRegistry->string($headerValues);
			$result[$headerName] = $this->nativeCodeContext->valueRegistry->list($values);
		}
		return $result;
	}

}