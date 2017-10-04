<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;

class MessageTransformer extends TransformerAbstract
{

    /**
     * Returns the response for a message
     *
     * @param string $message
     *
     * @return array
     */
    public function transform(string $message)
    {
        return ['message' => $message];
    }
}
