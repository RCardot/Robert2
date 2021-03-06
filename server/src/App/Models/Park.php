<?php
declare(strict_types=1);

namespace Robert2\API\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Respect\Validation\Validator as V;

class Park extends BaseModel
{
    use SoftDeletes;

    protected $table = 'parks';

    protected $_modelName = 'Park';
    protected $_orderField = 'name';
    protected $_orderDirection = 'asc';

    protected $_allowedSearchFields = ['name'];
    protected $_searchField = 'name';

    public function __construct()
    {
        parent::__construct();

        $this->validation = [
            'name'          => V::notEmpty()->length(2, 96),
            'person_id'     => V::optional(V::numeric()),
            'company_id'    => V::optional(V::numeric()),
            'street'        => V::optional(V::length(null, 191)),
            'postal_code'   => V::optional(V::length(null, 10)),
            'locality'      => V::optional(V::length(null, 191)),
            'country_id'    => V::optional(V::numeric()),
            'opening_hours' => V::optional(V::length(null, 255)),
        ];
    }

    // ——————————————————————————————————————————————————————
    // —
    // —    Relations
    // —
    // ——————————————————————————————————————————————————————

    protected $appends = [
        'total_items',
        'total_stock_quantity',
        'total_amount',
    ];

    public function Materials()
    {
        return $this->hasMany('Robert2\API\Models\Material');
    }

    public function Person()
    {
        return $this->belongsTo('Robert2\API\Models\Person');
    }

    public function Company()
    {
        return $this->belongsTo('Robert2\API\Models\Company');
    }

    public function Country()
    {
        return $this->belongsTo('Robert2\API\Models\Country')
            ->select(['id', 'name', 'code']);
    }

    // ——————————————————————————————————————————————————————
    // —
    // —    Mutators
    // —
    // ——————————————————————————————————————————————————————

    protected $casts = [
        'person_id'     => 'integer',
        'company_id'    => 'integer',
        'name'          => 'string',
        'street'        => 'string',
        'postal_code'   => 'string',
        'locality'      => 'string',
        'country_id'    => 'integer',
        'opening_hours' => 'string',
        'note'          => 'string',
    ];

    public function getMaterialsAttribute()
    {
        $materials = $this->Materials()->get();
        return $materials ? $materials->toArray() : null;
    }

    public function getTotalItemsAttribute()
    {
        return $this->Materials()->count();
    }

    public function getTotalStockQuantityAttribute()
    {
        $materials = $this->Materials()->get(['stock_quantity']);
        $total = 0;
        foreach ($materials as $material) {
            $total += $material->stock_quantity;
        }
        return $total;
    }

    public function getTotalAmountAttribute()
    {
        $materials = $this->Materials()->get(['stock_quantity', 'replacement_price']);
        $total = 0;
        foreach ($materials as $material) {
            $total += ($material->replacement_price * $material->stock_quantity);
        }
        return $total;
    }

    public function getPersonAttribute()
    {
        $user = $this->Person()->first();
        return $user ? $user->toArray() : null;
    }

    public function getCompanyAttribute()
    {
        $company = $this->Company()->first();
        return $company ? $company->toArray() : null;
    }

    public function getCountryAttribute()
    {
        $country = $this->Country()->first();
        return $country ? $country->toArray() : null;
    }

    // ——————————————————————————————————————————————————————
    // —
    // —    Setters
    // —
    // ——————————————————————————————————————————————————————

    protected $fillable = [
        'name',
        'street',
        'postal_code',
        'locality',
        'country_id',
        'opening_hours',
        'note',
    ];
}
