<?php

namespace Multiback\Uploader;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Multiback\Exception\UploadException;

class S3
{
	protected S3Client $client;

	protected string $dir;

	protected string $bucket;

	public function __construct(string $dir, string $bucket, string $access_key, string $secret_key, string $region, string $version = null)
	{
		$this->dir = $dir;
		$this->bucket = $bucket;
		$this->client = new S3Client([
			'region'  => $region,
			'version' => $version ?? 'latest',
			'credentials' => [
				'key'    => $access_key,
				'secret' => $secret_key,
			]
		]);
	}

	public function upload(): void
	{
		$keyPrefix = '';
		$options = [
			'params' => ['ACL' => 'public-read'],
		];

		try {
			$this->client->uploadDirectory($this->dir, $this->bucket, $keyPrefix, $options);
		} catch (S3Exception $e) {
			throw new UploadException(sprintf('Error uploading directory %s to bucket %s: %s', $this->dir, $this->bucket, $e->getMessage()));
		}
	}
}
