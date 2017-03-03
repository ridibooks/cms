<?php

namespace Ridibooks\CmsServer\Thrift;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Protocol\TJSONProtocol;
use Thrift\Protocol\TBinaryProtocol;
use UnexpectedValueException;

class ThriftResponse
{
	public static function make(Request $request, $processor, $format)
	{
		try {
			$readTransport = new TMemoryBuffer($request->getContent(false));
			$writeTransport = new TMemoryBuffer();

			switch ($format) {
				case 'json':
					$readProtocol = new TJSONProtocol($readTransport);
					$writeProtocol = new TJSONProtocol($writeTransport);
					break;
				case 'binary':
					$readProtocol = new TBinaryProtocol($readTransport);
					$writeProtocol = new TBinaryProtocol($writeTransport);
					break;
				case 'compact':
					$readProtocol = new TCompactProtocol($readTransport);
					$writeProtocol = new TCompactProtocol($writeTransport);
					break;
				default:
					throw new UnexpectedValueException;
			}

			$readTransport->open();
			$writeTransport->open();
			$processor->process($readProtocol, $writeProtocol);
			$readTransport->close();
			$writeTransport->close();

			$content = $writeTransport->getBuffer();
		} catch (\Exception $e)
		{
			error_log($e->getMessage());
		}

		return Response::create($content, 200, [
			'Content-Type' => 'application/x-thrift'
		]);
	}
}
