<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceTable extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_type'];

    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}
