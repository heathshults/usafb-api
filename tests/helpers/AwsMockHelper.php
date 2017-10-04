<?php

namespace Tests\Helpers;

use Mockery;
use App\Models\Enums\Role;

class AwsMockHelper
{
    /**
     * Mock AwsFacade
     *
     * @return void
     */
    static function mockAwsFacade($exceptionMessage = null)
    {
        //$mockAwsS3Client = AwsMockHelper::mockAwsS3Client();

        $mockAwsS3Client = Mockery::mock(Aws\S3\S3Client::class);

        if (is_null($exceptionMessage)) {
            $mockAwsS3Client
                ->shouldReceive('putObject')
                ->andReturn(
                    new \Aws\Result(['ObjectURL' => 'http://xxx.xxx'])
                )->shouldReceive('getObject')
                ->andReturn(
                    [
                        'Body' => json_encode([
                            'title' => 'some title',
                            'description' => 'some description',
                            'image' => 'some image in base64 format'
                        ])
                    ]
                );
        } else {
            $mockAwsS3Exception = Mockery::mock(\Aws\S3\Exception\S3Exception::class);
            $mockAwsS3Exception->shouldReceive('getAwsErrorMessage')
                ->andReturn(
                    $exceptionMessage
                );

            $mockAwsS3Client
                ->shouldReceive('putObject')
                ->andThrow($mockAwsS3Exception)
                ->shouldReceive('getObject')
                ->andThrow($mockAwsS3Exception);
        }

        \Aws\Laravel\AwsFacade::swap(\Mockery::mock()
            ->shouldReceive('createClient')
            ->once()
            ->andReturn($mockAwsS3Client)
            ->getMock()
        );
    }

    /**
     * Mock Aws S3 Client with Exception
     *
     * @return void
     */
    static function mockAwsS3ClientException($exceptionMessage)
    {
        $mockAwsS3Exception = Mockery::mock(\Aws\S3\Exception\S3Exception::class);
        $mockAwsS3Exception->shouldReceive('getAwsErrorMessage')
            ->andReturn(
                $exceptionMessage
            );

        $mockAwsS3Client = Mockery::mock(Aws\S3\S3Client::class);
        return $mockAwsS3Client
            ->shouldReceive('putObject')
            ->andThrow($mockAwsS3Exception)
            ->shouldReceive('getObject')
            ->andThrow($mockAwsS3Exception);
    }

     /**
      * Mock Aws S3 Client without Exceptions
      *
      * @return void
      */
    static function mockAwsS3Client()
    {
        $mockAwsS3Client = Mockery::mock(Aws\S3\S3Client::class);
        return $mockAwsS3Client
            ->shouldReceive('putObject')
            ->andReturn(
                new \Aws\Result(['ObjectURL' => 'http://xxx.xxx'])
            )->shouldReceive('getObject')
            ->andReturn(
                [
                    'Body' => json_encode([
                        'title' => 'some title',
                        'description' => 'some description',
                        'image' => 'some image in base64 format'
                    ])
                ]
            );
    }
}
