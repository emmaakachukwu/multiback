<?php

namespace Multiback\Uploader;

use Aws\S3\S3Client;

class S3
{
	protected S3Client $client;

	public function __construct()
	{
		$this->client = [
			'region'  => '-- your region --',
			'version' => 'latest',
			'credentials' => [
				'key'    => "-- access key id --",
				'secret' => "-- secret access key --",
			]
        ];
	}
}
