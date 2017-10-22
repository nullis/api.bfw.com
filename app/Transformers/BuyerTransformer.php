<?php

namespace App\Transformers;

use App\Buyer;
use League\Fractal\TransformerAbstract;
use Ramsey\Uuid\BinaryUtils;

class BuyerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Buyer $buyer
     * @return array
     */
    public function transform(Buyer $buyer)
    {
        return [
            'identifier' => (int)$buyer->id,
            'name' => (string)$buyer->name,
            'email' => (string)$buyer->email,
            'isVerified' => (int)$buyer->verified,
            'creationDate' => (string)$buyer->created_at,
            'lastChange' => (string)$buyer->updated_at,
            'deleteDate' => isset($buyer->delete_at) ? (string) $buyer->delete_at : null,
        ];
    }
}
