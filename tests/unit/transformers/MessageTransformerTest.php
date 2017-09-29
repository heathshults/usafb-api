<?php

namespace Tests\Unit\Transformers;

use App\Transformers\MessageTransformer;


class MessageTransformerTest extends \TestCase
{
    /**
     * Test success on transforming simple message string
     * into the defined message response
     *
     * @return void
     */
    public function testTransformResponse()
    {
        $message = "Some message";
        $transformer = new MessageTransformer();

        $response = $transformer->transform($message);

        $this->assertEquals($response['message'], $message);
    }
}
