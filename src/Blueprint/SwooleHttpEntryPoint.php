<?php

namespace Walnut\Lang\NativeConnector\SwooleHttp\Blueprint;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface SwooleHttpEntryPoint {
	public function execute(
        Request $request,
        Response $response
    ): void;
}