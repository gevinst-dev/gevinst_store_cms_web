<?php

namespace Modules\FrontendCMS\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Translatable\HasTranslations;

class ContactContent extends Model
{
    use HasFactory ,HasTranslations;

    protected $guarded = ['id'];
    public $translatable = ['mainTitle','subTitle','description'];
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

    }
}
