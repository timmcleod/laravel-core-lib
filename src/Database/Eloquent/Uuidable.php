<?php
namespace TimMcLeod\LaravelCoreLib\Database\Eloquent;

use Exception;
use Ramsey\Uuid\Uuid;

trait Uuidable
{
    /**
     * Binds creating/saving events to create UUIDs (and also prevent them from being overwritten).
     *
     * @return void
     */
    public static function bootUuidable()
    {
        static::creating(function ($model)
        {
            $model->verifyUuidsProperty();

            foreach ($model->uuids as $attribute)
            {
                $model->{$attribute} = Uuid::uuid4()->toString();
            }
        });

        static::saving(function ($model)
        {
            $model->verifyUuidsProperty();

            foreach ($model->uuids as $attribute)
            {
                // Prevent changes to the UUID.
                $originalUuid = $model->getOriginal($attribute);

                if ($originalUuid !== $model->{$attribute})
                {
                    $model->{$attribute} = $originalUuid;
                }

                // Safety net: if UUID is empty, then create one.
                if ($model->{$attribute} === '') $model->{$attribute} = Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * @throws Exception
     */
    public function verifyUuidsProperty()
    {
        if (!$this->uuids)
        {
            $c = get_class($this);

            throw new Exception("When using the Uuidable trait, the uuids array property must be defined in: $c");
        }
    }
}